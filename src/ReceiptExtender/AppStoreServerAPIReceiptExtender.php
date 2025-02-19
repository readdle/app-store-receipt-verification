<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\ReceiptExtender;

use DateTime;
use DateTimeZone;
use Exception;
use Readdle\AppStoreServerAPI\AppStoreServerAPIInterface;
use Readdle\AppStoreServerAPI\TransactionInfo;

final class AppStoreServerAPIReceiptExtender implements ReceiptExtenderInterface
{
    private AppStoreServerAPIInterface $api;

    public function __construct(AppStoreServerAPIInterface $api)
    {
        $this->api = $api;
    }

    /**
     * @throws Exception
     */
    public function extend(string $serializedReceipt, bool $mergeNewEntries = true): string
    {
        $receipt = json_decode($serializedReceipt, true);

        if (empty($receipt['receipt']['in_app'])) {
            return $serializedReceipt;
        }

        $originalTransactionId = $receipt['receipt']['in_app'][0]['original_transaction_id'];

        $transactionHistory = $this->api->getTransactionHistory($originalTransactionId);
        $transactionsMerged = false;

        foreach ($transactionHistory->getTransactions() as $transaction) {
            $receiptTransactionInfo = self::apiTransactionInfoToReceiptTransactionInfo($transaction->jsonSerialize());
            $transactionExtended = self::extendReceiptTransactionInfo($receipt, $receiptTransactionInfo);

            if (!$transactionExtended && $mergeNewEntries) {
                self::mergeTransactionInfo($receipt, $receiptTransactionInfo);
                $transactionsMerged = true;
            }
        }

        if ($transactionsMerged) {
            self::sortTransactions($receipt);
        }

        $subscriptionGroupIdentifierItems = $this->api->getAllSubscriptionStatuses($originalTransactionId)->getData();

        foreach ($subscriptionGroupIdentifierItems as $subscriptionGroupIdentifierItem) {
            $addedOriginalTransactionIDs = [];
            foreach ($subscriptionGroupIdentifierItem->getLastTransactions() as $transaction) {
                $otrID = $transaction->getOriginalTransactionId();
                if (in_array($otrID, $addedOriginalTransactionIDs)) {
                    continue;
                }

                $renewalInfo = $transaction->getRenewalInfo()->jsonSerialize();
                $receipt['pending_renewal_info'][] = self::renewalInfoToPendingRenewalInfo($renewalInfo);
            }
        }

        return json_encode($receipt);
    }

    /**
     * @param array<string, mixed> $apiTransactionInfo
     * @return array<string, mixed>
     */
    private static function apiTransactionInfoToReceiptTransactionInfo(array $apiTransactionInfo): array
    {
        $fieldsMapping = [
            'expiresDate' => [__CLASS__, 'formatTimestamp'],
            'inAppOwnershipType' => 'in_app_ownership_type',
            'isUpgraded' => 'is_upgraded',
            'offerIdentifier' => 'offer_code_ref_name',
            'offerType' => [__CLASS__, 'offerTypeToFlags'],
            'originalPurchaseDate' => [__CLASS__, 'formatTimestamp'],
            'originalTransactionId' => 'original_transaction_id',
            'productId' => 'product_id',
            'purchaseDate' => [__CLASS__, 'formatTimestamp'],
            'quantity' => 'quantity',
            'revocationDate' => [__CLASS__, 'formatTimestamp'],
            'revocationReason' => 'cancellation_reason',
            'subscriptionGroupIdentifier' => 'subscription_group_identifier',
            'transactionId' => 'transaction_id',
            'webOrderLineItemId' => 'web_order_line_item_id',

        ];

        $receiptTransactionInfo = [];

        foreach ($fieldsMapping as $field => $transformation) {
            if (!array_key_exists($field, $apiTransactionInfo)) {
                continue;
            }

            if (is_callable($transformation)) {
                $receiptTransactionInfo = array_merge(
                    $receiptTransactionInfo,
                    call_user_func($transformation, $apiTransactionInfo[$field], $field)
                );
            } elseif ($apiTransactionInfo[$field] !== null) {
                if (is_bool($apiTransactionInfo[$field])) {
                    $receiptTransactionInfo[$transformation] = $apiTransactionInfo[$field] ? 'true' : 'false';
                } else {
                    $receiptTransactionInfo[$transformation] = (string) $apiTransactionInfo[$field];
                }
            }
        }

        return $receiptTransactionInfo;
    }

    /**
     * @return array<string, int|string>
     * @noinspection PhpSameParameterValueInspection
     */
    private static function formatTimestamp(?int $timestamp, string $name): array
    {
        if ($timestamp === null) {
            return [];
        }

        switch ($name) {
            case 'expiresDate':
                $name = 'expires_date';
                break;

            case 'originalPurchaseDate':
                $name = 'original_purchase_date';
                break;

            case 'purchaseDate':
                $name = 'purchase_date';
                break;

            case 'revocationDate':
                $name = 'cancellation_date';
                break;

            case 'gracePeriodExpiresDate':
                $name = 'grace_period_expires_date';
                break;
        }

        $result = [
            $name => (string) $timestamp,
            $name . '_ms' => (string) $timestamp,
            $name . '_pst' => (string) $timestamp,
        ];

        try {
            $dt = new DateTime('@' . floor($timestamp / 1000));
        } catch (Exception $e) {
            return $result;
        }

        $dt->setTimezone(new DateTimeZone('Etc/GMT'));
        $result[$name] = $dt->format('Y-m-d H:i:s e');

        $dt->setTimezone(new DateTimeZone('America/Los_Angeles'));
        $result[$name . '_pst'] = $dt->format('Y-m-d H:i:s e');

        return $result;
    }

    /**
     * @return array<string, string>
     */
    private static function offerTypeToFlags(?int $offerType): array
    {
        return [
            'is_trial_period' => $offerType === TransactionInfo::OFFER_TYPE__INTRODUCTORY ? 'true' : 'false',
            'is_in_intro_offer_period' => $offerType === TransactionInfo::OFFER_TYPE__PROMOTIONAL ? 'true' : 'false',
        ];
    }

    /**
     * @param array<string, mixed> $receipt
     * @param array<string, mixed> $apiTransactionInfo
     */
    private static function extendReceiptTransactionInfo(array &$receipt, array $apiTransactionInfo): bool
    {
        $updated = [
            'inAppReceipts' => false,
            'latestReceiptInfo' => false,
        ];

        $toUpdate = [
            'inAppReceipts' => &$receipt['receipt']['in_app'],
            'latestReceiptInfo' => &$receipt['latest_receipt_info']
        ];

        foreach ($toUpdate as $subject => &$transactions) {
            foreach ($transactions as &$transaction) {
                if ($transaction['transaction_id'] !== $apiTransactionInfo['transaction_id']) {
                    continue;
                }

                $unknown = $transaction['unknown'] ?? null;
                unset($transaction['unknown']);

                $diff = array_diff_key($apiTransactionInfo, $transaction);

                if ($unknown !== null) {
                    $transaction['unknown'] = $unknown;
                }

                if (!$diff) {
                    continue;
                }

                $transaction = array_merge($diff, $transaction);
                $updated[$subject] = true;
                break;
            }
        }

        return array_reduce($updated, fn (bool $carry, bool $item) => $carry || $item, false);
    }

    /**
     * @param array<string, mixed> $receipt
     * @param array<string, mixed> $transactionInfo
     */
    private static function mergeTransactionInfo(array &$receipt, array $transactionInfo): void
    {
        $receipt['receipt']['in_app'][] = $transactionInfo;
        array_unshift($receipt['latest_receipt_info'], $transactionInfo);
    }

    /**
     * @param array<string, mixed> $receipt
     */
    private static function sortTransactions(array &$receipt): void
    {
        usort($receipt['receipt']['in_app'], fn ($r1, $r2) => $r1['purchase_date_ms'] <=> $r2['purchase_date_ms']);
        usort($receipt['latest_receipt_info'], fn ($r1, $r2) => $r2['purchase_date_ms'] <=> $r1['purchase_date_ms']);
    }

    /**
     * @param array<string, mixed> $renewalInfo
     * @return array<string, mixed>
     */
    private static function renewalInfoToPendingRenewalInfo(array $renewalInfo): array
    {
        $pendingRenewalInfo = [
            'auto_renew_product_id' => $renewalInfo['autoRenewProductId'],
            'auto_renew_status' => (string)$renewalInfo['autoRenewStatus'],
            'original_transaction_id' => $renewalInfo['originalTransactionId'],
            'product_id' => $renewalInfo['productId'],
        ];

        if ($renewalInfo['expirationIntent'] !== null) {
            $pendingRenewalInfo['expiration_intent'] = (string)$renewalInfo['expirationIntent'];
        }

        if ($renewalInfo['gracePeriodExpiresDate'] !== null) {
            $pendingRenewalInfo = array_merge(
                $pendingRenewalInfo,
                self::formatTimestamp($renewalInfo['gracePeriodExpiresDate'], 'gracePeriodExpiresDate')
            );
        }

        if ($renewalInfo['isInBillingRetryPeriod'] !== null) {
            $pendingRenewalInfo['is_in_billing_retry_period'] = $renewalInfo['isInBillingRetryPeriod'] ? '1' : '0';
        }

        if ($renewalInfo['offerIdentifier'] !== null) {
            $pendingRenewalInfo['offer_code_ref_name'] = (string) $renewalInfo['offerIdentifier'];
        }

        if ($renewalInfo['priceIncreaseStatus'] !== null) {
            $pendingRenewalInfo['price_increase_status'] = (string) $renewalInfo['priceIncreaseStatus'];
        }

        return $pendingRenewalInfo;
    }
}
