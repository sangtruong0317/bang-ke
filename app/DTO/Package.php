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
        return "{$this->product} = {$this->quantity}";
    }
}