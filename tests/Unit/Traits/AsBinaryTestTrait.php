<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Unit\Traits;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;

trait AsBinaryTestTrait
{
    protected function performAsBinaryTest(int $type, $value): void
    {
        $binary = $this->createASN1String($type, $value);
        $object = AbstractASN1Object::fromString($binary);
        $this->assertEquals($binary, $object->asBinary());
    }
}
