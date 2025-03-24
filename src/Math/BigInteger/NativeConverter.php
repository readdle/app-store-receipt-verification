<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Math\BigInteger;

use function array_map;
use function hexdec;
use function floor;
use function strlen;
use function str_split;

final class NativeConverter implements ConverterInterface
{
    public function hexToDecimal(string $hex): string
    {
        /** @noinspection SpellCheckingInspection */
        $decParts = array_map('hexdec', str_split($hex));
        $length = strlen($hex);
        $decValue = '';

        do {
            $div = 0;
            $newLength = 0;

            for ($i = 0; $i < $length; $i++) {
                $div = $div * 16 + (int) $decParts[$i];

                if ($div >= 10) {
                    $decParts[$newLength++] = floor($div / 10);
                    $div = $div % 10;
                } elseif ($newLength > 0) {
                    $decParts[$newLength++] = 0;
                }
            }

            $length = $newLength;
            $decValue = $div . $decValue;
        } while ($newLength != 0);

        return $decValue;
    }
}
