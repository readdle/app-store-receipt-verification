<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7\AppStore;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\ASN1\Universal\Integer;
use Readdle\AppStoreReceiptVerification\PKCS7\AbstractPKCS7Object;
use Readdle\AppStoreReceiptVerification\Utils;

use function in_array;

abstract class AbstractField extends AbstractPKCS7Object
{
    protected const BINARY_TYPES = [];

    protected const DATE_TYPES = [];

    protected const TYPE_TO_NAME = [];

    private int $type;
    private int $version;
    private string $value;

    final public function __construct(int $type, int $version, string $value)
    {
        $this->type = $type;
        $this->version = $version;
        $this->value = $value;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getTypeString(): string
    {
        return static::TYPE_TO_NAME[$this->getType()] ?? (string) $this->getType();
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isBinary(): bool
    {
        return self::isBinaryType($this->getType());
    }

    public function isDateTime(): bool
    {
        return in_array($this->getType(), static::DATE_TYPES);
    }
    public static function isBinaryType(int $type): bool
    {
        return in_array($type, static::BINARY_TYPES);
    }

    public static function fromASN1Object(AbstractASN1Object $object): self
    {
        self::assertASNIdentifierType($object, 'Sequence');
        self::assertASNChildrenStruct($object, [
            'Integer',
            'Integer',
            'Octet String',
        ]);

        /** @var Integer $typeObject */
        /** @var Integer $versionObject */
        [$typeObject, $versionObject, $valueObject] = $object->getValue();
        /** @phpstan-ignore-next-line $type */
        $type = $typeObject->getIntValue();
        /** @phpstan-ignore-next-line $type */
        $version = $versionObject->getIntValue();
        $value = $valueObject->getValue();

        if (!self::isBinaryType($type)) {
            $value = AbstractASN1Object::fromString($value)->getValue();
        }

        return new static($type, $version, $value);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->getTypeString(),
            'version' => $this->getVersion(),
            'value' => $this->isBinary() ? Utils::formatHexString($this->getValue()) : $this->getValue(),
        ];
    }
}
