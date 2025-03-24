<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Math;

use Readdle\AppStoreReceiptVerification\Math\BigInteger\BcmathConverter;
use Readdle\AppStoreReceiptVerification\Math\BigInteger\ConverterInterface;
use Readdle\AppStoreReceiptVerification\Math\BigInteger\GmpConverter;
use Readdle\AppStoreReceiptVerification\Math\BigInteger\NativeConverter;

use function array_map;
use function dechex;
use function function_exists;
use function join;
use function ord;
use function str_pad;
use function str_split;
use function strtoupper;

use const STR_PAD_LEFT;

final class BigInteger
{
    private static ConverterInterface $converter;
    private string $decimalValue;
    private string $hexValue;

    public function __construct(string $binary)
    {
        $this->hexValue = join(array_map(
            fn ($chr) => strtoupper(str_pad(dechex(ord($chr)), 2, '0', STR_PAD_LEFT)),
            str_split($binary)
        ));

        if (!isset(self::$converter)) {
            switch (true) {
                case function_exists('gmp_init'):
                    self::$converter = new GmpConverter();

                    break;

                case function_exists('bcadd'):
                    self::$converter = new BcmathConverter();

                    break;

                default:
                    self::$converter = new NativeConverter();
            }
        }

        $this->decimalValue = self::$converter->hexToDecimal($this->hexValue);
    }

    public static function setConverter(ConverterInterface $converter): void
    {
        self::$converter = $converter;
    }

    public function getDecValue(): string
    {
        return $this->decimalValue;
    }

    public function getHexValue(): string
    {
        return $this->hexValue;
    }
}
