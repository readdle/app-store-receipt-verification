<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7\X501;

use DateTimeImmutable;
use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\PKCS7\AbstractPKCS7Object;

final class Validity extends AbstractPKCS7Object
{
    private DateTimeImmutable $notBefore;
    private DateTimeImmutable $notAfter;

    protected function __construct(DateTimeImmutable $notBefore, DateTimeImmutable $notAfter)
    {
        $this->notBefore = $notBefore;
        $this->notAfter = $notAfter;
    }

    public function getNotBefore(): DateTimeImmutable
    {
        return $this->notBefore;
    }

    public function getNotAfter(): DateTimeImmutable
    {
        return $this->notAfter;
    }

    public static function fromASN1Object(AbstractASN1Object $object): self
    {
        self::assertASNIdentifierType($object, 'Sequence');
        self::assertASNChildrenStruct($object, [
            'UTC Time',
            'UTC Time',
        ]);

        [$notBefore, $notAfter] = $object->getValue();
        return new self($notBefore->getValue(), $notAfter->getValue());
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'notBefore' => $this->getNotBefore()->format('r'),
            'notAfter' => $this->getNotAfter()->format('r'),
        ];
    }
}
