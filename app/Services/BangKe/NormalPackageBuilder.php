<?php

namespace App\Services\BangKe;

use App\DTO\Order;
use App\DTO\Package;

final class NormalPackageBuilder
{
    /**
     * @return Package[]
     */
    public function build(Order $order): array
    {
        if ($order->quantity <= 0) {
            return [];
        }

        if ($order->packageSize <= 0) {
            return [
                new Package(
                    product: $order->product,
                    quantity: $order->quantity,
                    isRemain: true,
                ),
            ];
        }

        $packages = [];

        for ($index = 0; $index < $order->fullPackages(); $index++) {
            $packages[] = new Package(
                product: $order->product,
                quantity: $order->packageSize,
                isRemain: false,
            );
        }

        $remain = $order->remain();

        if ($remain > 0) {
            $packages[] = new Package(
                product: $order->product,
                quantity: $remain,
                isRemain: true,
            );
        }

        return $packages;
    }
}