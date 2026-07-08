<?php

namespace App\Services\BangKe;

use App\DTO\Customer;
use App\DTO\Order;
use App\DTO\Package;
use App\DTO\Truck;

final class BangKeGenerator
{
    /**
     * @param Order[] $orders
     * @param string[] $selectedKeys
     * @return Truck[]
     */
    public function generate(
        array $orders,
        array $selectedKeys
    ): array {

        $orders = $this->filter(
            $orders,
            $selectedKeys
        );

        $orders = $this->merge(
            $orders
        );

        return $this->sort(
    $this->build($orders)
);
    }

    /**
     * Lọc theo xe + kho được chọn.
     *
     * @param Order[] $orders
     * @param string[] $selectedKeys
     * @return Order[]
     */
    private function filter(
        array $orders,
        array $selectedKeys
    ): array {

        return array_values(

            array_filter(

                $orders,

                fn (Order $order) => in_array(
                    $order->key(),
                    $selectedKeys,
                    true
                )

            )

        );

    }

    /**
     * Gộp các dòng cùng:
     *
     * - Xe
     * - Khách hàng
     * - Sản phẩm
     * - Quy cách
     *
     * Sau khi filter thì KHO không còn ý nghĩa nữa.
     *
     * @param Order[] $orders
     * @return Order[]
     */
    private function merge(array $orders): array
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

                    'truck'       => $order->truck,
                    'warehouse'   => '',
                    'status'      => $order->status,
                    'customer'    => $order->customer,
                    'product'     => $order->product,
                    'quantity'    => 0,
                    'packageSize' => $order->packageSize,

                ];

            }

            $groups[$key]['quantity'] += $order->quantity;

        }

        $result = [];

        foreach ($groups as $row) {

            $result[] = new Order(

                truck: $row['truck'],
                warehouse: '',
                status: $row['status'],
                customer: $row['customer'],
                product: $row['product'],
                quantity: $row['quantity'],
                packageSize: $row['packageSize'],

            );

        }

        return $result;
    }
        /**
     * Xây dựng cây dữ liệu Truck -> Customer -> Package
     *
     * @param Order[] $orders
     * @return Truck[]
     */
    private function build(array $orders): array
    {
        /**
         * @var array<string, Truck> $trucks
         */
        $trucks = [];

        foreach ($orders as $order) {

            if (! isset($trucks[$order->truck])) {

                $trucks[$order->truck] = new Truck(
                    plateNumber: $order->truck
                );

            }

            $truck = $trucks[$order->truck];

            $customer = $truck->customer(
                $order->customer
            );

            foreach ($this->packages($order) as $package) {

                $customer->addPackage($package);

            }

        }

        return array_values($trucks);
    }

    /**
     * Chia một Order thành nhiều Package.
     *
     * Ví dụ:
     *
     * 110 / 20
     *
     * =>
     *
     * 20
     * 20
     * 20
     * 20
     * 20
     * 10
     *
     * @return Package[]
     */
    private function packages(Order $order): array
    {
        $packages = [];

        if ($order->packageSize <= 0) {
            return [];
        }

        $full = $order->fullPackages();

        for ($i = 0; $i < $full; $i++) {

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
        /**
     * Sắp xếp dữ liệu trước khi trả về.
     *
     * @param Truck[] $trucks
     * @return Truck[]
     */
    private function sort(array $trucks): array
    {
        foreach ($trucks as $truck) {
            $truck->sortCustomers();
        }

        uasort(
            $trucks,
            fn (Truck $a, Truck $b) => strnatcasecmp(
                $a->plateNumber,
                $b->plateNumber
            )
        );

        return array_values($trucks);
    }
}