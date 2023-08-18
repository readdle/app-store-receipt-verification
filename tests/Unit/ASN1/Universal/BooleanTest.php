<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Unit\ASN1\Universal;

use Readdle\AppStoreReceiptVerification\ASN1\ASN1Identifier;
use Readdle\AppStoreReceiptVerification\Tests\Unit\Traits\AsBinaryTestTrait;
use Readdle\AppStoreReceiptVerification\Tests\Unit\UnitTestCase;

final class BooleanTest extends UnitTestCase
{
    use AsBinaryTestTrait;

    public function testIdentifier(): void
    {
        $this->assertEquals(
            ASN1Identifier::TYPE__BOOLEAN,
            $this->createASN1Object(ASN1Identifier::TYPE__BOOLEAN, 1)->getIdentifier()->getType()
        );
    }

    public function testTrue(): void
    {
        $this->assertTrue($this->createASN1Object(ASN1Identifier::TYPE__BOOLEAN, 1)->getValue());
        $this->assertTrue($this->createASN1Object(ASN1Identifier::TYPE__BOOLEAN, 42)->getValue());
        $this->assertTrue($this->createASN1Object(ASN1Identifier::TYPE__BOOLEAN, 255)->getValue());
    }

    public function testFalse(): void
    {
        $this->assertFalse($this->createASN1Object(ASN1Identifier::TYPE__BOOLEAN, 0)->getValue());
    }

    public function testJsonSerialize(): void
    {
        $this->assertJsonSerializeResult(ASN1Identifier::TYPE__BOOLEAN, 1, 'true');
        $this->assertJsonSerializeResult(ASN1Identifier::TYPE__BOOLEAN, 0, 'false');
    }

    public function testAsBinary(): void
    {
        $this->performAsBinaryTest(ASN1Identifier::TYPE__BOOLEAN, 1);
    }
}
