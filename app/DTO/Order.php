<?php

namespace App\DTO;

final readonly class Order
{
    public function __construct(
        public string $truck,
        public string $warehouse,
        public string $status,

        public string $customer,
        public string $product,

        public int $quantity,
        public int $packageSize,
    ) {
    }

    /**
     * Khóa xe + kho.
     */
    public function key(): string
    {
        return "{$this->truck}|{$this->warehouse}";
    }

    /**
     * Số bao đầy.
     */
    public function fullPackages(): int
    {
        if ($this->packageSize <= 0) {
            return 0;
        }

        return intdiv($this->quantity, $this->packageSize);
    }

    /**
     * Bao lẻ.
     */
    public function remain(): int
    {
        if ($this->packageSize <= 0) {
            return 0;
        }

        return $this->quantity % $this->packageSize;
    }
}