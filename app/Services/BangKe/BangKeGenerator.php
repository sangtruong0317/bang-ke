<?php

namespace App\Services\BangKe;

use App\DTO\Order;
use App\DTO\Truck;

final class BangKeGenerator
{
    public function __construct(
        private readonly NormalPackageBuilder $normalBuilder,
        private readonly ChillPackageBuilder $chillBuilder,
    ) {
    }

    /**
     * @param Order[] $orders
     * @param string[] $selectedKeys
     * @return Truck[]
     */
    public function generate(
        array $orders,
        array $selectedKeys
    ): array {
        $orders = $this->filter($orders, $selectedKeys);

        $normalOrders = [];
        $specialGroups = [];

        foreach ($orders as $order) {
            $warehouse = mb_strtoupper(
                trim($order->warehouse),
                'UTF-8'
            );

            if ($warehouse === 'CHILL') {
                $key = $order->truck . '|' . $order->customer;

                $specialGroups[$key]['truck'] = $order->truck;
                $specialGroups[$key]['customer'] = $order->customer;
                $specialGroups[$key]['orders'][] = $order;

                continue;
            }

            $normalOrders[] = $order;
        }

        $normalOrders = $this->mergeNormalOrders($normalOrders);

        $trucks = [];

        foreach ($normalOrders as $order) {
            $truck = $this->getTruck($trucks, $order->truck);
            $customer = $truck->customer($order->customer);

            foreach ($this->normalBuilder->build($order) as $package) {
                $customer->addPackage($package);
            }
        }

        foreach ($specialGroups as $group) {
            $truck = $this->getTruck(
                $trucks,
                $group['truck']
            );

            $customer = $truck->customer(
                $group['customer']
            );

            foreach ($this->chillBuilder->build(
                $group['orders']
            ) as $package) {
                $customer->addPackage($package);
            }
        }

        return $this->sort(array_values($trucks));
    }

    /**
     * @param Order[] $orders
     * @param string[] $selectedKeys
     * @return Order[]
     */
    private function filter(
        array $orders,
        array $selectedKeys
    ): array {
        return array_values(array_filter(
            $orders,
            fn (Order $order): bool => in_array(
                $order->key(),
                $selectedKeys,
                true
            )
        ));
    }

    /**
     * @param Order[] $orders
     * @return Order[]
     */
    private function mergeNormalOrders(array $orders): array
    {
        $groups = [];

        foreach ($orders as $order) {
            $key = implode('|', [
                $order->truck,
                $order->customer,
                $order->product,
                $order->packageSize,
            ]);

            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'truck' => $order->truck,
                    'status' => $order->status,
                    'customer' => $order->customer,
                    'product' => $order->product,
                    'quantity' => 0,
                    'packageSize' => $order->packageSize,
                ];
            }

            $groups[$key]['quantity'] += $order->quantity;
        }

        $result = [];

        foreach ($groups as $group) {
            $result[] = new Order(
                truck: $group['truck'],
                warehouse: '',
                status: $group['status'],
                customer: $group['customer'],
                product: $group['product'],
                quantity: $group['quantity'],
                packageSize: $group['packageSize'],
            );
        }

        return $result;
    }

    /**
     * @param array<string, Truck> $trucks
     */
    private function getTruck(
        array &$trucks,
        string $plateNumber
    ): Truck {
        if (! isset($trucks[$plateNumber])) {
            $trucks[$plateNumber] = new Truck(
                plateNumber: $plateNumber
            );
        }

        return $trucks[$plateNumber];
    }

    /**
     * @param Truck[] $trucks
     * @return Truck[]
     */
    private function sort(array $trucks): array
    {
        foreach ($trucks as $truck) {
            $truck->sortCustomers();
        }

        usort(
            $trucks,
            fn (Truck $left, Truck $right): int =>
                strnatcasecmp(
                    $left->plateNumber,
                    $right->plateNumber
                )
        );

        return $trucks;
    }
}