<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\Excel\ExcelReaderService;
use App\Services\BangKe\BangKeGenerator;
use App\Services\Export\ExcelExportService;

class BangKeController extends Controller
{
    /**
     * Trang upload.
     */
    public function index()
    {
        return view('index');
    }

    /**
     * Upload file Excel.
     */
    public function upload(Request $request)
{
    $request->validate([
        'excel' => ['required', 'file', 'mimes:xlsx,xls'],
    ]);

    $path = $request->file('excel')->store('excel');

    $fullPath = Storage::disk('local')->path($path);

    $reader = app(ExcelReaderService::class);

    $orders = $reader->read($fullPath);

    session()->forget(['orders', 'excel_path']);

    session([
        'excel_path' => $path,
    ]);

    $tree = $reader->truckTree($orders);

    return view('truck-list', compact('tree'));
}

    /**
     * Sinh bảng kê.
     */
    public function generate(Request $request)
    {
        $selected = $request->input(
            'warehouse',
            []
        );

        if (empty($selected)) {

            return back()->with(
                'error',
                'Vui lòng chọn ít nhất một xe/kho.'
            );

        }

        $path = session('excel_path');

if (!$path) {
    return redirect('/')->with('error', 'Không tìm thấy dữ liệu Excel.');
}

$fullPath = Storage::disk('local')->path($path);

$reader = app(ExcelReaderService::class);

$orders = $reader->read($fullPath);
        

        if (empty($orders)) {

            return redirect('/')
                ->with(
                    'error',
                    'Không tìm thấy dữ liệu Excel.'
                );

        }

        $generator = app(
            BangKeGenerator::class
        );

        $trucks = $generator->generate(
            $orders,
            $selected
        );

        $export = app(
            ExcelExportService::class
        );

        return $export->download(
            $trucks
        );
    }
}