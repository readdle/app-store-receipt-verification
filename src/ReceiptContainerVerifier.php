<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification;

use DateTimeImmutable;
use Exception;
use Readdle\AppStoreReceiptVerification\PKCS7\AppStore\AppReceiptField;

use function count;
use function openssl_verify;
use function openssl_x509_verify;

use const OPENSSL_ALGO_SHA1;
use const OPENSSL_ALGO_SHA256;

final class ReceiptContainerVerifier implements ReceiptContainerVerifierInterface
{
    private ReceiptContainer $receiptContainer;

    public function __construct(ReceiptContainer $receiptContainer)
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
            if (openssl_x509_verify($signedCertificates[$i]->getPEM(), $signedCertificates[$i + 1]->getPEM()) !== 1) {
                return false;
            }
        }

        return true;
    }

    private function verifyRootCertificate(string $trustedAppleRootCertificate): bool
    {
        return openssl_x509_verify(
            $this->receiptContainer->getSignedCertificates()[2]->getPEM(),
            $trustedAppleRootCertificate
        ) === 1;
    }

    private function verifyReceiptSignature(): bool
    {
        $signerInfo = $this->receiptContainer->getSignerInfo();
        $serialNumber = $signerInfo->getIssuerAndSerialNumber()->getSerialNumber();
        $signerCertificate = null;

        foreach ($this->receiptContainer->getSignedCertificates() as $signedCertificate) {
            if ($serialNumber === $signedCertificate->getCertificate()->getSerialNumber()) {
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
}
