<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\PKCS7\X501\Name;

final class IssuerAndSerialNumber extends AbstractPKCS7Object
{
    private Name $issuer;
    private string $serialNumber;

    protected function __construct(Name $issuer, string $serialNumber)
    {
        $this->issuer = $issuer;
        $this->serialNumber = $serialNumber;
    }

    public function getIssuer(): Name
    {
        return $this->issuer;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public static function fromASN1Object(AbstractASN1Object $object): self
    {
        self::assertASNIdentifierType($object, 'Sequence');

        [$issuer, $serialNumber] = $object->getValue();
        return new self(Name::fromASN1Object($issuer), $serialNumber->getValue());
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'issuer' => $this->getIssuer(),
            'serialNumber' => $this->getSerialNumber(),
        ];
    }
}
