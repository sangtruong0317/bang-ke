<?php

namespace App\Jobs;

use App\Services\ExcelReaderService;
use App\Services\BangKeGenerator;
use App\Services\Export\ExcelExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Str;

final class GenerateBangKeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $filePath,
        private readonly string $userId,
        private readonly ?string $truck = null,
        private readonly ?string $warehouse = null,
    ) {}

    public function handle(
        ExcelReaderService $reader,
        BangKeGenerator $generator,
        ExcelExportService $exporter
    ): void {

        $orders = $reader->readFromPath($this->filePath);

        // FILTER (đưa xuống generator nếu cần nâng cấp)
        if ($this->truck) {
            $orders = $orders->where('truck', $this->truck);
        }

        if ($this->warehouse) {
            $orders = $orders->where('warehouse', $this->warehouse);
        }

        $rows = $generator->generate($orders);

        $spreadsheet = $exporter->export($rows);

        $fileName = 'bangke_' . Str::random(10) . '.xlsx';
        $path = storage_path("app/bangke/{$fileName}");

        if (!is_dir(storage_path('app/bangke'))) {
            mkdir(storage_path('app/bangke'), 0777, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
    }
}