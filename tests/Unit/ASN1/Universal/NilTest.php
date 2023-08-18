<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Unit\ASN1\Universal;

use Readdle\AppStoreReceiptVerification\ASN1\ASN1Identifier;
use Readdle\AppStoreReceiptVerification\Tests\Unit\Traits\AsBinaryTestTrait;
use Readdle\AppStoreReceiptVerification\Tests\Unit\UnitTestCase;

final class NilTest extends UnitTestCase
{
    use AsBinaryTestTrait;

    public function testIdentifier(): void
    {
        $this->assertEquals(
            ASN1Identifier::TYPE__NULL,
            $this->createASN1Object(ASN1Identifier::TYPE__NULL, null)->getIdentifier()->getType()
        );
    }

    public function testNull(): void
    {
        $this->assertNull($this->createASN1Object(ASN1Identifier::TYPE__NULL, null)->getValue());
    }

    public function testJsonSerialize(): void
    {
        $this->assertJsonSerializeResult(ASN1Identifier::TYPE__NULL, 0, 'null');
    }

    public function testAsBinary(): void
    {
        $this->performAsBinaryTest(ASN1Identifier::TYPE__NULL, null);
    }
}
