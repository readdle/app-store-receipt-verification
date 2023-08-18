<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\Utils;

final class Data extends AbstractData
{
    private string $binaryData;

    protected function __construct(string $binaryData)
    {
        $this->binaryData = $binaryData;
    }

    public function getBinaryData(): string
    {
        return $this->binaryData;
    }

    public static function fromASN1Object(AbstractASN1Object $object): self
    {
        self::assertASNIdentifierType($object, 'Octet String');
        return new self($object->getValue());
    }

    public function jsonSerialize(): string
    {
        return Utils::formatHexString($this->binaryData);
    }
}
