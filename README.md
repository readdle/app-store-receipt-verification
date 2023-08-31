# About

This is a ***zero-dependencies\* pure PHP*** App Store receipt verification library which allows to parse/validate/verify receipts without API calls to `App Store Server API`.

However, the bridge to `App Store Server API` is implemented as well, so it's possible to go event further and extend receipt data using API.

<sub>* Zero-dependencies means that this library doesn't rely on any third-party library. At the same time this library relies on such an essential PHP extensions as `json` and `openssl`</sub>

# Installation

Nothing special here, just use composer to install the package:

> composer install readdle/app-store-receipt-verification

# Usage

Parse base64-encoded receipt data and verify it was signed by Apple root certificate:

```
$appleIncRootCertificate = \Readdle\AppStoreReceiptVerification\Utils::DER2PEM(
    file_get_contents('https://www.apple.com/appleca/AppleIncRootCertificate.cer')
);

$serializedReceipt = \Readdle\AppStoreReceiptVerification\AppStoreReceiptVerification::verifyReceipt(
    $receiptData,
    $appleIncRootCertificate
);
```

Extend receipt with latest info using the bridge to `readdle/app-store-server-api` package:

```
try {
    $serverApi = new \Readdle\AppStoreServerAPI\AppStoreServerAPI(
        'Production',
        '1a2b3c4d-1234-4321-1111-1a2b3c4d5e6f',
        'com.readdle.MyBundle',
        'ABC1234DEF',
        "-----BEGIN PRIVATE KEY-----\n<base64-encoded private key goes here>\n-----END PRIVATE KEY-----"
    );
} catch (\Readdle\AppStoreServerAPI\Exception\WrongEnvironmentException $e) {
    exit($e->getMessage());
}

$receiptExtender = new \Readdle\AppStoreReceiptVerification\ReceiptExtender\AppStoreServerAPIReceiptExtender($serverApi);
$mergeNewEntries = true;

try {
    $extendedReceipt = $receiptExtender->extend($serializedReceipt, $mergeNewEntries);
} catch (\Exception $e) {
    exit($e->getMessage());
}
```

# Self-signed StoreKit receipts

Since version 1.4.0 self-signed `StoreKit` receipts are also supported. Note, that such receipts contain very limited amount of data if compare to sandbox/production receipts, and **they could NOT be verified, so parse them in dev mode (see below) ONLY**.

# About the content of receipts

Unfortunately, App Store receipts doesn't contain all the information returned by deprecated `App Store Receipt Verification` API inside on them.

At the same time they contain some extra fields which are, probably, not so useful, but as they are there anyway, you'll get them in the result set as well.

The list of missing fields in the in-app purchase receipt:

- `app_account_token`
- `in_app_ownership_type`
- `offer_code_ref_name`
- `subscription_group_identifier`

The list of extra fields in the app receipt:

- `age_rating`
- `opaque_value`
- `sha1_hash`

# Extending receipts

A bit ~~funny~~ annoying but `App Store Server API` returns **NOT** as detailed set of information as you could find in the response of `App Store Receipt Verification` API ¯\_(ツ)_/¯

Thus, receipts extended with info from `App Store Server API` contains the most amount of information available.

# Merging new entries in receipts

The second argument of `$receiptExtender->extend()` methods is a boolean flag, indicating if you want to merge *new* entries into the `in_app`/`latest_receipt_info` arrays.

*New* means those transactions which are not present in the receipt itself, but are available through API (for example, when you're dealing with outdated receipt).  Note, that in this case the set of information about each new transaction is limited to what is available from API.

# Dev mode

You can turn on dev mode using this call:

```
\Readdle\AppStoreReceiptVerification\AppStoreReceiptVerification::devMode();
```

In dev mode **no receipt container check will be performed**, so use it **ONLY** for development purposes or in tests.

Additionally, in dev mode there will be property called `unknown` in both app and in-app purchase receipts. In this array you can find all unrecognized fields containing in binary receipt data.

In case you know what does any of them mean, please, contact me, I will update the library :)

# Tests

In `tests/` directory you can find some tests.

The most useful for you will be `tests/Functional/AppStoreReceiptVerificationTest.php`.

This test looks into `tests/playground/` directory searching for four files (you don't have to create all four, just those which you need): `production.json`, `sandbox.json`, `xcode.json` and `unknown.json`. An expected structure of all of them is the same:

```
[
    {
        "name": "any name for your receipt",
        "base64": "...base64-encoded receipt data..."
    },
    {
        "name": "any name for your receipt",
        "base64": "...base64-encoded receipt data..."
    },
    ...
]
```
<sub>NOTE: each hash can contain any additional key/value pairs, these two are the only which are used</sub>

Each file can contain as many entries as you want. Separation for `production`/`sandbox`/`xcode`/`unknown` is just to make management of test receipts a bit more convenient. However, there is a difference, `xcode` and `unknown` lists will be parsed in dev mode (because it's impossible to verify self-signed receipts, and `unknown`, as followed from its name, can contain self-signed receipts as well).

This test will result in creation of `production.parsed.json`, `sandbox.parsed.json`, `xcode.parser.json` and `unknown.parsed.json`. Each of them will contain a hash, where a key will be the name of the receipt (`name` in source file OR `unknown_X` in case if `name` is omitted, where X is an index number of the receipt in source file) and the value will be parsed receipt data (the same as you get from `AppStoreReceipVerification::verifyReceipt()`).

# External links

#### [Validating receipts on the device](https://developer.apple.com/documentation/appstorereceipts/validating_receipts_on_the_device)
#### [Receipt Validation Programming Guide](https://developer.apple.com/library/archive/releasenotes/General/ValidateAppStoreReceipt/Chapters/ReceiptFields.html#//apple_ref/doc/uid/TP40010573-CH106-SW1)
#### [A Layman's Guide to a Subset of ASN.1, BER, and DER](https://luca.ntop.org/Teaching/Appunti/asn1.html)
#### [ASN.1 Made Simple — What is ASN.1?](https://www.oss.com/asn1/resources/asn1-made-simple/introduction.html)
#### [Information technology – ASN.1 encoding rules: Specification of Basic Encoding Rules (BER), Canonical Encoding Rules (CER) and Distinguished Encoding Rules (DER)](https://www.itu.int/ITU-T/studygroups/com17/languages/X.690-0207.pdf)
#### [Module PKCS7 (X.420:06/1999)](https://www.itu.int/ITU-T/formal-language/itu-t/x/x420/1999/PKCS7.html)
#### [Module AuthenticationFramework (X.509:08/1997)](https://www.itu.int/ITU-T/formal-language/itu-t/x/x509/1997/AuthenticationFramework.html)
