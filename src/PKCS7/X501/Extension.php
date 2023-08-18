<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7\X501;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object as ASNObject;
use Readdle\AppStoreReceiptVerification\PKCS7\AbstractPKCS7Object;
use Readdle\AppStoreReceiptVerification\Utils;

use function count;

final class Extension extends AbstractPKCS7Object
{
    private string $id;
    private bool $critical;
    private string $value;

    protected function __construct(string $id, bool $critical, string $value)
    {
        $this->id = $id;
        $this->critical = $critical;
        $this->value = $value;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isCritical(): bool
    {
        return $this->critical;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function fromASN1Object(ASNObject $object): self
    {
        self::assertASNIdentifierType($object, 'Sequence');

        $children = $object->getValue();

        switch (count($children)) {
            case 2:
                [$id, $value] = $children;
                $critical = false;
                break;

            case 3:
                [$id, $critical, $value] = $children;
                break;

            default:
                self::expectationFailed('Extension should consist of an array either of 2 or 3 elements');
        }

        return new self(
            /** @phpstan-ignore-next-line */
            $id->getValue(),
            /** @phpstan-ignore-next-line */
            $critical instanceof ASNObject ? $critical->getValue() : $critical,
            /** @phpstan-ignore-next-line */
            $value->getValue()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => Utils::stringifyOID($this->getId()),
            'critical' => $this->isCritical() ? 'true' : 'false',
            'value' => Utils::formatHexString($this->getValue()),
        ];
    }
}
