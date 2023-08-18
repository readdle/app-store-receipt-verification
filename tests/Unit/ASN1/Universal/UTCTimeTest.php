<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Unit\ASN1\Universal;

use DateTimeImmutable;
use Readdle\AppStoreReceiptVerification\ASN1\ASN1Identifier;
use Readdle\AppStoreReceiptVerification\Tests\Unit\Traits\AsBinaryTestTrait;
use Readdle\AppStoreReceiptVerification\Tests\Unit\UnitTestCase;

final class UTCTimeTest extends UnitTestCase
{
    use AsBinaryTestTrait;

    public function testIdentifier(): void
    {
        $this->assertEquals(
            ASN1Identifier::TYPE__UTC_TIME,
            $this->createASN1Object(ASN1Identifier::TYPE__UTC_TIME, '230726141516Z')->getIdentifier()->getType()
        );
    }

    public function test(): void
    {
        $utcTime = $this->createASN1Object(ASN1Identifier::TYPE__UTC_TIME, '230726141516Z');
        $this->assertEquals(new DateTimeImmutable('2023-07-26 14:15:16'), $utcTime->getValue());
        $this->assertEquals('Wed, 26 Jul 2023 14:15:16 +0000', $utcTime->jsonSerialize());
    }

    public function testAsBinary(): void
    {
        $this->performAsBinaryTest(ASN1Identifier::TYPE__UTC_TIME, '230726141516Z');
    }
}
