<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\ASN1;

use Readdle\AppStoreReceiptVerification\ASN1\Universal\BitString;
use Readdle\AppStoreReceiptVerification\ASN1\Universal\Boolean;
use Readdle\AppStoreReceiptVerification\ASN1\Universal\IA5String;
use Readdle\AppStoreReceiptVerification\ASN1\Universal\Integer;
use Readdle\AppStoreReceiptVerification\ASN1\Universal\Nil;
use Readdle\AppStoreReceiptVerification\ASN1\Universal\ObjectIdentifier;
use Readdle\AppStoreReceiptVerification\ASN1\Universal\OctetString;
use Readdle\AppStoreReceiptVerification\ASN1\Universal\PrintableString;
use Readdle\AppStoreReceiptVerification\ASN1\Universal\Sequence;
use Readdle\AppStoreReceiptVerification\ASN1\Universal\Set;
use Readdle\AppStoreReceiptVerification\ASN1\Universal\UTCTime;
use Readdle\AppStoreReceiptVerification\ASN1\Universal\UTF8String;
use Readdle\AppStoreReceiptVerification\ASN1\Universal\VisibleString;
use Readdle\AppStoreReceiptVerification\BufferReader;
use UnexpectedValueException;

use function array_key_exists;
use function dechex;
use function strtoupper;

final class ASN1Identifier
{
    const TYPE__EOC = 0x00; // special type, means "End-of-contents", see X.690-0207 8.1.3
    const TYPE__BOOLEAN = 0x01;
    const TYPE__INTEGER = 0x02;
    const TYPE__BIT_STRING = 0x03;
    const TYPE__OCTET_STRING = 0x04;
    const TYPE__NULL = 0x05;
    const TYPE__OBJECT_IDENTIFIER = 0x06;
    const TYPE__UTF8_STRING = 0x0C;
    const TYPE__SEQUENCE = 0x10;
    const TYPE__SET = 0x11;
    const TYPE__PRINTABLE_STRING = 0x13;
    const TYPE__IA5_STRING = 0x16;
    const TYPE__UTC_TIME = 0x17;
    const TYPE__VISIBLE_STRING = 0x1A;

    const IS_CONTEXT_SPECIFIC = 0b10;
    const IS_CONSTRUCTED = 0b00100000;
    const IS_LONG_FORM = 0b00011111;

    const TYPE_TO_PHP_CLASS = [
        self::TYPE__BOOLEAN => Boolean::class,
        self::TYPE__INTEGER => Integer::class,
        self::TYPE__BIT_STRING => BitString::class,
        self::TYPE__OCTET_STRING => OctetString::class,
        self::TYPE__NULL => Nil::class, // 'Nil' since we can't use 'Null' as a class name
        self::TYPE__OBJECT_IDENTIFIER => ObjectIdentifier::class,
        self::TYPE__UTF8_STRING => UTF8String::class,
        self::TYPE__SEQUENCE => Sequence::class,
        self::TYPE__SET => Set::class,
        self::TYPE__PRINTABLE_STRING => PrintableString::class,
        self::TYPE__IA5_STRING => IA5String::class,
        self::TYPE__UTC_TIME => UTCTime::class,
        self::TYPE__VISIBLE_STRING => VisibleString::class,
    ];

    const TYPE_TO_STRING = [
        self::TYPE__BOOLEAN => 'Boolean',
        self::TYPE__INTEGER => 'Integer',
        self::TYPE__BIT_STRING => 'Bit String',
        self::TYPE__OCTET_STRING => 'Octet String',
        self::TYPE__NULL => 'Null',
        self::TYPE__OBJECT_IDENTIFIER => 'Object Identifier',
        self::TYPE__UTF8_STRING => 'UTF8 String',
        self::TYPE__SEQUENCE => 'Sequence',
        self::TYPE__SET => 'Set',
        self::TYPE__PRINTABLE_STRING => 'Printable String',
        self::TYPE__IA5_STRING => 'IA5 String',
        self::TYPE__UTC_TIME => 'UTC Time',
        self::TYPE__VISIBLE_STRING => 'Visible String',
    ];

    private int $octet;

    /**
     * @throws UnexpectedValueException
     */
    public function __construct(BufferReader $bufferReader)
    {
        $this->octet = $bufferReader->readOrdinal();

        if (($this->octet & self::IS_LONG_FORM) === self::IS_LONG_FORM) {
            throw new UnexpectedValueException('ASN.1 long form identifier are not supported (yet?)');
        }
    }

    public function isEOC(): bool
    {
        return $this->octet === self::TYPE__EOC;
    }

    public function isContextSpecific(): bool
    {
        // we're interested in 8 & 7 octets, so eliminate others
        return ($this->octet >> 6) === self::IS_CONTEXT_SPECIFIC;
    }

    public function isConstructed(): bool
    {
        return ($this->octet & self::IS_CONSTRUCTED) === self::IS_CONSTRUCTED;
    }

    public function getLength(): int
    {
        // could be changed once long form support is introduced
        return 1;
    }

    public function getType(): int
    {
        // class is encoded by 5-1 octets, so we need to zero 8-6
        return $this->octet & 0b00011111;
    }

    public function getTypeString(): string
    {
        $type = $this->getType();

        if ($this->isConstructed() && $this->isContextSpecific()) {
            return 'Constructed Context-Specific 0x' . strtoupper(dechex($type));
        }

        return self::TYPE_TO_STRING[$type] ?? ('0x' . strtoupper(dechex($type)));
    }

    public function getObjectClass(): string
    {
        $type = $this->getType();

        if (!array_key_exists($type, self::TYPE_TO_PHP_CLASS)) {
            throw new UnexpectedValueException('Unimplemented object type: 0x' . strtoupper(dechex($type)));
        }

        return self::TYPE_TO_PHP_CLASS[$type];
    }
}
