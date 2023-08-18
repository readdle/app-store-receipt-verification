<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification;

use function ord;

final class BufferReader
{
    private Buffer $buffer;
    private int $offset;

    public function __construct(Buffer $buffer, int $offset = 0)
    {
        $this->buffer = $buffer;
        $this->offset = $offset;
    }

    public function createPointer(): BufferPointer
    {
        return new BufferPointer($this->buffer, $this->offset);
    }

    public function readSequence(int $length): string
    {
        $slice = $this->buffer->read($this->offset, $length);
        $this->offset += $length;
        return $slice;
    }

    public function readOrdinal(): int
    {
        return ord($this->buffer->read($this->offset++, 1));
    }
}
