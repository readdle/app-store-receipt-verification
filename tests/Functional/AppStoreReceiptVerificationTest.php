<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Functional;

use Exception;
use PHPUnit\Framework\TestCase;
use Readdle\AppStoreReceiptVerification\AppStoreReceiptVerification;
use Readdle\AppStoreReceiptVerification\ReceiptContainer;
use Readdle\AppStoreReceiptVerification\Utils;

final class AppStoreReceiptVerificationTest extends TestCase
{
    public function test(): void
    {
        $pathToSamples = join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'samples']);
        $certificate = Utils::DER2PEM(file_get_contents('https://www.apple.com/appleca/AppleIncRootCertificate.cer'));
        $filesList = glob($pathToSamples . DIRECTORY_SEPARATOR . 'receipt?*.base64.txt');

        foreach ($filesList as $file) {
            $filename = basename($file);

            if (!preg_match('/receipt(\d+)\.base64\.txt/', $filename, $m)) {
                continue;
            }

            $base64 = file_get_contents($file);
            AppStoreReceiptVerification::devMode();
            ob_start();

            try {
                echo json_encode(
                    json_decode(AppStoreReceiptVerification::verifyReceipt($base64, $certificate), true),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                );
            } catch (Exception $e) {
                ob_end_clean();
                $this->fail("[$filename]: {$e->getMessage()}");
            }

            file_put_contents($pathToSamples . DIRECTORY_SEPARATOR . "receipt$m[1].json", ob_get_clean());
            ob_start();

            echo json_encode(
                (new ReceiptContainer(base64_decode($base64)))->getContainer(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            );
            file_put_contents($pathToSamples . DIRECTORY_SEPARATOR . "receipt$m[1].dump.json", ob_get_clean());
        }

        $this->assertTrue(true);
    }
}
