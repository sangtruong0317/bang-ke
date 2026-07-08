<?php

namespace App\Services\Excel;

use App\DTO\Order;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelReaderService
{
    /**
     * Đọc file Excel.
     *
     * @return Order[]
     */
    public function read(string $path): array
{
    $spreadsheet = IOFactory::load($path);

    $sheet = $spreadsheet->getSheetByName('data');

    if (! $sheet instanceof Worksheet) {
        throw new \RuntimeException('Không tìm thấy sheet "data".');
    }

    $headers = $this->findHeaders($sheet);

    $highestRow = $sheet->getHighestRow();

    $orders = [];

    for ($row = $headers['_row'] + 1; $row <= $highestRow; $row++) {

        $order = $this->readRow(
            $sheet,
            $headers,
            $row
        );

        if ($order !== null) {
            $orders[] = $order;
        }
    }

    return $orders;
}

    /**
     * Tìm dòng Header và map tên cột.
     */
    private function findHeaders(
        Worksheet $sheet
    ): array {

        $highestRow = min(
            20,
            $sheet->getHighestRow()
        );

        $highestColumn = $sheet->getHighestColumn();

        for ($row = 1; $row <= $highestRow; $row++) {

            $map = [];

            foreach (range('A', $highestColumn) as $column) {

                $text = trim((string)$sheet
                    ->getCell($column . $row)
                    ->getFormattedValue());

                if ($text === '') {
                    continue;
                }

                $map[$text] = $column;
            }

            if (
                isset($map['SỐ XE']) &&
                isset($map['KHO']) &&
                isset($map['TÊN KH']) &&
                isset($map['TÊN SP']) &&
                isset($map['SỐ LƯỢNG']) &&
                isset($map['QUY CÁCH']) &&
                isset($map['Tình trạng'])
            ) {

                $map['_row'] = $row;

                return $map;
            }
        }

        throw new \RuntimeException(
            'Không tìm thấy dòng tiêu đề.'
        );
    }
        /**
     * Đọc một dòng dữ liệu.
     */
    private function readRow(
    Worksheet $sheet,
    array $headers,
    int $row
): ?Order {

    $truck = $this->text(
        $sheet->getCell($headers['SỐ XE'] . $row)->getCalculatedValue()
    );

    $warehouse = $this->text(
        $sheet->getCell($headers['KHO'] . $row)->getCalculatedValue()
    );

    $status = $this->text(
        $sheet->getCell($headers['Tình trạng'] . $row)->getCalculatedValue()
    );

    $customer = $this->text(
        $sheet->getCell($headers['TÊN KH'] . $row)->getCalculatedValue()
    );

    $product = $this->text(
        $sheet->getCell($headers['TÊN SP'] . $row)->getCalculatedValue()
    );

    $quantity = (int) str_replace(
        ',',
        '',
        $sheet->getCell($headers['SỐ LƯỢNG'] . $row)->getCalculatedValue()
    );

    $packageSize = $this->parsePackageSize(
        $sheet->getCell($headers['QUY CÁCH'] . $row)->getCalculatedValue()
    );

    if (mb_strtolower($status) !== 'lấy') {
        return null;
    }

    if (
        $truck === '' ||
        $customer === '' ||
        $product === '' ||
        $quantity <= 0
    ) {
        return null;
    }

    return new Order(
        truck: $truck,
        warehouse: $warehouse,
        status: $status,
        customer: $customer,
        product: $product,
        quantity: $quantity,
        packageSize: $packageSize,
    );
}

    /**
     * Chuẩn hóa chuỗi.
     */
    private function text(mixed $value): string
    {
        $value = trim((string) $value);

        return preg_replace('/\s+/u', ' ', $value);
    }
        /**
     * Lấy quy cách.
     *
     * Ví dụ:
     *
     * 20KG      -> 20
     * 1X500G    -> 1
     * 500g(10)  -> 10
     * Gói       -> 0
     */
    private function parsePackageSize(mixed $value): int
{
    $value = trim((string) $value);

    if (is_numeric($value)) {
        return (int) $value;
    }

    if (preg_match('/\d+/', $value, $match)) {
        return (int) $match[0];
    }

    return 0;
}

    /**
     * Danh sách xe và kho.
     *
     * @param Order[] $orders
     */
    public function truckTree(array $orders): array
    {
        $tree = [];

        foreach ($orders as $order) {

            if (! isset($tree[$order->truck])) {
                $tree[$order->truck] = [];
            }

            $tree[$order->truck][$order->warehouse] = $order->warehouse;
        }

        ksort($tree, SORT_NATURAL);

        foreach ($tree as &$warehouses) {

            natcasesort($warehouses);

            $warehouses = array_values($warehouses);
        }

        return $tree;
    }
}