<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Unit;

use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use Readdle\AppStoreReceiptVerification\Buffer;

final class BufferTest extends TestCase
{
    private Buffer $buffer;

    protected function setUp(): void
    {
        $this->buffer = new Buffer('0123456789');
    }

    public function testRead(): void
    {
        $this->assertEquals('0', $this->buffer->read(0, 1));
        $this->assertEquals('01', $this->buffer->read(0, 2));
        $this->assertEquals('456', $this->buffer->read(4, 3));
        $this->assertEquals('89', $this->buffer->read(8, 2));
        $this->assertEquals('9', $this->buffer->read(9, 1));

        $this->assertEquals('0123456789', $this->buffer->read(0, 10));
    }

    public function testReadExceptionOnOutOfRangeNegativeOffset(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->buffer->read(-1, 1);
    }

    public function testReadExceptionOnOutOfRangeZeroLength(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->buffer->read(0, 0);
    }

    public function testReadExceptionOnOutOfRangeTooBigOffset(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->buffer->read(10, 1);
    }

    public function testReadExceptionOnOutOfRangeTooBigLength(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->buffer->read(1, 10);
    }

    public function testReadExceptionOnOutOfRangeTooBigOffsetAndLength(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->buffer->read(10, 1);
    }
}
