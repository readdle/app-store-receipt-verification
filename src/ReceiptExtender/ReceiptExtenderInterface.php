<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\ReceiptExtender;

interface ReceiptExtenderInterface
{
    /**
     * Extend serialized receipt with missing and/or new data.
     *
     * @param string $serializedReceipt JSON representation of parsed receipt
     * @param bool $mergeNewEntries If true, new entries (transactions/renewal info/etc.) should be added. If false,
     * only existing entries should be updated.
     *
     * @return string JSON representation of extended receipt
     */
    public function extend(string $serializedReceipt, bool $mergeNewEntries = true): string;
}
