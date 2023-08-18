<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7\AppStore;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object as ASNObject;
use Readdle\AppStoreReceiptVerification\PKCS7\AbstractPKCS7Object;
use Readdle\AppStoreReceiptVerification\Utils;

final class AppReceipt extends AbstractPKCS7Object
{
    /**
     * @var array<AppReceiptField>
     */
    private array $fields;

    /**
     * @var array<InAppPurchaseReceipt>
     */
    private array $inAppReceipts;

    /**
     * @param array<AppReceiptField> $fields
     * @param array<InAppPurchaseReceipt> $inAppReceipts
     */
    public function __construct(array $fields, array $inAppReceipts)
    {
        $this->fields = $fields;
        $this->inAppReceipts = $inAppReceipts;
    }

    /**
     * @return array<AppReceiptField>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array<InAppPurchaseReceipt>
     */
    public function getInAppReceipts(): array
    {
        return $this->inAppReceipts;
    }

    public function getFieldByType(int $type): ?AppReceiptField
    {
        foreach ($this->fields as $field) {
            if ($field->getType() === $type) {
                return $field;
            }
        }

        return null;
    }

    public static function fromASN1Object(ASNObject $object): self
    {
        self::assertASNIdentifierType($object, 'Set');

        $fields = [];
        $inAppReceipts = [];

        foreach ($object->getValue() as $child) {
            $field = AppReceiptField::fromASN1Object($child);

            if ($field->getType() === AppReceiptField::TYPE__IN_APP) {
                $inAppReceipts[] = InAppPurchaseReceipt::fromASN1String($field->getValue());
            } else {
                $fields[] = $field;
            }
        }

        return new self($fields, $inAppReceipts);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $receipt = Utils::receiptJsonSerialize($this->getFields());
        $receipt['in_app'] = $this->getInAppReceipts();
        return $receipt;
    }
}
