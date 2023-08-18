<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\PKCS7\X509\AlgorithmIdentifier;
use Readdle\AppStoreReceiptVerification\Utils;

final class SignerInfo extends AbstractPKCS7Object
{
    private string $version;
    private IssuerAndSerialNumber $issuerAndSerialNumber;
    private AlgorithmIdentifier $digestAlgorithm;
    private AlgorithmIdentifier $digestEncryptionAlgorithm;
    private string $encryptedDigest;

    protected function __construct(
        string $version,
        IssuerAndSerialNumber $issuerAndSerialNumber,
        AlgorithmIdentifier $digestAlgorithm,
        AlgorithmIdentifier $digestEncryptionAlgorithm,
        string $encryptedDigest
    ) {
        $this->version = $version;
        $this->issuerAndSerialNumber = $issuerAndSerialNumber;
        $this->digestAlgorithm = $digestAlgorithm;
        $this->digestEncryptionAlgorithm = $digestEncryptionAlgorithm;
        $this->encryptedDigest = $encryptedDigest;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getIssuerAndSerialNumber(): IssuerAndSerialNumber
    {
        return $this->issuerAndSerialNumber;
    }

    public function getDigestAlgorithm(): AlgorithmIdentifier
    {
        return $this->digestAlgorithm;
    }

    public function getDigestEncryptionAlgorithm(): AlgorithmIdentifier
    {
        return $this->digestEncryptionAlgorithm;
    }

    public function getEncryptedDigest(): string
    {
        return $this->encryptedDigest;
    }

    public static function fromASN1Object(AbstractASN1Object $object): self
    {
        self::assertASNIdentifierType($object, 'Sequence');

        [
            $version,
            $issuerAndSerialNumber,
            $digestAlgorithm,
            $digestEncryptionAlgorithm,
            $encryptedDigest
        ] = $object->getValue();

        return new self(
            $version->getValue(),
            IssuerAndSerialNumber::fromASN1Object($issuerAndSerialNumber),
            AlgorithmIdentifier::fromASN1Object($digestAlgorithm),
            AlgorithmIdentifier::fromASN1Object($digestEncryptionAlgorithm),
            $encryptedDigest->getValue()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'version' => $this->getVersion(),
            'issuerAndSerialNumber' => $this->getIssuerAndSerialNumber(),
            'digestAlgorithm' => $this->getDigestAlgorithm(),
            'digestEncryptionAlgorithm' => $this->getDigestEncryptionAlgorithm(),
            'encryptedDigest' => Utils::formatHexString($this->getEncryptedDigest()),
         ];
    }
}
