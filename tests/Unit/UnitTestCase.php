<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\Buffer;
use Readdle\AppStoreReceiptVerification\BufferReader;

use function array_map;
use function chr;
use function floor;
use function is_array;
use function is_int;
use function is_null;
use function is_string;
use function join;
use function strlen;
use function strrev;

abstract class UnitTestCase extends TestCase
{
    /**
     * @param string|array|int $value
     * @return string
     */
    protected function toValueBytes($value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            return join(array_map(fn (int $ord) => chr($ord), $value));
        }

        if (is_int($value)) {
            return chr($value);
        }

        if (is_null($value)) {
            return '';
        }

        $this->fail('Unrecognized incoming value');
    }

    /**
     * @param string|array|int $value
     * @return BufferReader
     */
    protected function createBufferReader($value): BufferReader
    {
        return new BufferReader(new Buffer($this->toValueBytes($value)));
    }

    protected function createASN1String(int $type, $value): string
    {
        $valueBytes = $this->toValueBytes($value);
        $length = strlen($valueBytes);

        if ($length <= 0b01111111) {
            $lengthBytes = chr($length);
        } else {
            $lengthBytes = '';

            while ($length != 0) {
                $lengthBytes .= chr($length % 256);
                $length = floor($length / 256);
            }

            $lengthBytes = chr(strlen($lengthBytes) | 0b10000000) . strrev($lengthBytes);
        }

        return chr($type) . $lengthBytes . $valueBytes;
    }

    protected function createASN1Object(int $type, $value): AbstractASN1Object
    {
        return AbstractASN1Object::fromString($this->createASN1String($type, $value));
    }

    /**
     * @param int $type
     * @param string|array|int $input
     * @param string $output
     */
    public function assertJsonSerializeResult(int $type, $input, string $output): void
    {
        $this->assertEquals($output, $this->createASN1Object($type, $input)->jsonSerialize());
    }
}
