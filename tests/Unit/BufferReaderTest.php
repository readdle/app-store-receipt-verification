<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Unit;

use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use Readdle\AppStoreReceiptVerification\Buffer;
use Readdle\AppStoreReceiptVerification\BufferReader;

use function ord;

final class BufferReaderTest extends TestCase
{
    private BufferReader $bufferReader;

    protected function setUp(): void
    {
        $this->bufferReader = new BufferReader(new Buffer('0123456789'));
    }

    public function testReadOrdinal(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->assertEquals(ord((string) $i), $this->bufferReader->readOrdinal());
        }
    }

    public function testReadNext(): void
    {
        $this->assertEquals('0123', $this->bufferReader->readSequence(4));
        $this->assertEquals('456', $this->bufferReader->readSequence(3));
        $this->assertEquals('78', $this->bufferReader->readSequence(2));
        $this->assertEquals('9', $this->bufferReader->readSequence(1));
    }

    public function testReadNextExceptionOnOutOfRangeLength(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->bufferReader->readSequence(11);
    }

    public function testReadNextExceptionOnOutOfRangeHit(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->bufferReader->readSequence(10);
        $this->bufferReader->readSequence(1);
    }
}
