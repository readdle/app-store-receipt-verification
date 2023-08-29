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
