<?php

namespace App\Services\BangKe;

use App\DTO\Order;
use App\DTO\Package;

final class ChillPackageBuilder
{
    private const MAX_WEIGHT_KG = 20.0;

    private const PREFERRED_QUANTITIES = [40, 30, 20, 10, 5];

    /**
     * @param Order[] $orders
     * @return Package[]
     */
    public function build(array $orders): array
    {
        $groups = [];

        foreach ($orders as $order) {
            $key = implode('|', [
                $order->product,
            ]);

            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'product' => $order->product,
                    'quantity' => 0,
                ];
            }

            $groups[$key]['quantity'] += $order->quantity;
        }

        $chunks = [];

        foreach ($groups as $group) {
            $unitWeightKg = $this->extractUnitWeightKg(
                $group['product']
            );

            foreach ($this->splitQuantity(
                $group['quantity'],
                $unitWeightKg
            ) as $quantity) {
                $chunks[] = [
                    'product' => $group['product'],
                    'quantity' => $quantity,
                    'unitWeightKg' => $unitWeightKg,
                    'weightKg' => $quantity * $unitWeightKg,
                ];
            }
        }

        $bins = $this->packChunks($chunks);

        $packages = [];

        foreach ($bins as $bin) {
            $parts = [];

            foreach ($bin['items'] as $item) {
                $parts[] = trim($item['product'])
                    . ' = '
                    . $item['quantity'];
            }

            $packages[] = new Package(
                product: implode(' + ', $parts),
                quantity: 0,
                isRemain: false,
            );
        }

        return $packages;
    }

    /**
     * @return int[]
     */
    private function splitQuantity(
        int $quantity,
        float $unitWeightKg
    ): array {
        $result = [];
        $remaining = $quantity;

        while ($remaining > 0) {
            $chunk = $this->chooseChunk(
                $remaining,
                $unitWeightKg
            );

            if ($chunk <= 0) {
                break;
            }

            $result[] = $chunk;
            $remaining -= $chunk;
        }

        return $result;
    }

    private function chooseChunk(
        int $remaining,
        float $unitWeightKg
    ): int {
        foreach (self::PREFERRED_QUANTITIES as $preferred) {
            if (
                $preferred <= $remaining
                && ($preferred * $unitWeightKg) <= self::MAX_WEIGHT_KG
            ) {
                return $preferred;
            }
        }

        $maximumByWeight = max(
            1,
            (int) floor(
                self::MAX_WEIGHT_KG / $unitWeightKg
            )
        );

        return min($remaining, $maximumByWeight);
    }

    /**
     * @param array<int, array{
     *     product:string,
     *     quantity:int,
     *     unitWeightKg:float,
     *     weightKg:float
     * }> $chunks
     *
     * @return array<int, array{
     *     weightKg:float,
     *     items:array
     * }>
     */
    private function packChunks(array $chunks): array
    {
        usort(
            $chunks,
            fn (array $left, array $right): int =>
                $right['weightKg'] <=> $left['weightKg']
        );

        $bins = [];

        foreach ($chunks as $chunk) {
            $bestBinIndex = null;
            $smallestRemaining = PHP_FLOAT_MAX;

            foreach ($bins as $index => $bin) {
                $newWeight = $bin['weightKg'] + $chunk['weightKg'];

                if ($newWeight > self::MAX_WEIGHT_KG) {
                    continue;
                }

                $remaining = self::MAX_WEIGHT_KG - $newWeight;

                if ($remaining < $smallestRemaining) {
                    $smallestRemaining = $remaining;
                    $bestBinIndex = $index;
                }
            }

            if ($bestBinIndex === null) {
                $bins[] = [
                    'weightKg' => $chunk['weightKg'],
                    'items' => [$chunk],
                ];

                continue;
            }

            $this->addChunkToBin(
                $bins[$bestBinIndex],
                $chunk
            );
        }

        return $bins;
    }

    private function addChunkToBin(
        array &$bin,
        array $chunk
    ): void {
        $bin['weightKg'] += $chunk['weightKg'];

        foreach ($bin['items'] as &$item) {
            if ($item['product'] !== $chunk['product']) {
                continue;
            }

            $item['quantity'] += $chunk['quantity'];
            $item['weightKg'] += $chunk['weightKg'];

            return;
        }

        unset($item);

        $bin['items'][] = $chunk;
    }

    private function extractUnitWeightKg(string $product): float
    {
        $normalized = str_replace(',', '.', $product);

        if (preg_match(
            '/(\d+(?:\.\d+)?)\s*KG\b/iu',
            $normalized,
            $match
        )) {
            return max(0.001, (float) $match[1]);
        }

        if (preg_match(
            '/(\d+(?:\.\d+)?)\s*G\b/iu',
            $normalized,
            $match
        )) {
            return max(
                0.001,
                ((float) $match[1]) / 1000
            );
        }

        if (preg_match_all(
            '/(?<!\d)(\d{3})(?!\d)/u',
            $normalized,
            $matches
        )) {
            $grams = (int) end($matches[1]);

            if ($grams >= 100 && $grams <= 999) {
                return $grams / 1000;
            }
        }

        return 0.5;
    }
}