<?php

namespace App\Services\Export;

use App\DTO\Truck;
use App\DTO\Customer;
use App\DTO\Package;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExportService
{
    /**
     * Xuất Excel.
     *
     * @param Truck[] $trucks
     */
    public function export(array $trucks): string
    {
        $spreadsheet = new Spreadsheet();

        $first = true;

        foreach ($trucks as $truck) {

            if ($first) {

                $sheet = $spreadsheet->getActiveSheet();
                $first = false;

            } else {

                $sheet = $spreadsheet->createSheet();

            }

            $title = trim($truck->plateNumber);

            if ($title === '') {
                $title = 'Truck';
            }

            $sheet->setTitle(substr($title, 0, 31));

            $this->writeTruck(
                $sheet,
                $truck
            );
        }

        $path = storage_path(
            'app/public/BangKe.xlsx'
        );

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $writer = new Xlsx($spreadsheet);

        $writer->save($path);

        return $path;
    }

    /**
     * Ghi dữ liệu của 1 xe.
     */
    private function writeTruck(
        Worksheet $sheet,
        Truck $truck
    ): void
    {
        // Header
        $sheet->setCellValue('A1', 'Tên khách hàng');
        $sheet->setCellValue('B1', 'Số bao');
        $sheet->setCellValue('C1', 'Tên sản phẩm');

        $sheet->getStyle('A1:C1')
            ->getFont()
            ->setBold(true);

        $row = 2;

        foreach ($truck->customers() as $customer) {

            $row = $this->writeCustomer(
                $sheet,
                $customer,
                $row
            );

            // cách 1 dòng giữa 2 khách
            $row++;
        }

        foreach (['A', 'B', 'C'] as $column) {

    $sheet
        ->getColumnDimension($column)
        ->setAutoSize(true);

}

$this->formatSheet($sheet);
    }
        /**
     * Ghi dữ liệu của một khách hàng.
     *
     * Trả về dòng tiếp theo cần ghi.
     */
    private function writeCustomer(
        Worksheet $sheet,
        Customer $customer,
        int $row
    ): int {

        $packageNo = 1;
        $lineOfCustomer = 0;

        foreach ($customer->packages() as $package) {

            $row = $this->writePackage(
                $sheet,
                $customer,
                $package,
                $packageNo,
                $lineOfCustomer,
                $row
            );

            $packageNo++;
            $lineOfCustomer++;
        }

        return $row;
    }

    /**
     * Ghi một bao.
     *
     * Trả về dòng tiếp theo.
     */
    private function writePackage(
        Worksheet $sheet,
        Customer $customer,
        Package $package,
        int $packageNo,
        int $lineOfCustomer,
        int $row
    ): int {

        // Cứ mỗi 25 dòng thì ghi lại tên khách
        if ($lineOfCustomer % 25 === 0) {

            $sheet->setCellValue(
                "A{$row}",
                $customer->name
            );

        }

        // STT bao
        $sheet->setCellValue(
            "B{$row}",
            $packageNo
        );

        // Tên sản phẩm + số lượng bao
        $sheet->setCellValue(
            "C{$row}",
            $package->displayName()
        );

        return $row + 1;
    }
        /**
     * Tự động định dạng sheet.
     */
    private function formatSheet(Worksheet $sheet): void
    {
        // Auto Filter
        $sheet->setAutoFilter('A1:C1');

        // Freeze dòng tiêu đề
        $sheet->freezePane('A2');

        // Căn giữa cột STT
        $sheet->getStyle('B:B')
            ->getAlignment()
            ->setHorizontal(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            );

        // Căn giữa theo chiều dọc
        $sheet->getStyle('A:C')
            ->getAlignment()
            ->setVertical(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            );

        // Kẻ viền vùng có dữ liệu
        $lastRow = $sheet->getHighestRow();

        if ($lastRow > 1) {

            $sheet->getStyle("A1:C{$lastRow}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(
                    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                );

        }
    }
        /**
     * Trả về response download.
     *
     * @param Truck[] $trucks
     */
    public function download(array $trucks)
    {
        $path = $this->export($trucks);

        return response()->download(
            $path,
            'BangKe.xlsx'
        )->deleteFileAfterSend(true);
    }
}