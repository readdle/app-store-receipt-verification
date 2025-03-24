<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Unit\ASN1\Universal;

use Exception;
use Readdle\AppStoreReceiptVerification\ASN1\ASN1Identifier;
use Readdle\AppStoreReceiptVerification\ASN1\Universal\Integer;
use Readdle\AppStoreReceiptVerification\Math\BigInteger;
use Readdle\AppStoreReceiptVerification\Math\BigInteger\BcmathConverter;
use Readdle\AppStoreReceiptVerification\Math\BigInteger\GmpConverter;
use Readdle\AppStoreReceiptVerification\Math\BigInteger\NativeConverter;
use Readdle\AppStoreReceiptVerification\Tests\Unit\Traits\AsBinaryTestTrait;
use Readdle\AppStoreReceiptVerification\Tests\Unit\UnitTestCase;

final class IntegerTest extends UnitTestCase
{
    use AsBinaryTestTrait;

    public function testIdentifier(): void
    {
        $this->assertEquals(
            ASN1Identifier::TYPE__INTEGER,
            $this->createASN1Object(ASN1Identifier::TYPE__INTEGER, 1)->getIdentifier()->getType()
        );
    }

    /**
     * @throws Exception
     */
    public function testSmallNumbers(): void
    {
        /** @var Integer $i0 */
        $i0 = $this->createASN1Object(ASN1Identifier::TYPE__INTEGER, 0);
        $this->assertEquals(0, $i0->getIntValue());
        $this->assertEquals('0', $i0->getValue());
        $this->assertEquals('0', $i0->jsonSerialize());
        $this->assertEquals('00', $i0->getHexValue());

        /** @var Integer $i1 */
        $i1 = $this->createASN1Object(ASN1Identifier::TYPE__INTEGER, 1);
        $this->assertEquals(1, $i1->getIntValue());
        $this->assertEquals('1', $i1->getValue());
        $this->assertEquals('1', $i1->jsonSerialize());
        $this->assertEquals('01', $i1->getHexValue());

        /** @var Integer $i15 */
        $i15 = $this->createASN1Object(ASN1Identifier::TYPE__INTEGER, 15);
        $this->assertEquals(15, $i15->getIntValue());
        $this->assertEquals('15', $i15->getValue());
        $this->assertEquals('15', $i15->jsonSerialize());
        $this->assertEquals('0F', $i15->getHexValue());

        /** @var Integer $i255 */
        $i255 = $this->createASN1Object(ASN1Identifier::TYPE__INTEGER, 255);
        $this->assertEquals(255, $i255->getIntValue());
        $this->assertEquals('255', $i255->getValue());
        $this->assertEquals('255', $i255->jsonSerialize());
        $this->assertEquals('FF', $i255->getHexValue());

        /** @var Integer $i65535 */
        $i65535 = $this->createASN1Object(ASN1Identifier::TYPE__INTEGER, [255, 255]);
        $this->assertEquals(65535, $i65535->getIntValue());
        $this->assertEquals('65535', $i65535->getValue());
        $this->assertEquals('65535', $i65535->jsonSerialize());
        $this->assertEquals('FFFF', $i65535->getHexValue());
    }

    public function testBigNumbers(): void
    {
        $bigIntegers = [
            '297410490623418148084457021309030309957491687369' => [
                0x34, 0x18, 0x58, 0xFF, 0x01, 0xFE, 0x06, 0x3F,
                0x8E, 0xF1, 0x9F, 0x1F, 0xE9, 0x3C, 0x01, 0xB4,
                0xC1, 0x46, 0xFF, 0xC9,
            ],

            '2434711642599936838418079929676142812201287505356'
            . '127448669743750553361348481737541188736615645718'
            . '989288223896217131426030370994739753051323302379'
            . '329352646158502802869540193660826287454332357104'
            . '787170039772749883772728842716497492811898393082'
            . '806015283842831072573860868489284777394418282591'
            . '593245524122103604484611848100889757839534802039'
            . '140438680879407224449203171728849564045559582104'
            . '740577228074195821843718354099768626871204598286'
            . '501929179026607795917726707096420836984919000579'
            . '022471870244207317119690711290566695177854054825'
            . '344631872510607483805655948957929108405721669948'
            . '0821424581721853100982336246602294699649' => [
                0x00, 0xC0, 0xDD, 0xC6, 0xAE, 0xB4, 0xE8, 0xBC,
                0x6D, 0xEB, 0xC1, 0x7D, 0xB5, 0xCE, 0x98, 0x59,
                0xF6, 0x50, 0x58, 0xB6, 0x42, 0xDC, 0x95, 0xE3,
                0x1A, 0xC5, 0xF5, 0xCF, 0x64, 0xAA, 0x7F, 0xD2,
                0xCC, 0x61, 0x4C, 0xD6, 0x14, 0xE3, 0x77, 0x81,
                0xC4, 0x59, 0x31, 0xB7, 0x19, 0xC7, 0x4D, 0x4B,
                0xAB, 0x9D, 0x27, 0xA6, 0x1C, 0x3E, 0x54, 0x72,
                0x50, 0x22, 0x1E, 0x3D, 0x30, 0x1D, 0x4A, 0x0E,
                0x46, 0xE5, 0xD8, 0x26, 0x29, 0x15, 0x4B, 0xE3,
                0x20, 0x9C, 0xAE, 0xD5, 0xF3, 0x25, 0x63, 0xD0,
                0x98, 0xD4, 0x71, 0xF8, 0x6E, 0x96, 0xF3, 0x8E,
                0xA0, 0x0A, 0xC5, 0x04, 0x35, 0xE9, 0x38, 0xF7,
                0xAB, 0xF8, 0x3B, 0x94, 0xDA, 0x07, 0xE5, 0x5B,
                0x39, 0x85, 0xFF, 0x9D, 0x79, 0x40, 0xF4, 0x1A,
                0x0A, 0xAE, 0x95, 0x59, 0x98, 0xD3, 0x1C, 0x5C,
                0x8A, 0x6C, 0x05, 0x7D, 0x9A, 0x1F, 0x34, 0x45,
                0xC1, 0x04, 0xCD, 0xD5, 0xC9, 0x92, 0xA5, 0x46,
                0x17, 0x06, 0xAC, 0xA4, 0x85, 0xF3, 0x77, 0x5C,
                0x1F, 0x85, 0xF7, 0xA3, 0x41, 0xE6, 0xDB, 0x13,
                0x10, 0x6F, 0x6D, 0xF0, 0x21, 0xEA, 0xAC, 0x26,
                0xA6, 0x66, 0x3D, 0x5E, 0x82, 0x15, 0xC7, 0x5C,
                0x9B, 0x29, 0x96, 0x4D, 0x7E, 0x8A, 0x4E, 0x8E,
                0x78, 0xBB, 0x57, 0xC9, 0x35, 0x94, 0x7B, 0xB6,
                0x00, 0xCE, 0x18, 0x0A, 0x8B, 0x75, 0x95, 0x9B,
                0xE9, 0xC3, 0x14, 0x3E, 0xC1, 0x10, 0x34, 0x04,
                0x6D, 0xB7, 0x3C, 0x37, 0x33, 0xDF, 0xCD, 0x14,
                0x3E, 0x62, 0x0C, 0xC0, 0xDF, 0x2F, 0x72, 0xBB,
                0xED, 0x4A, 0xBF, 0xE3, 0xC8, 0x69, 0x0D, 0x7E,
                0x96, 0x6D, 0x1D, 0x4F, 0x10, 0x37, 0x6E, 0xD3,
                0xFC, 0x3D, 0x1A, 0x06, 0x7D, 0x6C, 0x01, 0x14,
                0xC8, 0xC4, 0x5F, 0x31, 0x6A, 0x52, 0xF1, 0x33,
                0x05, 0xC8, 0x60, 0xE3, 0xC6, 0x03, 0xCD, 0x26,
                0x81
            ],
        ];

        $converters = [NativeConverter::class, GmpConverter::class, BcmathConverter::class];

        foreach ($converters as $converter) {
            BigInteger::setConverter(new $converter());

            foreach ($bigIntegers as $string => $binary) {
                $this->assertEquals($string, $this->createASN1Object(ASN1Identifier::TYPE__INTEGER, $binary)->getValue());
            }
        }
    }

    public function testAsBinary(): void
    {
        $this->performAsBinaryTest(ASN1Identifier::TYPE__INTEGER, 31337);
    }
}
