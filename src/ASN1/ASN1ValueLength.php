<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\ASN1;

use Readdle\AppStoreReceiptVerification\BufferReader;
use UnexpectedValueException;

final class ASN1ValueLength
{
    const IS_INDEFINITE = 0b10000000;
    const IS_RESERVED = 0b11111111;
    const SHORT_FORM_MAX = 0b01111111;

    private int $ownLength = 1;
    private int $value;

    public function __construct(BufferReader $bufferReader)
    {
        $octet = $bufferReader->readOrdinal();

        if ($octet === self::IS_INDEFINITE) {
            throw new UnexpectedValueException('ASN.1 indefinite form length is not supported (yet?)');
        }

        if ($octet === self::IS_RESERVED) {
            throw new UnexpectedValueException('ASN.1 wrong length octet passed (RESERVED)');
        }

        if ($octet <= self::SHORT_FORM_MAX) {
            $this->value = $octet;
            return;
        }

        $this->value = 0;
        $octetsNumber = $octet & 0b01111111; // number of octets is between 1 and 127, we need to drop the first bit
        $this->ownLength += $octetsNumber;

        for ($i = 0; $i < $octetsNumber; $i++) {
            $octet = $bufferReader->readOrdinal();
            $this->value = $this->value * 256 + $octet;
        }
    }

    public function getOwnLength(): int
    {
        return $this->ownLength;
    }

    public function getValueLength(): int
    {
        return $this->value;
    }
}
