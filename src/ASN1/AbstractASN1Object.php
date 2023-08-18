<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\ASN1;

use DateTimeImmutable;
use JsonSerializable;
use Readdle\AppStoreReceiptVerification\Buffer;
use Readdle\AppStoreReceiptVerification\BufferPointer;
use Readdle\AppStoreReceiptVerification\BufferReader;

abstract class AbstractASN1Object implements JSONSerializable
{
    private ASN1Identifier $identifier;
    private BufferPointer $pointer;
    private int $length;

    protected function __construct(ASN1Identifier $identifier, BufferPointer $pointer, int $length)
    {
        $this->identifier = $identifier;
        $this->pointer = $pointer;
        $this->length = $length;
    }

    public function getIdentifier(): ASN1Identifier
    {
        return $this->identifier;
    }

    public function asBinary(): string
    {
        return $this->pointer->createReader()->readSequence($this->getLength());
    }

    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    abstract protected function setValue($value);

    /**
     * @return null|bool|int|string|DateTimeImmutable|array<AbstractASN1Object>
     */
    abstract public function getValue();

    public static function fromBufferReader(BufferReader $bufferReader): self
    {
        $pointer = $bufferReader->createPointer();
        $identifier = new ASN1Identifier($bufferReader);
        $isConstructed = $identifier->isConstructed();
        $isContextSpecific = $identifier->isContextSpecific();

        $length = new ASN1ValueLength($bufferReader);
        $valueLength = $length->getValueLength();
        $totalLength = $identifier->getLength() + $length->getOwnLength() + $valueLength;

        $class = $isConstructed && $isContextSpecific ? ConstructedASN1Object::class : $identifier->getObjectClass();
        /** @var AbstractASN1Object $object */
        $object = new $class($identifier, $pointer, $totalLength);

        if ($isConstructed) {
            $toRead = $valueLength;
            $children = [];

            while ($toRead > 0) {
                $child = self::fromBufferReader($bufferReader);
                $toRead -= $child->getLength();
                $children[] = $child;
            }

            $object->setValue($children);
        } elseif ($valueLength > 0) {
            $object->setValue($bufferReader->readSequence($valueLength));
        }

        return $object;
    }

    public static function fromString(string $string): self
    {
        return self::fromBufferReader(new BufferReader(new Buffer($string)));
    }
}
