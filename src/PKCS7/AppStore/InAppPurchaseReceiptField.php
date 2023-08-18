<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7\AppStore;

/**
 * @method static self fromASN1Object($field)
 */
final class InAppPurchaseReceiptField extends AbstractField
{
    const TYPE__QUANTITY = 1701;
    const TYPE__PRODUCT_ID = 1702;
    const TYPE__TRANSACTION_ID = 1703;
    const TYPE__PURCHASE_DATE = 1704;
    const TYPE__ORIGINAL_TRANSACTION_ID = 1705;
    const TYPE__ORIGINAL_PURCHASE_DATE = 1706;
    const TYPE__EXPIRES_DATE = 1708;
    const TYPE__WEB_ORDER_LINE_ITEM_ID = 1711;
    const TYPE__CANCELLATION_DATE = 1712;
    const TYPE__IS_TRIAL_PERIOD = 1713;
    const TYPE__IS_IN_INTRO_OFFER_PERIOD = 1719;
    const TYPE__CANCELLATION_REASON = 1720;
    const TYPE__PROMOTIONAL_OFFER_ID = 1721;

    protected const DATE_TYPES = [
        self::TYPE__PURCHASE_DATE,
        self::TYPE__ORIGINAL_PURCHASE_DATE,
        self::TYPE__EXPIRES_DATE,
        self::TYPE__CANCELLATION_DATE,
    ];

    protected const TYPE_TO_NAME = [
        self::TYPE__QUANTITY => 'quantity',
        self::TYPE__PRODUCT_ID => 'product_id',
        self::TYPE__TRANSACTION_ID => 'transaction_id',
        self::TYPE__PURCHASE_DATE => 'purchase_date',
        self::TYPE__ORIGINAL_TRANSACTION_ID => 'original_transaction_id',
        self::TYPE__ORIGINAL_PURCHASE_DATE => 'original_purchase_date',
        self::TYPE__EXPIRES_DATE => 'expires_date',
        self::TYPE__WEB_ORDER_LINE_ITEM_ID => 'web_order_line_item_id',
        self::TYPE__CANCELLATION_DATE => 'cancellation_date',
        self::TYPE__IS_TRIAL_PERIOD => 'is_trial_period',
        self::TYPE__IS_IN_INTRO_OFFER_PERIOD => 'is_in_intro_offer_period',
        self::TYPE__CANCELLATION_REASON => 'cancellation_reason',
        self::TYPE__PROMOTIONAL_OFFER_ID => 'promotional_offer_id',
    ];
}
