<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Functional\ReceiptExtender;

use Exception;
use PHPUnit\Framework\TestCase;
use Readdle\AppStoreReceiptVerification\ReceiptExtender\AppStoreServerAPIReceiptExtender;
use Readdle\AppStoreServerAPI\AppStoreServerAPI;
use Readdle\AppStoreServerAPI\Exception\WrongEnvironmentException;

final class AppStoreServerAPIReceiptExtenderTest extends TestCase
{
    public function test(): void
    {
        $credentialsFile = dirname(__DIR__, 2) . '/samples/credentials.json';
        $credentials = json_decode(file_get_contents($credentialsFile), true);

        if (
            empty($credentials)
            || empty($credentials['issuerId'])
            || empty($credentials['keyId'])
            || empty($credentials['key'])
        ) {
            $this->fail("No credentials found in $credentialsFile");
        }

        $receiptJson = file_get_contents(dirname(__DIR__, 2) . '/samples/receipt2.json');
        $receipt = json_decode($receiptJson, true);

        try {
            $serverApi = new AppStoreServerAPI(
                $receipt['environment'],
                $credentials['issuerId'],
                $receipt['receipt']['bundle_id'],
                $credentials['keyId'],
                $credentials['key']
            );
        } catch (WrongEnvironmentException $e) {
            $this->fail($e->getMessage());
        }

        $receiptExtender = new AppStoreServerAPIReceiptExtender($serverApi);

        try {
            $extendedReceipt = $receiptExtender->extend($receiptJson);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        echo "Extended receipt:\n\n$extendedReceipt\n\n";
        $this->assertTrue(true);
    }
}
