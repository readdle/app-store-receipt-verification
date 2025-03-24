<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\ASN1\Universal;

use Exception;
use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\Math\BigInteger;

final class Integer extends AbstractASN1Object
{
    protected string $decValue;
    protected string $hexValue;

    /**
     * @throws Exception
     */
    protected function setValue($value): void
    {
        $bigInteger = new BigInteger($value);
        $this->decValue = $bigInteger->getDecValue();
        $this->hexValue = $bigInteger->getHexValue();
    }

    public function getValue(): string
    {
        return $this->decValue;
    }

    public function getIntValue(): int
    {
        return (int) $this->getValue();
    }

    /**
     * @throws Exception
     */
    public function getHexValue(): string
    {
        return $this->hexValue;
    }

    public function jsonSerialize(): string
    {
        return $this->decValue;
    }
}
