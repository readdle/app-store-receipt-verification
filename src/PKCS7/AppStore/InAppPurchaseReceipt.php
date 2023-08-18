<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7\AppStore;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\PKCS7\AbstractPKCS7Object;
use Readdle\AppStoreReceiptVerification\Utils;

use function array_map;

/**
 * @method static self fromASN1String(string $string)
 */
final class InAppPurchaseReceipt extends AbstractPKCS7Object
{
    /**
     * @var array<InAppPurchaseReceiptField>
     */
    private array $fields;

    /**
     * @param array<InAppPurchaseReceiptField> $fields
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return array<InAppPurchaseReceiptField>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public static function fromASN1Object(AbstractASN1Object $object): InAppPurchaseReceipt
    {
        self::assertASNIdentifierType($object, 'Set');

        return new self(array_map(
            fn ($field) => InAppPurchaseReceiptField::fromASN1Object($field),
            $object->getValue()
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return Utils::receiptJsonSerialize($this->getFields());
    }
}
