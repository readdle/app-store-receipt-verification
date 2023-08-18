<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification;

final class BufferPointer
{
    private Buffer $buffer;
    private int $offset;

    public function __construct(Buffer $buffer, int $offset)
    {
        $this->buffer = $buffer;
        $this->offset = $offset;
    }

    public function createReader(): BufferReader
    {
        return new BufferReader($this->buffer, $this->offset);
    }
}
