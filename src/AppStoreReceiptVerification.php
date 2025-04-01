<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification;

use Exception;

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

        $receipt = AppStoreResponseComposer::serializeReceipt($receiptContainer->getReceipt(), self::$devMode);

        return json_encode(AppStoreResponseComposer::serializeVerifyEndpointResponse($receipt, $receiptData));
    }
}
