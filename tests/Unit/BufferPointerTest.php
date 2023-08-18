<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Unit;

use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use Readdle\AppStoreReceiptVerification\Buffer;
use Readdle\AppStoreReceiptVerification\BufferReader;

final class BufferPointerTest extends TestCase
{
    private BufferReader $bufferReader;

    protected function setUp(): void
    {
        $this->bufferReader = new BufferReader(new Buffer('0123456789'));
    }

    public function test(): void
    {
        $this->bufferReader->readSequence(5);
        $this->assertEquals('56789', $this->bufferReader->createPointer()->createReader()->readSequence(5));
    }

    public function testExceptionOnOutOfRange(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->bufferReader->readSequence(9);
        $readerFromPointer = $this->bufferReader->createPointer()->createReader();
        // this one should be fine
        $readerFromPointer->readSequence(1);
        //this one should throw the exception
        $readerFromPointer->readSequence(1);
    }
}
