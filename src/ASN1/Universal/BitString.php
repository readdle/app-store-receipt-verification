<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\ASN1\Universal;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\Utils;

use function ord;
use function substr;

final class BitString extends AbstractASN1Object
{
    protected int $numberOfUnusedBits;
    protected string $value = '';

    protected function setValue($value): void
    {
        $this->numberOfUnusedBits = ord($value[0]);
        $this->value = substr($value, 1);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return Utils::formatHexString($this->value);
    }
}
