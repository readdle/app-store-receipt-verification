<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\ASN1\Universal;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;

use function ord;

final class Boolean extends AbstractASN1Object
{
    protected bool $value;

    protected function setValue($value): void
    {
        $this->value = (bool) ord($value);
    }

    public function getValue(): bool
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value ? 'true' : 'false';
    }
}
