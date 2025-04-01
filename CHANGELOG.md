### [1.8.1] 2025-04-01

**IMPROVEMENTS:**

- `AppStoreResponseComposer` class introduced and everything related to serialization of decoded receipt to `verifyReceipt`-compatible format moved to that class

### [1.8.0] 2025-03-24

**IMPROVEMENT:**

- Introduced support of [GMP](https://www.php.net/manual/en/book.gmp.php) and [BCMath](https://www.php.net/manual/en/book.bc.php) PHP extensions. Library auto-detects if one of them is installed (preference to GMP). It continues to use native implementation if no extension found. Also, you can choose which one to use manually.

### [1.7.1] 2025-02-19

**BUGFIX:**

- `pending_renewal_info` is now populated from ALL transactions of each subscription group, not only from the first one as it was previously

### [1.7.0] 2024-11-13

**IMPROVEMENT:**

- `latest_receipt` field is now added to Xcode receipts as well (there was an exception for Xcode receipts previously)

### [1.6.2] 2024-09-11

**BUGFIX:**

- `AppStoreServerAPIReceiptExtender::extendReceiptTransactionInfo()`: fixed merging of transaction data

### [1.6.1] 2024-07-14

**IMPROVEMENTS:**

- [ISSUE-4](https://github.com/readdle/app-store-receipt-verification/issues/4): `preorder_date` field added to the App Receipt (kudos to @noemi-salaun)

### [1.6.0] 2024-04-16

**IMPROVEMENTS:**

- [PR-1](https://github.com/readdle/app-store-receipt-verification/pull/1): Added ASN.1 VisibleString type (kudos to @mgd-php)

### [1.5.1] 2024-02-22

**BUGFIX:**

- `Utils::receiptJsonSerialize()`: DateTime field serialization fixed fix, handle possible exception in case of wrong format

### [1.5.0] 2023-08-31

**IMPROVEMENTS:**

- AppStoreReceiptVerification: response composing refactored
- `organization_display_name` VPP field added to the list of known receipt fields
- AppStoreReceiptVerificationTest reorganized, `README` updated

### [1.4.2] 2023-08-30

**BUGFIX:**

- AppStoreServerAPIReceiptExtender: proper handling of 'unknown' subarray (in case of dev mode) added

### [1.4.1] 2023-08-30

**BUGFIX:**

- Let AppStoreServerAPIReceiptExtender throw AppStoreServerAPIException to allow its proper handling

### [1.4.0] 2023-08-29

**IMPROVEMENTS:**

- ASN.1: indefinite length support added
- Thereby StoreKit receipts now supported
- Got rid of necessity of any math extension

### [1.3.0] 2023-08-25

**IMPROVEMENTS:**

- AppStoreServerAPIReceiptExtender: merge strategy changed, now receipt's data won't be overwritten by API response. The reason is that API response doesn't contain `is_trial_period` and `is_in_intro_offer_period`

### [1.2.0] 2023-08-25

**IMPROVEMENTS:**

- Receipt verification fix: receiptCreationDate is now ignored in favor of requestDate

### [1.1.0] 2023-08-23

**IMPROVEMENTS:**

- Receipt verification fix: if no receiptCreationDate found in the receipt, requestDate will be used instead
