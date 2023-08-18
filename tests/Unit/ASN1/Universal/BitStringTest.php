<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Unit\ASN1\Universal;

use Readdle\AppStoreReceiptVerification\ASN1\ASN1Identifier;
use Readdle\AppStoreReceiptVerification\Tests\Unit\Traits\AsBinaryTestTrait;
use Readdle\AppStoreReceiptVerification\Tests\Unit\Traits\StringTestTrait;
use Readdle\AppStoreReceiptVerification\Tests\Unit\UnitTestCase;

use function chr;

final class BitStringTest extends UnitTestCase
{
    use AsBinaryTestTrait;
    use StringTestTrait;

    public function testIdentifier(): void
    {
        $this->assertEquals(
            ASN1Identifier::TYPE__BIT_STRING,
            $this->createASN1Object(ASN1Identifier::TYPE__BIT_STRING, 1)->getIdentifier()->getType()
        );
    }

    public function testStringLength(): void
    {
        $this->performStringLengthTests(ASN1Identifier::TYPE__BIT_STRING);
    }

    public function testJsonSerialize(): void
    {
        $this->assertJsonSerializeResult(ASN1Identifier::TYPE__BIT_STRING, '', '');

        // the first byte is "number of ignored bits" and it is not a part of a value
        $this->assertJsonSerializeResult(
            ASN1Identifier::TYPE__BIT_STRING,
            chr(0x00) . chr(0x01) . chr(0x0F) . chr(0xF0) . chr(0xFF),
            '01 0F F0 FF'
        );
    }

    public function testAsBinary(): void
    {
        $this->performAsBinaryTest(ASN1Identifier::TYPE__BIT_STRING, chr(0) . 'test');
    }
}
