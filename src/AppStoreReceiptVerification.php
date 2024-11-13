<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification;

use Exception;
use Readdle\AppStoreReceiptVerification\PKCS7\AppStore\AppReceipt;
use Readdle\AppStoreReceiptVerification\PKCS7\AppStore\InAppPurchaseReceipt;

final class AppStoreReceiptVerification
{
    private static bool $devMode = false;

    /**
     * Turns dev mode on/off.
     * Returns previous state of dev mode.
     *
     * NOTE: Dev mode means that receipts will contain raw data, e.g.: fields which are unrecognized atm.
     */
    public static function devMode(bool $state = true): bool
    {
        $previousState = self::$devMode;
        self::$devMode = $state;
        return $previousState;
    }

    /**
     * Returns current state of dev mode.
     */
    public static function isDevMode(): bool
    {
        return self::$devMode;
    }

    /**
     * Verifies receipt container and returns serialized receipt
     *
     * @param string $receiptData The Base64-encoded receipt data
     * @param string $trustedAppleRootCertificate Apple root certificate from trusted source (e.g. Apple website).
     * Not used in dev mode
     *
     * @return string JSON representation of parsed receipt
     *
     * @throws Exception In case if verification of receipt failed
     */
    public static function verifyReceipt(string $receiptData, string $trustedAppleRootCertificate): string
    {
        $receiptContainer = new ReceiptContainer(base64_decode($receiptData));

        if (!self::$devMode) {
            $receiptContainerVerifier = new ReceiptContainerVerifier($receiptContainer);

            if (!$receiptContainerVerifier->verify($trustedAppleRootCertificate)) {
                throw new Exception('Verification failed');
            }
        }

        $receipt = self::composeReceipt($receiptContainer->getReceipt());

        return json_encode(self::composeReceiptResponse($receipt, $receiptData));
    }

    /**
     * @return array<string, mixed>
     */
    private static function composeReceipt(AppReceipt $appReceipt): array
    {
        $receipt = Utils::receiptJsonSerialize($appReceipt->getFields(), self::$devMode);

        if (!empty($receipt['app_item_id'])) {
            $receipt['adam_id'] = $receipt['app_item_id'];
        }

        $receipt['in_app'] = array_map(
            fn (InAppPurchaseReceipt $inAppReceipt) => Utils::receiptJsonSerialize(
                $inAppReceipt->getFields(),
                self::$devMode
            ),
            $appReceipt->getInAppReceipts()
        );
        usort($receipt['in_app'], fn ($r1, $r2) => $r1['purchase_date_ms'] <=> $r2['purchase_date_ms']);

        return $receipt;
    }

    private static function getEnvironmentFromReceiptType(string $receiptType): string
    {
        switch ($receiptType) {
            case 'Production':
            case 'ProductionVPP':
                return 'Production';

            case 'ProductionSandbox':
            case 'ProductionVPPSandbox':
                return 'Sandbox';

            case 'Xcode':
                return 'Xcode';

            default:
                return '[unknown]';
        }
    }

    /**
     * @param array<string, mixed> $receipt
     * @return array<string, mixed>
     */
    private static function composeReceiptResponse(array $receipt, string $receiptData): array
    {
        $environment = self::getEnvironmentFromReceiptType($receipt['receipt_type']);

        $response = [
            'status' => 0,
            'environment' => $environment,
            'receipt' => $receipt,
        ];

        if (!empty($receipt['in_app'])) {
            $response['latest_receipt'] = trim($receiptData);
            $response['latest_receipt_info'] = array_reverse($receipt['in_app']);
            $response['pending_renewal_info'] = [];
        }

        return $response;
    }
}
