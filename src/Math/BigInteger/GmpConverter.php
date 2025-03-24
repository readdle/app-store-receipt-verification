<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Math\BigInteger;

use function gmp_strval;

final class GmpConverter implements ConverterInterface
{
    public function hexToDecimal(string $hex): string
    {
        return gmp_strval('0x' . $hex);
    }
}
