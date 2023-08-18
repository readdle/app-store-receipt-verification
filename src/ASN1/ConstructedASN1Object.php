<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\ASN1;

class ConstructedASN1Object extends AbstractASN1Object
{
    /**
     * @var array<int, mixed>
     */
    protected array $value;

    /**
     * @param mixed $value
     */
    protected function setValue($value): void
    {
        $this->value = (array) $value;
    }

    /**
     * @return array<int, mixed>
     */
    public function getValue(): array
    {
        return $this->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $serialized = [];

        foreach ($this->value as $i => $entry) {
            $serialized['[' . $i . '] ' . $entry->getIdentifier()->getTypeString()] = $entry;
        }

        return $serialized;
    }
}
