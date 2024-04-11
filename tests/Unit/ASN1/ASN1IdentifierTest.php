<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Unit\ASN1;

use OutOfRangeException;
use Readdle\AppStoreReceiptVerification\ASN1\ASN1Identifier;
use Readdle\AppStoreReceiptVerification\Tests\Unit\UnitTestCase;
use UnexpectedValueException;

use function dechex;
use function ucfirst;

final class ASN1IdentifierTest extends UnitTestCase
{
    public function testExceptionOnInvalidData(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('offset=0,length=1');
        new ASN1Identifier($this->createBufferReader(''));
    }

    public function testExceptionOnLongForm(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('ASN.1 long form identifier are not supported (yet?)');
        new ASN1Identifier($this->createBufferReader(ASN1Identifier::IS_LONG_FORM));
    }

    public function testEmptyValue(): void
    {
        $identifier = new ASN1Identifier($this->createBufferReader(0));
        $this->assertFalse($identifier->isContextSpecific());
        $this->assertFalse($identifier->isConstructed());
        $this->assertEquals(1, $identifier->getLength());
    }

    public function testIsContextSpecific(): void
    {
        $bufferReader = $this->createBufferReader(ASN1Identifier::IS_CONTEXT_SPECIFIC << 6);
        $this->assertTrue((new ASN1Identifier($bufferReader))->isContextSpecific());
    }

    public function testIsConstructed(): void
    {
        $bufferReader = $this->createBufferReader(ASN1Identifier::IS_CONSTRUCTED);
        $this->assertTrue((new ASN1Identifier($bufferReader))->isConstructed());
    }

    public function testGetTypeAndGetTypeString(): void
    {
        foreach (ASN1Identifier::TYPE_TO_STRING as $type => $typeString) {
            $simple = new ASN1Identifier($this->createBufferReader($type));
            $this->assertEquals($type, $simple->getType());
            $this->assertEquals($typeString, $simple->getTypeString());

            $contextSpecific = new ASN1Identifier($this->createBufferReader(
                (ASN1Identifier::IS_CONTEXT_SPECIFIC << 6) | $type
            ));

            $this->assertEquals($type, $contextSpecific->getType());
            $this->assertEquals($typeString, $contextSpecific->getTypeString());

            $constructed = new ASN1Identifier($this->createBufferReader(ASN1Identifier::IS_CONSTRUCTED | $type));
            $this->assertEquals($type, $constructed->getType());
            $this->assertEquals($typeString, $constructed->getTypeString());

            $contextSpecificConstructed = new ASN1Identifier($this->createBufferReader(
                (ASN1Identifier::IS_CONTEXT_SPECIFIC << 6) | ASN1Identifier::IS_CONSTRUCTED | $type
            ));
            $this->assertEquals($type, $contextSpecificConstructed->getType());
            $this->assertEquals(
                'Constructed Context-Specific 0x' . strtoupper(dechex($type)),
                $contextSpecificConstructed->getTypeString()
            );
        }
    }
}
