<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\ASN1\Universal;

use Exception;
use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;

use function array_map;
use function count;
use function dechex;
use function floor;
use function join;
use function ord;
use function str_pad;
use function str_split;
use function strtoupper;

use const STR_PAD_LEFT;

final class Integer extends AbstractASN1Object
{
    protected string $decValue;
    protected string $hexValue;

    /**
     * @throws Exception
     */
    protected function setValue($value): void
    {
        $hexParts = array_map(
            fn ($chr) => strtoupper(str_pad(dechex(ord($chr)), 2, '0', STR_PAD_LEFT)),
            str_split($value)
        );
        $this->hexValue = join(' ', $hexParts);

        /** @noinspection SpellCheckingInspection */
        $decParts = array_map('hexdec', str_split(join($hexParts)));
        $length = count($hexParts) * 2;
        $this->decValue = '';

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
            $this->decValue = $div . $this->decValue;
        } while ($newLength != 0);
    }

    public function getValue(): string
    {
        return $this->decValue;
    }

    public function getIntValue(): int
    {
        return (int) $this->getValue();
    }

    /**
     * @throws Exception
     */
    public function getHexValue(): string
    {
        return $this->hexValue;
    }

    public function jsonSerialize(): string
    {
        return $this->decValue;
    }
}
