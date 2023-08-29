<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Unit\ASN1;

use OutOfRangeException;
use Readdle\AppStoreReceiptVerification\ASN1\ASN1ValueLength;
use Readdle\AppStoreReceiptVerification\Tests\Unit\UnitTestCase;
use UnexpectedValueException;

final class ASN1ValueLengthTest extends UnitTestCase
{
    public function testExceptionOnInvalidData(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('offset=0,length=1');
        new ASN1ValueLength($this->createBufferReader(''));
    }

    public function testExceptionOnReserved(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('ASN.1 wrong length octet passed (RESERVED)');
        new ASN1ValueLength($this->createBufferReader(ASN1ValueLength::IS_RESERVED));
    }

    public function testOneByteLength(): void
    {
        foreach ([0, 1, ASN1ValueLength::SHORT_FORM_MAX] as $lengthValue) {
            $length = new ASN1ValueLength($this->createBufferReader($lengthValue));
            $this->assertEquals($lengthValue, $length->getValueLength());
            $this->assertEquals(1, $length->getOwnLength());
            $this->assertFalse($length->isIndefinite());
        }
    }

    public function testMultiBytesLength(): void
    {
        $lengthsToTest = [
            128 => [0b10000000 | 1, 128],
            65535 => [0b10000000 | 2, 0xFF, 0xFF],
        ];

        foreach ($lengthsToTest as $value => $lengthBytes) {
            $length = new ASN1ValueLength($this->createBufferReader($lengthBytes));
            $this->assertEquals($value, $length->getValueLength());
            $this->assertEquals(count($lengthBytes), $length->getOwnLength());
            $this->assertFalse($length->isIndefinite());
        }
    }

    public function testIndefiniteLength(): void
    {
        $length = new ASN1ValueLength($this->createBufferReader(ASN1ValueLength::IS_INDEFINITE));
        $this->assertEquals(0, $length->getValueLength());
        $this->assertEquals(1, $length->getOwnLength());
        $this->assertTrue($length->isIndefinite());
    }
}
