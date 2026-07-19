<?php

namespace App\DTO;

final readonly class Package
{
    public function __construct(
        public string $product,
        public int $quantity,
        public bool $isRemain,
    ) {}


    public function displayName(): string
    {
        // CHILL đã tạo sẵn chuỗi hoàn chỉnh
    if ($this->quantity === 0) {
        return trim($this->product);
    }

    return trim($this->product) . ' = ' . $this->quantity;
    }
}