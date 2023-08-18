<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7\X501;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\PKCS7\AbstractPKCS7Object;

final class Name extends AbstractPKCS7Object
{
    /**
     * @var array<string, mixed>
     */
    private array $attributes;

    /**
     * @param array<string, mixed> $attributes
     */
    protected function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public static function fromASN1Object(AbstractASN1Object $object): self
    {
        self::assertASNIdentifierType($object, 'Sequence');

        $relativeDistinguishedNames = $object->getValue();
        $attributes = [];

        foreach ($relativeDistinguishedNames as $relativeDistinguishedName) {
            self::assertASNIdentifierType($relativeDistinguishedName, 'Set');
            $attributeTypeAndDistinguishedValue = $relativeDistinguishedName->getValue()[0];
            self::assertASNIdentifierType($attributeTypeAndDistinguishedValue, 'Sequence');
            self::assertASNChildrenStruct($attributeTypeAndDistinguishedValue, [
                'Object Identifier',
                'Printable String|UTF8 String',
            ]);
            [$type, $value] = $attributeTypeAndDistinguishedValue->getValue();
            $attributes[$type->getValue()] = $value->getValue();
        }

        return new self($attributes);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->getAttributes();
    }
}
