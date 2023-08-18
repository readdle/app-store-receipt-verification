<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\PKCS7\X509\AlgorithmIdentifier;

final class DigestAlgorithmIdentifiers extends AbstractPKCS7Object
{
    /**
     * @var array<AlgorithmIdentifier>
     */
    private array $algorithms;

    /**
     * @param array<AlgorithmIdentifier> $algorithms
     */
    protected function __construct(array $algorithms)
    {
        $this->algorithms = $algorithms;
    }

    public static function fromASN1Object(AbstractASN1Object $object): self
    {
        self::assertASNIdentifierType($object, 'Set');

        $algorithms = [];

        foreach ($object->getValue() as $identifier) {
            $algorithms[] = AlgorithmIdentifier::fromASN1Object($identifier);
        }

        return new self($algorithms);
    }

    /**
     * @return array<AlgorithmIdentifier>
     */
    public function getAlgorithms(): array
    {
        return $this->algorithms;
    }

    /**
     * @return array<AlgorithmIdentifier>
     */
    public function jsonSerialize(): array
    {
        return $this->getAlgorithms();
    }
}
