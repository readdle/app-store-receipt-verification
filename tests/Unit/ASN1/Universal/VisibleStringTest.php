<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Unit\ASN1\Universal;

use Readdle\AppStoreReceiptVerification\ASN1\ASN1Identifier;
use Readdle\AppStoreReceiptVerification\Tests\Unit\Traits\AsBinaryTestTrait;
use Readdle\AppStoreReceiptVerification\Tests\Unit\Traits\StringTestTrait;
use Readdle\AppStoreReceiptVerification\Tests\Unit\UnitTestCase;

final class VisibleStringTest extends UnitTestCase
{
    use AsBinaryTestTrait;
    use StringTestTrait;

    public function testIdentifier(): void
    {
        $this->assertEquals(
            ASN1Identifier::TYPE__VISIBLE_STRING,
            $this->createASN1Object(ASN1Identifier::TYPE__VISIBLE_STRING, 1)->getIdentifier()->getType()
        );
    }

    public function testStringLength(): void
    {
        $this->performStringLengthTests(ASN1Identifier::TYPE__VISIBLE_STRING);
    }

    public function testJsonSerialize(): void
    {
        $this->assertJsonSerializeResult(ASN1Identifier::TYPE__VISIBLE_STRING, '', '');
        $this->assertJsonSerializeResult(ASN1Identifier::TYPE__VISIBLE_STRING, 'test', 'test');
    }

    public function testAsBinary(): void
    {
        $this->performAsBinaryTest(ASN1Identifier::TYPE__VISIBLE_STRING, 'test');
    }
}
