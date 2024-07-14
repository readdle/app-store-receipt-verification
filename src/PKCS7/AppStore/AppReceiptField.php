<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7\AppStore;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;

/**
 * @method static self fromASN1Object(AbstractASN1Object $object)
 */
final class AppReceiptField extends AbstractField
{
    const TYPE__RECEIPT_TYPE = 0;
    const TYPE__APP_ITEM_ID = 1;
    const TYPE__BUNDLE_ID = 2;
    const TYPE__APPLICATION_VERSION = 3;
    const TYPE__OPAQUE_VALUE = 4;
    const TYPE__SHA1_HASH = 5;
    const TYPE__RECEIPT_CREATION_DATE = 8;
    const TYPE__AGE_RATING = 10;
    const TYPE__REQUEST_DATE = 12;
    const TYPE__DOWNLOAD_ID = 15;
    const TYPE__VERSION_EXTERNAL_IDENTIFIER = 16;
    const TYPE__IN_APP = 17;
    const TYPE__ORIGINAL_PURCHASE_DATE = 18;
    const TYPE__ORIGINAL_APPLICATION_VERSION = 19;
    const TYPE__EXPIRATION_DATE = 21;
    const TYPE__ORGANIZATION_DISPLAY_NAME = 23;
    const TYPE__PREORDER_DATE = 32;

    protected const BINARY_TYPES = [
        self::TYPE__OPAQUE_VALUE,
        self::TYPE__SHA1_HASH,
        6, // ??
        7, // ??
        self::TYPE__IN_APP,
    ];

    protected const DATE_TYPES = [
        self::TYPE__REQUEST_DATE,
        self::TYPE__RECEIPT_CREATION_DATE,
        self::TYPE__ORIGINAL_PURCHASE_DATE,
        self::TYPE__EXPIRATION_DATE,
        self::TYPE__PREORDER_DATE,
    ];

    protected const TYPE_TO_NAME = [
        self::TYPE__RECEIPT_TYPE => 'receipt_type',
        self::TYPE__APP_ITEM_ID => 'app_item_id',
        self::TYPE__BUNDLE_ID => 'bundle_id',
        self::TYPE__APPLICATION_VERSION => 'application_version',
        self::TYPE__OPAQUE_VALUE => 'opaque_value',
        self::TYPE__SHA1_HASH => 'sha1_hash',
        self::TYPE__AGE_RATING => 'age_rating',
        self::TYPE__REQUEST_DATE => 'request_date',
        self::TYPE__RECEIPT_CREATION_DATE => 'receipt_creation_date',
        self::TYPE__DOWNLOAD_ID => 'download_id',
        self::TYPE__VERSION_EXTERNAL_IDENTIFIER => 'version_external_identifier',
        self::TYPE__IN_APP => 'in_app',
        self::TYPE__ORIGINAL_PURCHASE_DATE => 'original_purchase_date',
        self::TYPE__ORIGINAL_APPLICATION_VERSION => 'original_application_version',
        self::TYPE__EXPIRATION_DATE => 'expiration_date',
        self::TYPE__ORGANIZATION_DISPLAY_NAME => 'organization_display_name',
        self::TYPE__PREORDER_DATE => 'preorder_date',
    ];
}
