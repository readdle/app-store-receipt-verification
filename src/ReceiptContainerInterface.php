<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification;

use Readdle\AppStoreReceiptVerification\PKCS7\AppStore\AppReceipt;
use Readdle\AppStoreReceiptVerification\PKCS7\SignedData;
use Readdle\AppStoreReceiptVerification\PKCS7\SignerInfo;
use Readdle\AppStoreReceiptVerification\PKCS7\X509\SignedCertificate;

interface ReceiptContainerInterface
{
    public function __construct(string $binaryData);

    public function getReceiptBinary(): string;

    public function getContainer(): SignedData;

    public function getReceipt(): AppReceipt;

    /**
     * @return SignedCertificate[]
     */
    public function getSignedCertificates(): array;

    public function getSignerInfo(): SignerInfo;
}
