<?php

namespace App\DTO;

final class Customer
{
    /**
     * @var Package[]
     */
    private array $packages = [];

    public function __construct(
        public readonly string $name,
    ) {
    }
    public function nextPackageNumber(): int
{
    return count($this->packages) + 1;
}
    /**
     * Thêm một bao.
     */
    public function addPackage(Package $package): void
    {
        $this->packages[] = $package;
    }

    /**
     * Danh sách bao.
     *
     * @return Package[]
     */
    public function packages(): array
    {
        return $this->packages;
    }

    /**
     * Tổng số bao.
     */
    public function packageCount(): int
    {
        return count($this->packages);
    }

    /**
     * Có bao nào không?
     */
    public function isEmpty(): bool
    {
        return empty($this->packages);
    }

    /**
     * STT của từng bao khi xuất Excel.
     */
    public function numberedPackages(): iterable
    {
        $number = 1;

        foreach ($this->packages as $package) {

            yield $number++ => $package;

        }
    }
}