<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7\X509;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\PKCS7\AbstractPKCS7Object;
use Readdle\AppStoreReceiptVerification\Utils;

final class AlgorithmIdentifier extends AbstractPKCS7Object
{
    private string $algorithm;

    /**
     * @var array<string>|null
     */
    private ?array $parameters;

    /**
     * @param array<string>|null $parameters
     */
    protected function __construct(string $algorithm, ?array $parameters)
    {
        $this->algorithm = $algorithm;
        $this->parameters = $parameters;
    }

    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    /**
     * @return array<string>|null
     */
    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    public static function fromASN1Object(AbstractASN1Object $object): self
    {
        self::assertASNIdentifierType($object, 'Sequence');
        self::assertASNChildrenStruct($object, [
            'Object Identifier',
            'Null',
        ]);

        [$algorithm, $parameters] = $object->getValue();
        return new self($algorithm->getValue(), $parameters->getValue());
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'algorithm' => Utils::stringifyOID($this->getAlgorithm()),
            'parameters' => $this->getParameters(),
        ];
    }
}
