<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\ASN1\Universal;

use Exception;
use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\Math;

use function array_reverse;
use function chunk_split;
use function count;
use function dechex;
use function join;
use function ord;
use function strlen;
use function strtoupper;
use function trim;

final class Integer extends AbstractASN1Object
{
    protected string $value;

    /**
     * @throws Exception
     */
    protected function setValue($value): void
    {
        $length = strlen($value);

        if ($length === 1) {
            $this->value = (string) ord($value);
            return;
        }

        $this->value = '0';

        for ($i = 0; $i < strlen($value); $i++) {
            $this->value = Math::add(Math::mul($this->value, '256'), (string) ord($value[$i]));
        }
    }

    public function getValue(): string
    {
        return $this->value;
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
        $int = $this->getValue();

        if ($int === '0') {
            return '00';
        }

        $hex = [];

        while ($int != '0') {
            $hex[] = strtoupper(dechex((int) Math::mod($int, '16')));
            $int = Math::div($int, '16');
        }

        if (count($hex) % 2) {
            $hex[] = 0;
        }

        return trim(chunk_split(join(array_reverse($hex)), 2, ' '));
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
