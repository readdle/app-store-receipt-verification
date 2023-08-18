<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\ASN1\Universal;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;

use function floor;
use function join;
use function ord;
use function strlen;

final class ObjectIdentifier extends AbstractASN1Object
{
    protected string $value;

    protected function setValue($value): void
    {
        $objectIdentifier = [];

        $octet = ord($value[0]);
        $objectIdentifier[] = floor($octet / 40);
        $objectIdentifier[] = $octet % 40;

        $position = 1;
        $length = strlen($value);
        $subIdentifier = 0;

        while ($position < $length) {
            $octet = ord($value[$position++]);
            $subIdentifier = ($subIdentifier << 7) + ($octet & 0b01111111);

            if (($octet & 0b10000000) === 0) {
                $objectIdentifier[] = $subIdentifier;
                $subIdentifier = 0;
            }
        }

        $this->value = join('.', $objectIdentifier);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
