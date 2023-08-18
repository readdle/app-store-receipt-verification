<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification;

use OutOfRangeException;

use function substr;

final class Buffer
{
    private string $binary;
    private int $length;

    public function __construct(string $binary)
    {
        $this->binary = $binary;
        $this->length = strlen($binary);
    }

    /**
     * @throws OutOfRangeException
     */
    public function read(int $offset, int $length): string
    {
        if ($offset < 0 || $length < 1 || $offset + $length > $this->length) {
            throw new OutOfRangeException("offset=$offset,length=$length");
        }

        return substr($this->binary, $offset, $length);
    }
}
