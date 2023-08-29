<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\ASN1;

use DateTimeImmutable;
use Exception;
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

    /**
     * @throws Exception
     */
    public static function fromBufferReader(BufferReader $bufferReader): ?self
    {
        $pointer = $bufferReader->createPointer();
        $identifier = new ASN1Identifier($bufferReader);
        $isConstructed = $identifier->isConstructed();
        $isContextSpecific = $identifier->isContextSpecific();

        $length = new ASN1ValueLength($bufferReader);
        $valueLength = $length->getValueLength();

        if ($identifier->isEOC()) {
            if ($valueLength !== 0) {
                throw new Exception('ASN.1: unexpected EOC identifier with non-zero length');
            }

            return null;
        }

        if ($isConstructed) {
            $toRead = $valueLength;
            $isIndefinite = $length->isIndefinite();
            $value = [];

            while ($toRead > 0 || $isIndefinite) {
                $child = self::fromBufferReader($bufferReader);

                if ($child === null) {
                    if ($isIndefinite) {
                        break;
                    }

                    throw new Exception("ASN.1: unexpected NULL child in {$identifier->getTypeString()}");
                }

                $childLength = $child->getLength();
                $toRead -= $childLength;

                if ($isIndefinite) {
                    $valueLength += $childLength;
                }

                $value[] = $child;
            }
        } elseif ($valueLength > 0) {
            $value = $bufferReader->readSequence($valueLength);
        } else {
            $value = null;
        }

        $totalLength = $identifier->getLength() + $length->getOwnLength() + $valueLength;
        $class = ($isConstructed && $isContextSpecific) ? ConstructedASN1Object::class : $identifier->getObjectClass();
        $object = new $class($identifier, $pointer, $totalLength);

        if ($value !== null) {
            if ((!$object instanceof ConstructedASN1Object) && is_array($value)) {
                if (count($value) > 1) {
                    throw new Exception('ASN.1: unexpected primitive type with non-primitive value');
                }

                $value = $value[0]->getValue();
            }

            $object->setValue($value);
        }

        return $object;
    }

    public static function fromString(string $string): self
    {
        return self::fromBufferReader(new BufferReader(new Buffer($string)));
    }
}
