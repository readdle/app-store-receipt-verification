<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7\X509;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\PKCS7\AbstractPKCS7Object;
use Readdle\AppStoreReceiptVerification\Utils;

final class SignedCertificate extends AbstractPKCS7Object
{
    private Certificate $certificate;
    private AlgorithmIdentifier $algorithmIdentifier;
    private string $encryptedHash;
    private string $der;

    protected function __construct(
        Certificate $certificate,
        AlgorithmIdentifier $algorithmIdentifier,
        string $encryptedHash,
        string $der
    ) {
        $this->certificate = $certificate;
        $this->algorithmIdentifier = $algorithmIdentifier;
        $this->encryptedHash = $encryptedHash;
        $this->der = $der;
    }

    public function getCertificate(): Certificate
    {
        return $this->certificate;
    }

    public function getAlgorithmIdentifier(): AlgorithmIdentifier
    {
        return $this->algorithmIdentifier;
    }

    public function getEncryptedHash(): string
    {
        return $this->encryptedHash;
    }

    public function getDER(): string
    {
        return $this->der;
    }

    public function getPEM(): string
    {
        return Utils::DER2PEM($this->getDER());
    }

    public static function fromASN1Object(AbstractASN1Object $object): self
    {
        self::assertASNIdentifierType($object, 'Sequence');
        self::assertASNChildrenStruct($object, [
            'Sequence',
            'Sequence',
            'Bit String',
        ]);

        [$toBeSigned, $algorithmIdentifier, $encryptedHash] = $object->getValue();
        return new self(
            Certificate::fromASN1Object($toBeSigned),
            AlgorithmIdentifier::fromASN1Object($algorithmIdentifier),
            $encryptedHash->getValue(),
            $object->asBinary()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'certificate' => $this->getCertificate(),
            'algorithmIdentifier' => $this->getAlgorithmIdentifier(),
            'encryptedHash' => Utils::formatHexString($this->getEncryptedHash()),
            'DER' => Utils::formatHexString($this->getDER()),
        ];
    }
}
