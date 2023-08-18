<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;

final class SignerInfos extends AbstractPKCS7Object
{
    /**
     * @var array<SignerInfo>
     */
    private array $signerInfos;

    /**
     * @param array<SignerInfo> $signerInfos
     */
    protected function __construct(array $signerInfos)
    {
        $this->signerInfos = $signerInfos;
    }

    public static function fromASN1Object(AbstractASN1Object $object): self
    {
        self::assertASNIdentifierType($object, 'Set');

        $signerInfos = [];

        foreach ($object->getValue() as $signerInfo) {
            $signerInfos[] = SignerInfo::fromASN1Object($signerInfo);
        }

        return new self($signerInfos);
    }

    /**
     * @return array<SignerInfo>
     */
    public function getSignerInfos(): array
    {
        return $this->signerInfos;
    }

    /**
     * @return array<SignerInfo>
     */
    public function jsonSerialize(): array
    {
        return $this->getSignerInfos();
    }
}
