<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Unit\ASN1\Universal;

use Readdle\AppStoreReceiptVerification\ASN1\ASN1Identifier;
use Readdle\AppStoreReceiptVerification\Tests\Unit\Traits\AsBinaryTestTrait;
use Readdle\AppStoreReceiptVerification\Tests\Unit\UnitTestCase;

final class ObjectIdentifierTest extends UnitTestCase
{
    use AsBinaryTestTrait;

    public function testIdentifier(): void
    {
        $this->assertEquals(
            ASN1Identifier::TYPE__OBJECT_IDENTIFIER,
            $this->createASN1Object(ASN1Identifier::TYPE__OBJECT_IDENTIFIER, 1)->getIdentifier()->getType()
        );
    }

    public function test(): void
    {
        $valuesToTest = [
            '1.0' => 0x28,
            '1.1' => 0x29,
            '1.0.1' => [0x28, 0x01],
            '1.1.1' => [0x29, 0x01],
            '1.2.840.113635' => [0x2A, 0x86, 0x48, 0x86, 0xF7, 0x63],
        ];

        foreach ($valuesToTest as $expectedValue => $inputValue) {
            $oid = $this->createASN1Object(ASN1Identifier::TYPE__OBJECT_IDENTIFIER, $inputValue);
            $this->assertEquals($expectedValue, $oid->getValue());
            $this->assertEquals($expectedValue, $oid->jsonSerialize());
        }
    }

    public function testAsBinary(): void
    {
        $this->performAsBinaryTest(ASN1Identifier::TYPE__OBJECT_IDENTIFIER, '1.2.3');
    }
}
