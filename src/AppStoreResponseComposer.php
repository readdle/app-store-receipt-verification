<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification;

use Readdle\AppStoreReceiptVerification\PKCS7\AppStore\AppReceipt;
use Readdle\AppStoreReceiptVerification\PKCS7\AppStore\InAppPurchaseReceipt;

final class AppStoreResponseComposer
{
    /**
     * @return array<string, mixed>
     */
    public static function serializeReceipt(AppReceipt $appReceipt, bool $includeUnknownFields = false): array
    {
        $receipt = Utils::receiptJsonSerialize($appReceipt->getFields(), $includeUnknownFields);

        if (!empty($receipt['app_item_id'])) {
            $receipt['adam_id'] = $receipt['app_item_id'];
        }

        $receipt['in_app'] = array_map(
            fn (InAppPurchaseReceipt $inAppReceipt) => Utils::receiptJsonSerialize(
                $inAppReceipt->getFields(),
                $includeUnknownFields
            ),
            $appReceipt->getInAppReceipts()
        );

        usort($receipt['in_app'], fn ($r1, $r2) => $r1['purchase_date_ms'] <=> $r2['purchase_date_ms']);

        return $receipt;
    }

    /**
     * @param array<string, mixed> $receipt
     * @return array<string, mixed>
     */
    public static function serializeVerifyEndpointResponse(array $receipt, string $receiptData): array
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

    public static function getEnvironmentFromReceiptType(string $receiptType): string
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
}
