<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Tests\Unit\Traits;

use Exception;
use Readdle\AppStoreReceiptVerification\ASN1\ASN1Identifier;

trait StringTestTrait
{
    protected function performStringLengthTests(int $type): void
    {
        $lengthsToTest = [0, 1, 127, 128, 10000, 100000];
        $lengthToBytesCount = function (int $length) {
            $bytesCount = 0;

            while ($length > 0) {
                $bytesCount++;
                $length = (int) floor($length / 256);
            }

            return $bytesCount;
        };

        foreach ($lengthsToTest as $lengthToTest) {
            if ($lengthToTest === 0) {
                $value = '';
            } else {
                // chr(0) is added to make it compatible with bit string,
                // which first byte should be "number of unused bits".
                // other string types don't have such a thing, so for them,
                // it's insignificant which byte is the first
                try {
                    $value = chr(0) . ($lengthToTest === 1 ? '' : random_bytes($lengthToTest - 1));
                } catch (Exception $e) {
                    $this->fail('random_bytes() failed: ' . $e->getMessage());
                }
            }

            $object = $this->createASN1Object($type, $value);
            $length = 1 + ($lengthToTest < 128 ? 1 : (1 + $lengthToBytesCount($lengthToTest))) + $lengthToTest;

            $this->assertEquals($length, $object->getLength());

            if ($type === ASN1Identifier::TYPE__BIT_STRING) {
                // ignoring the first "number of unused bits" byte (see comment above)
                $this->assertEquals(substr($value, 1), $object->getValue());
            } else {
                $this->assertEquals($value, $object->getValue());
            }
        }
    }
}
