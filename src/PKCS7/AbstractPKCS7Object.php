<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7;

use InvalidArgumentException;
use JsonSerializable;
use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;

use function array_merge;
use function count;
use function explode;
use function in_array;
use function strpos;
use function strrpos;
use function substr;

abstract class AbstractPKCS7Object implements JSONSerializable
{
    /**
     * @throws InvalidArgumentException
     */
    abstract public static function fromASN1Object(AbstractASN1Object $object): self;

    public static function fromASN1String(string $string): self
    {
        return static::fromASN1Object(AbstractASN1Object::fromString($string));
    }

    /**
     * @throws InvalidArgumentException
     */
    protected static function assertASNIdentifierType(AbstractASN1Object $object, string $expectedType): void
    {
        if ($object->getIdentifier()->getTypeString() !== $expectedType) {
            self::expectationFailed(
                'ASN object expected to be of type "%s", "%s" passed',
                [$expectedType, $object->getIdentifier()->getTypeString()]
            );
        }
    }

    /**
     * @param array<string> $expectedStruct
     *
     * @throws InvalidArgumentException
     */
    protected static function assertASNChildrenStruct(AbstractASN1Object $object, array $expectedStruct): void
    {
        $children = $object->getValue();

        if (count($children) !== count($expectedStruct)) {
            self::expectationFailed(
                'Number of children expected to be %d, %d passed',
                [count($expectedStruct), count($children)]
            );
        }

        foreach ($expectedStruct as $position => $expectedType) {
            if ($expectedType === '*') {
                $expectationFailed = false;
            } elseif (strpos($expectedType, '|') > 0) {
                $expectationFailed = !in_array(
                    $children[$position]->getIdentifier()->getTypeString(),
                    explode('|', $expectedType)
                );
            } else {
                $expectationFailed = $children[$position]->getIdentifier()->getTypeString() !== $expectedType;
            }

            if ($expectationFailed) {
                self::expectationFailed(
                    'Child at position #%d expected to be of type "%s", "%s" passed',
                    [$position, $expectedType, $children[$position]->getIdentifier()->getTypeString()]
                );
            }
        }
    }

    /**
     * @param array<int|string> $values
     *
     * @throws InvalidArgumentException
     */
    protected static function expectationFailed(string $message, array $values = []): void
    {
        throw new InvalidArgumentException(vsprintf(
            "Expectation failed for '%s': $message",
            array_merge([substr(static::class, strrpos(static::class, '\\') + 1)], $values)
        ));
    }
}
