<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification;

interface ReceiptContainerVerifierInterface
{
    public function __construct(ReceiptContainer $receiptContainer);

    public function verify(string $trustedAppleRootCertificate): bool;
}
