<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7\X501;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object as ASNObject;
use Readdle\AppStoreReceiptVerification\PKCS7\AbstractPKCS7Object;

final class Extensions extends AbstractPKCS7Object
{
    /**
     * @var array<Extension>
     */
    private array $extensions;

    /**
     * @param array<Extension> $extensions
     */
    protected function __construct(array $extensions)
    {
        $this->extensions = $extensions;
    }

    public static function fromASN1Object(ASNObject $object): self
    {
        self::assertASNIdentifierType($object, 'Sequence');

        $extensions = [];

        foreach ($object->getValue() as $extension) {
            $extensions[] = Extension::fromASN1Object($extension);
        }

        return new self($extensions);
    }

    /**
     * @return array<Extension>
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * @return array<Extension>
     */
    public function jsonSerialize(): array
    {
        return $this->getExtensions();
    }
}
