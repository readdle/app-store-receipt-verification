<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification;

use Exception;
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
     * @param string $trustedAppleRootCertificate Apple root certificate from trusted source (Apple website). Not used
     * in dev mode
     *
     * @return string JSON representation of parsed receipt
     *
     * @throws Exception In case if verification of receipt failed
     */
    public static function verifyReceipt(string $receiptData, string $trustedAppleRootCertificate): string
    {
        $receiptContainer = new ReceiptContainer(base64_decode($receiptData));
        $receiptContainerVerifier = new ReceiptContainerVerifier($receiptContainer);

        if (!self::$devMode && !$receiptContainerVerifier->verify($trustedAppleRootCertificate)) {
            throw new Exception('Verification failed');
        }

        $appReceipt = $receiptContainer->getReceipt();
        $receipt = Utils::receiptJsonSerialize($appReceipt->getFields(), self::$devMode);
        $receipt['in_app'] = array_map(
            fn (InAppPurchaseReceipt $inAppReceipt) => Utils::receiptJsonSerialize(
                $inAppReceipt->getFields(),
                self::$devMode
            ),
            $appReceipt->getInAppReceipts()
        );
        usort($receipt['in_app'], fn ($r1, $r2) => $r1['purchase_date_ms'] <=> $r2['purchase_date_ms']);

        return json_encode([
            'environment' => preg_match('/Sandbox$/', $receipt['receipt_type']) ? 'Sandbox' : 'Production',
            'receipt' => $receipt,
            'latest_receipt_info' => array_reverse($receipt['in_app']),
            'latest_receipt' => empty($receipt['in_app']) ? '' : $receiptData,
            'pending_renewal_info' => [],
            'status' => 0,
        ]);
    }
}
