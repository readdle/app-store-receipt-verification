<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\ASN1\Universal;

use DateTimeImmutable;
use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;

final class UTCTime extends AbstractASN1Object
{
    protected DateTimeImmutable $value;

    protected function setValue($value): void
    {
        $this->value = DateTimeImmutable::createFromFormat('ymdHisZ', $value);
    }

    public function getValue(): DateTimeImmutable
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value->format('r');
    }
}
