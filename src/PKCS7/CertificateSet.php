<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\PKCS7\X509\SignedCertificate;

final class CertificateSet extends AbstractData
{
    /**
     * @var array<SignedCertificate>
     */
    private array $signedCertificates;

    /**
     * @param array<SignedCertificate> $signedCertificates
     */
    protected function __construct(array $signedCertificates)
    {
        $this->signedCertificates = $signedCertificates;
    }

    /**
     * @return array<SignedCertificate>
     */
    public function getSignedCertificates(): array
    {
        return $this->signedCertificates;
    }

    public static function fromASN1Object(AbstractASN1Object $object): self
    {
        self::assertASNIdentifierType($object, 'Constructed Context-Specific 0x0');

        $signedCertificates = [];

        foreach ($object->getValue() as $signedCertificate) {
            $signedCertificates[]  = SignedCertificate::fromASN1Object($signedCertificate);
        }

        return new self($signedCertificates);
    }

    /**
     * @return array<SignedCertificate>
     */
    public function jsonSerialize(): array
    {
        return $this->getSignedCertificates();
    }
}
