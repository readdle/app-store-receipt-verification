<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Readdle\AppStoreReceiptVerification\ReceiptContainer;
use Readdle\AppStoreReceiptVerification\ReceiptContainerVerifier;
use Readdle\AppStoreReceiptVerification\Utils;

final class ReceiptContainerVerifierTest extends TestCase
{
    public function test(): void
    {
        $binaryData = base64_decode(file_get_contents("tmp/receipt2.base64.txt"));
        $rootCertificate = Utils::DER2PEM(file_get_contents('https://www.apple.com/appleca/AppleIncRootCertificate.cer'));
        $receiptContainer = new ReceiptContainer($binaryData);
        $receiptContainerVerifier = new ReceiptContainerVerifier($receiptContainer);
        echo 'Verification: ' . ($receiptContainerVerifier->verify($rootCertificate) ? 'passed' : 'failed') . "\n";
        $this->assertTrue(true);
    }
}
