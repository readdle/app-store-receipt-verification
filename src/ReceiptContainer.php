<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification;

use Exception;
use InvalidArgumentException;
use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\PKCS7\AppStore\AppReceipt;
use Readdle\AppStoreReceiptVerification\PKCS7\ContentInfo;
use Readdle\AppStoreReceiptVerification\PKCS7\Data;
use Readdle\AppStoreReceiptVerification\PKCS7\SignedData;
use Readdle\AppStoreReceiptVerification\PKCS7\SignerInfo;
use Readdle\AppStoreReceiptVerification\PKCS7\X509\SignedCertificate;

final class ReceiptContainer
{
    private const EXPECTED_CONTENT_TYPE = ObjectIdentifierTree::PKCS7__SIGNED_DATA;

    private SignedData $signedData;
    private AppReceipt $receipt;

    /**
     * @throws Exception
     */
    public function __construct(string $binaryData)
    {
        $bufferReader = new BufferReader(new Buffer($binaryData));
        $contentInfo = ContentInfo::fromASN1Object(AbstractASN1Object::fromBufferReader($bufferReader));

        if ($contentInfo->getContentType() !== self::EXPECTED_CONTENT_TYPE) {
            throw new InvalidArgumentException(sprintf(
                'Receipt container expects for PKCS #7 ContentInfo with %s, %s passed',
                self::EXPECTED_CONTENT_TYPE,
                $contentInfo->getContentType()
            ));
        }

        /** @var SignedData $data */
        $data = $contentInfo->getData();
        $this->signedData = $data;
    }

    public function getReceiptBinary(): string
    {
        /** @var Data $data */
        $data = $this->signedData->getContentInfo()->getData();
        return $data->getBinaryData();
    }

    public function getContainer(): SignedData
    {
        return $this->signedData;
    }

    public function getReceipt(): AppReceipt
    {
        if (!isset($this->receipt)) {
            $this->receipt = AppReceipt::fromASN1Object(AbstractASN1Object::fromString($this->getReceiptBinary()));
        }

        return $this->receipt;
    }

    /**
     * @return array<SignedCertificate>
     */
    public function getSignedCertificates(): array
    {
        return $this->signedData->getCertificateSet()->getSignedCertificates();
    }

    public function getSignerInfo(): SignerInfo
    {
        return $this->signedData->getSignerInfos()->getSignerInfos()[0];
    }
}
