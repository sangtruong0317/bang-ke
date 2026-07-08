<?php

namespace App\DTO;

final class Truck
{
    /**
     * @var array<string, Customer>
     */
    private array $customers = [];

    public function __construct(
        public readonly string $plateNumber,
    ) {
    }

    /**
     * Lấy hoặc tạo Customer theo tên.
     */
    public function customer(string $name): Customer
    {
        if (!isset($this->customers[$name])) {
            $this->customers[$name] = new Customer($name);
        }

        return $this->customers[$name];
    }

    /**
     * @return Customer[]
     */
    public function customers(): array
    {
        return array_values($this->customers);
    }

    /**
     * Sắp xếp khách hàng theo tên.
     */
    public function sortCustomers(): void
    {
        ksort($this->customers, SORT_NATURAL);
    }

    /**
     * Tổng số khách.
     */
    public function customerCount(): int
    {
        return count($this->customers);
    }

    /**
     * Tổng số bao.
     */
    public function packageCount(): int
    {
        $count = 0;

        foreach ($this->customers as $customer) {
            $count += $customer->packageCount();
        }

        return $count;
    }

    /**
     * Có dữ liệu không.
     */
    public function isEmpty(): bool
    {
        return empty($this->customers);
    }
}