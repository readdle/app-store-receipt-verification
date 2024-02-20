<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Functional;

use Exception;
use PHPUnit\Framework\TestCase;
use Readdle\AppStoreReceiptVerification\AppStoreReceiptVerification;
use Readdle\AppStoreReceiptVerification\Utils;

final class AppStoreReceiptVerificationTest extends TestCase
{
    public function test(): void
    {
        $playgroundDir = join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'playground']);
        $certificate = Utils::DER2PEM(file_get_contents('https://www.apple.com/appleca/AppleIncRootCertificate.cer'));

        foreach (['production', 'sandbox', 'xcode', 'unknown'] as $receiptsListName) {
            $basename = join(DIRECTORY_SEPARATOR, [$playgroundDir, $receiptsListName]);
            $filename = realpath($basename . '.json');

            if (!file_exists($filename)) {
                continue;
            }

            $receiptsListJson = file_get_contents($filename);

            if (empty($receiptsListJson)) {
                continue;
            }

            $receiptsList = json_decode($receiptsListJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->fail(sprintf("Malformed JSON (%s) in '$filename'", json_last_error_msg()));
            }

            AppStoreReceiptVerification::devMode(in_array($receiptsListName, ['xcode', 'unknown']));
            $parsedReceiptsList = [];

            foreach ($receiptsList as $i => $receipt) {
                $receiptName = $receipt['name'] ?? "unnamed_$i";

                if (empty($receipt['base64'])) {
                    $this->fail("Receipt '$receiptName' in list '$receiptsListName' does not contain 'base64' prop");
                }

                try {
                    $parsedReceiptsList[$receiptName] = json_decode(AppStoreReceiptVerification::verifyReceipt(
                        $receipt['base64'],
                        $certificate
                    ));
                } catch (Exception $e) {
                    $this->fail("[$filename, $receiptName]: {$e->getMessage()}");
                }
            }

            file_put_contents(
                $basename . '.parsed.json',
                json_encode($parsedReceiptsList, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }

        $this->assertTrue(true);
    }
}
