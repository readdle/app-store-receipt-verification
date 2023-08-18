<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\ASN1\Universal;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;

final class Nil extends AbstractASN1Object
{
    protected function setValue($value): void
    {
    }

    public function getValue()
    {
        return null;
    }

    public function jsonSerialize(): string
    {
        return 'null';
    }
}
