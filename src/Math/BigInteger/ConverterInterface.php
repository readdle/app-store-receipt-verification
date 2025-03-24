<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\Math\BigInteger;

interface ConverterInterface
{
    public function hexToDecimal(string $hex): string;
}
