<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification;

use DateTimeImmutable;
use Exception;
use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\PKCS7\AppStore\AppReceiptField;
use Readdle\AppStoreReceiptVerification\PKCS7\X501\Name;
use Readdle\AppStoreReceiptVerification\PKCS7\X509\Certificate;

use function count;
use function hash_equals;
use function ksort;
use function openssl_verify;
use function openssl_x509_fingerprint;
use function openssl_x509_verify;
use function ord;

use const OPENSSL_ALGO_SHA1;
use const OPENSSL_ALGO_SHA256;

final class ReceiptContainerVerifier implements ReceiptContainerVerifierInterface
{
    // X.509 extension OIDs, see https://www.rfc-editor.org/rfc/rfc5280#section-4.2.1
    private const OID_BASIC_CONSTRAINTS = '2.5.29.19';
    private const OID_KEY_USAGE = '2.5.29.15';

    // KeyUsage ::= BIT STRING, keyCertSign is bit 5 (0-indexed), i.e. the 6th most significant bit of byte 0
    private const KEY_USAGE_KEY_CERT_SIGN_MASK = 0x04;

    private ReceiptContainer $receiptContainer;

    public function __construct(ReceiptContainerInterface $receiptContainer)
    {
        $this->receiptContainer = $receiptContainer;
    }

    public function verify(string $trustedAppleRootCertificate): bool
    {
        return
            $this->verifyCertificatesChain()
            && $this->verifyRootCertificate($trustedAppleRootCertificate)
            && $this->verifyReceiptSignature()
        ;
    }

    private function verifyCertificatesChain(): bool
    {
        $signedAt = $this->receiptContainer
            ->getReceipt()
            ->getFieldByType(AppReceiptField::TYPE__REQUEST_DATE)
            ->getValue()
        ;

        if (empty($signedAt)) {
            return false;
        }

        try {
            $signDateTime = new DateTimeImmutable($signedAt);
        } catch (Exception $e) {
            return false;
        }

        $signedCertificates = $this->receiptContainer->getSignedCertificates();

        if (count($signedCertificates) !== 3) {
            return false;
        }

        foreach ($signedCertificates as $signedCertificate) {
            $validity = $signedCertificate->getCertificate()->getValidity();

            if (
                $signDateTime < $validity->getNotBefore()
                || $signDateTime > $validity->getNotAfter()
            ) {
                return false;
            }
        }

        for ($i = 0; $i < count($signedCertificates) - 1; $i++) {
            $subjectCertificate = $signedCertificates[$i]->getCertificate();
            $issuerCertificate = $signedCertificates[$i + 1]->getCertificate();

            // A signature match alone (openssl_x509_verify()) only proves that the issuer's
            // private key produced the signature, not that the issuer is entitled to sign
            // other certificates. Without this check, any certificate signed by a real CA
            // (e.g. an ordinary Apple Developer certificate signed by Apple WWDR) could be
            // used to mint a forged leaf, since it would satisfy every other check the same
            // way a genuine intermediate would.
            if (!$this->isUsableAsCertificateAuthority($issuerCertificate)) {
                return false;
            }

            if (!$this->namesMatch($subjectCertificate->getIssuer(), $issuerCertificate->getSubject())) {
                return false;
            }

            if (openssl_x509_verify($signedCertificates[$i]->getPEM(), $signedCertificates[$i + 1]->getPEM()) !== 1) {
                return false;
            }
        }

        return true;
    }

    private function verifyRootCertificate(string $trustedAppleRootCertificate): bool
    {
        $topCertificateFingerprint = openssl_x509_fingerprint(
            $this->receiptContainer->getSignedCertificates()[2]->getPEM(),
            'sha256'
        );
        $trustedRootFingerprint = openssl_x509_fingerprint($trustedAppleRootCertificate, 'sha256');

        if (!$topCertificateFingerprint || !$trustedRootFingerprint) {
            return false;
        }

        return hash_equals($trustedRootFingerprint, $topCertificateFingerprint);
    }

    private function verifyReceiptSignature(): bool
    {
        $signerInfo = $this->receiptContainer->getSignerInfo();
        $issuerAndSerialNumber = $signerInfo->getIssuerAndSerialNumber();
        $serialNumber = $issuerAndSerialNumber->getSerialNumber();
        $issuer = $issuerAndSerialNumber->getIssuer();
        $signerCertificate = null;

        foreach ($this->receiptContainer->getSignedCertificates() as $signedCertificate) {
            $certificate = $signedCertificate->getCertificate();

            if ($serialNumber === $certificate->getSerialNumber() && $this->namesMatch($issuer, $certificate->getIssuer())) {
                $signerCertificate = $signedCertificate;
                break;
            }
        }

        if (!$signerCertificate) {
            return false;
        }

        switch ($signerInfo->getDigestAlgorithm()->getAlgorithm()) {
            case ObjectIdentifierTree::SIGNATURE__SHA1:
                $algorithm = OPENSSL_ALGO_SHA1;
                break;

            case ObjectIdentifierTree::SIGNATURE__SHA256:
                $algorithm = OPENSSL_ALGO_SHA256;
                break;

            default:
                return false;
        }

        return openssl_verify(
            $this->receiptContainer->getReceiptBinary(),
            $signerInfo->getEncryptedDigest(),
            $signerCertificate->getPEM(),
            $algorithm
        ) === 1;
    }

    private function isUsableAsCertificateAuthority(Certificate $certificate): bool
    {
        $isCA = false;
        $canSignCertificates = false;

        foreach ($certificate->getExtensions() as $extension) {
            if ($extension->getId() === self::OID_BASIC_CONSTRAINTS) {
                $isCA = $this->basicConstraintsAllowCA($extension->getValue());
            } elseif ($extension->getId() === self::OID_KEY_USAGE) {
                $canSignCertificates = $this->keyUsageAllowsCertSigning($extension->getValue());
            }
        }

        return $isCA && $canSignCertificates;
    }

    private function basicConstraintsAllowCA(string $derEncodedExtensionValue): bool
    {
        try {
            // BasicConstraints ::= SEQUENCE { cA BOOLEAN DEFAULT FALSE, pathLenConstraint INTEGER OPTIONAL }
            // an absent cA field (empty sequence) means CA defaults to FALSE.
            $children = AbstractASN1Object::fromString($derEncodedExtensionValue)->getValue();
        } catch (Exception $e) {
            return false;
        }

        return isset($children[0]) && $children[0]->getValue() === true;
    }

    private function keyUsageAllowsCertSigning(string $derEncodedExtensionValue): bool
    {
        try {
            // KeyUsage ::= BIT STRING
            $bits = AbstractASN1Object::fromString($derEncodedExtensionValue)->getValue();
        } catch (Exception $e) {
            return false;
        }

        return isset($bits[0]) && (ord($bits[0]) & self::KEY_USAGE_KEY_CERT_SIGN_MASK) !== 0;
    }

    private function namesMatch(Name $a, Name $b): bool
    {
        $attributesA = $a->getAttributes();
        $attributesB = $b->getAttributes();
        ksort($attributesA);
        ksort($attributesB);

        return $attributesA === $attributesB;
    }
}
