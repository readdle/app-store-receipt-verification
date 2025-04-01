<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification;

interface ReceiptContainerVerifierInterface
{
    public function __construct(ReceiptContainerInterface $receiptContainer);

    public function verify(string $trustedAppleRootCertificate): bool;
}
