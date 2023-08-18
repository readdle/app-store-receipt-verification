<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7\X501;

use Exception;
use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\ASN1\Universal\Integer;
use Readdle\AppStoreReceiptVerification\PKCS7\AbstractPKCS7Object;
use Readdle\AppStoreReceiptVerification\PKCS7\X509\AlgorithmIdentifier;

final class SubjectPublicKeyInfo extends AbstractPKCS7Object
{
    private AlgorithmIdentifier $algorithm;
    private string $publicKey;
    private int $exponent;

    protected function __construct(AlgorithmIdentifier $algorithm, string $publicKey, int $exponent)
    {
        $this->algorithm = $algorithm;
        $this->publicKey = $publicKey;
        $this->exponent = $exponent;
    }

    public function getAlgorithm(): AlgorithmIdentifier
    {
        return $this->algorithm;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getExponent(): int
    {
        return $this->exponent;
    }

    /**
     * @throws Exception
     */
    public static function fromASN1Object(AbstractASN1Object $object): self
    {
        self::assertASNIdentifierType($object, 'Sequence');

        [$algorithm, $subjectPublicKey] = $object->getValue();
        self::assertASNIdentifierType($subjectPublicKey, 'Bit String');

        $publicKeyParts = AbstractASN1Object::fromString($subjectPublicKey->getValue());
        self::assertASNChildrenStruct($publicKeyParts, [
            'Integer',
            'Integer',
        ]);

        /** @var Integer $publicKey */
        /** @var Integer $exponent */
        [$publicKey, $exponent] = $publicKeyParts->getValue();
        return new self(
            AlgorithmIdentifier::fromASN1Object($algorithm),
            /** @phpstan-ignore-next-line */
            $publicKey->getHexValue(),
            /** @phpstan-ignore-next-line */
            $exponent->getIntValue()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'algorithm' => $this->getAlgorithm(),
            'key' => $this->getPublicKey(),
            'exponent' => $this->getExponent(),
        ];
    }
}
