<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Math\BigInteger;

use function bcadd;
use function bcmul;
use function bcpow;
use function hexdec;
use function strlen;
use function strval;

final class BcmathConverter implements ConverterInterface
{
    public function hexToDecimal(string $hex): string
    {
        $dec = '0';
        $len = strlen($hex);

        for ($i = 1; $i <= $len; $i++) {
            $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
        }

        return $dec;
    }
}
