<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\ObjectIdentifierTree;
use Readdle\AppStoreReceiptVerification\Utils;

final class ContentInfo extends AbstractPKCS7Object
{
    private string $contentType;
    private AbstractData $data;

    protected function __construct(string $contentType, AbstractData $data)
    {
        $this->contentType = $contentType;
        $this->data = $data;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getData(): AbstractData
    {
        return $this->data;
    }

    public static function fromASN1Object(AbstractASN1Object $object): self
    {
        self::assertASNIdentifierType($object, 'Sequence');
        self::assertASNChildrenStruct($object, [
            'Object Identifier',
            'Constructed Context-Specific 0x0',
        ]);

        [$contentType, $data] = $object->getValue();
        $contentTypeValue = $contentType->getValue();

        switch ($contentTypeValue) {
            case ObjectIdentifierTree::PKCS7__DATA:
                $dataClass = Data::class;
                break;

            case ObjectIdentifierTree::PKCS7__SIGNED_DATA:
                $dataClass = SignedData::class;
                break;

            default:
                self::expectationFailed('"ContentType" expected to be "Data" or "SignedData"');
        }

        /** @phpstan-ignore-next-line */
        return new self($contentTypeValue, $dataClass::fromASN1Object($data->getValue()[0]));
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'contentType' => Utils::stringifyOID($this->getContentType()),
            'data' => $this->getData(),
        ];
    }
}
