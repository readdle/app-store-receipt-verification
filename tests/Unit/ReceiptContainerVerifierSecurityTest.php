<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Readdle\AppStoreReceiptVerification\ReceiptContainer;
use Readdle\AppStoreReceiptVerification\ReceiptContainerVerifier;

/**
 * Regression test for the certificate chain-shift forgery reported against ReceiptContainerVerifier
 * (CWE-295): openssl_x509_verify() only checks a signature, not whether a certificate is entitled to
 * act as a CA and not whether the top of the chain IS the trusted root rather than merely signed by
 * it. That let an attacker present [forgedLeaf, aRealDeveloperCert, WWDR] - a chain shifted up one
 * level - and have it accepted as if it were [receiptLeaf, WWDR, AppleRoot].
 *
 * Fixtures under tests/Fixtures/chain-shift-attack were generated with a throwaway 3-tier PKI
 * (root -> "WWDR" intermediate -> leaf) standing in for Apple's hierarchy:
 * - legit-receipt.der: signed by the intermediate's leaf, chain [leaf, intermediate, root]
 * - forged-receipt.der: signed by a forged leaf whose issuer is an ordinary (CA:FALSE) certificate
 *   that the intermediate legitimately signed, chain [forgedLeaf, developerCert, intermediate] -
 *   i.e. the intermediate sits in the "root" slot, exactly as described in the report
 * Both were produced via `openssl cms -sign -noattr`, then had the certificate SET reordered by
 * exact byte splice into the [signer, issuer, issuer's issuer] order the library assumes.
 */
final class ReceiptContainerVerifierSecurityTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../Fixtures/chain-shift-attack';

    public function testLegitimateChainIsAccepted(): void
    {
        $verifier = $this->createVerifier('legit-receipt.der');
        $this->assertTrue($verifier->verify($this->trustedRootCertificate()));
    }

    public function testChainShiftForgeryIsRejected(): void
    {
        $verifier = $this->createVerifier('forged-receipt.der');
        $this->assertFalse($verifier->verify($this->trustedRootCertificate()));
    }

    private function createVerifier(string $receiptFixture): ReceiptContainerVerifier
    {
        $binaryData = file_get_contents(self::FIXTURES_DIR . '/' . $receiptFixture);
        return new ReceiptContainerVerifier(new ReceiptContainer($binaryData));
    }

    private function trustedRootCertificate(): string
    {
        return file_get_contents(self::FIXTURES_DIR . '/trusted-root.pem');
    }
}
