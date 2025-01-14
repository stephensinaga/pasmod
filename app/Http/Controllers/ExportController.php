<?php

namespace App\Http\Controllers;

use App\Models\MainOrder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExportController extends Controller
{
    public function ExportLaporanPenjualanHarian()
    {
        // Buat Spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Tanggal hari ini
        $today = Carbon::now()->format('d M Y');

        // Tambahkan header dengan tanggal hari ini
        $sheet->setCellValue('A1', 'Laporan Penjualan Harian - ' . $today);
        $sheet->mergeCells('A1:I1'); // Gabungkan sel untuk header
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header kolom di Excel (mulai dari baris ke-2 karena header laporan di baris pertama)
        $sheet->setCellValue('A2', 'No');
        $sheet->setCellValue('B2', 'ID');
        $sheet->setCellValue('C2', 'Cashier');
        $sheet->setCellValue('D2', 'Grand Total');
        $sheet->setCellValue('E2', 'Payment Method');
        $sheet->setCellValue('F2', 'Cash');
        $sheet->setCellValue('G2', 'Changes');
        $sheet->setCellValue('H2', 'Status');
        $sheet->setCellValue('I2', 'Order Date');
        // Ambil data penjualan harian
        $orders = MainOrder::whereDate('created_at', today())->get();

        // Isi data ke dalam Excel
        $row = 3; // Mulai dari baris ketiga setelah header
        $no = 1;  // Nomor urut
        foreach ($orders as $order) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $order->id);
            $sheet->setCellValue('C' . $row, $order->cashier);
            $sheet->setCellValue('D' . $row, 'Rp ' . number_format($order->grandtotal, 0, ',', '.'));
            $sheet->setCellValue('E' . $row, ucfirst($order->payment));
            $sheet->setCellValue('F' . $row, 'Rp ' . number_format($order->cash, 0, ',', '.'));
            $sheet->setCellValue('G' . $row, 'Rp ' . number_format($order->changes, 0, ',', '.'));
            $sheet->setCellValue('H' . $row, ucfirst($order->status));
            $sheet->setCellValue('I' . $row, $order->created_at->format('d M Y'));

            // Styling untuk setiap baris data
            $sheet->getStyle('A' . $row . ':I' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . $row . ':I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
            $no++; // Increment nomor urut
        }

        // Auto-size kolom
        foreach (range('A', 'I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Simpan Excel dengan nama file yang mengandung tanggal hari ini
        $fileName = 'Laporan_Penjualan_Harian_' . Carbon::now()->format('d_m_Y') . '.xlsx';
        $filePath = storage_path($fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Kirim file ke browser untuk diunduh
        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    public function ExportLaporanPenjualan(Request $request)
    {
        // Buat Spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Tanggal hari ini
        $today = Carbon::now()->format('d M Y');

        // Judul laporan
        $title = 'Laporan Penjualan - ' . $today;

        // Tambahkan header dengan tanggal hari ini
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:J1'); // Gabungkan sel untuk header
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header kolom di Excel
        $sheet->setCellValue('A2', 'No');
        $sheet->setCellValue('B2', 'ID');
        $sheet->setCellValue('C2', 'Cashier');
        $sheet->setCellValue('D2', 'Grand Total');
        $sheet->setCellValue('E2', 'Payment Method');
        $sheet->setCellValue('F2', 'Cash');
        $sheet->setCellValue('G2', 'Changes');
        $sheet->setCellValue('H2', 'Status');
        $sheet->setCellValue('I2', 'Order Date');
        $sheet->setCellValue('J2', 'Items');

        // Mulai query untuk MainOrder, dengan filter
        $mainOrdersQuery = MainOrder::query();
        if ($request->has('payment_method')) {
            $mainOrdersQuery->where('payment', $request->payment_method);
        }
        if ($request->has('cashier')) {
            $mainOrdersQuery->where('cashier', $request->cashier);
        }
        if ($request->has('date')) {
            $mainOrdersQuery->whereDate('created_at', $request->date);
        }
        if ($request->has('start_date') && $request->has('end_date')) {
            $start_date = $request->start_date;
            $end_date = Carbon::parse($request->end_date)->endOfDay(); // Tambahkan waktu akhir hari
            $mainOrdersQuery->whereBetween('created_at', [$start_date, $end_date]);
        }

        // Ambil data penjualan sesuai filter
        $mainOrders = $mainOrdersQuery->with('orders')->get();

        // Inisialisasi variabel untuk menghitung total
        $totalGrandTotal = 0;

        // Isi data ke dalam Excel
        $row = 3; // Mulai dari baris ketiga setelah header
        $no = 1;  // Nomor urut
        foreach ($mainOrders as $mainOrder) {
            // Gabungkan nama barang dan kuantitas
            $items = $mainOrder->orders->map(function ($order) {
                return $order->product_name . ' * ' . $order->qty;
            })->implode(', ');

            // Isi data
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $mainOrder->id);
            $sheet->setCellValue('C' . $row, $mainOrder->cashier);
            $sheet->setCellValue('D' . $row, 'Rp ' . number_format($mainOrder->grandtotal, 0, ',', '.'));
            $sheet->setCellValue('E' . $row, ucfirst($mainOrder->payment));
            $sheet->setCellValue('F' . $row, 'Rp ' . number_format($mainOrder->cash, 0, ',', '.'));
            $sheet->setCellValue('G' . $row, 'Rp ' . number_format($mainOrder->changes, 0, ',', '.'));
            $sheet->setCellValue('H' . $row, ucfirst($mainOrder->status));
            $sheet->setCellValue('I' . $row, $mainOrder->created_at->format('d M Y'));
            $sheet->setCellValue('J' . $row, $items);

            // Tambahkan total grandtotal
            $totalGrandTotal += $mainOrder->grandtotal;

            // Styling untuk setiap baris data
            $sheet->getStyle('A' . $row . ':I' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . $row . ':I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Hanya styling border pada kolom J, tanpa pengaturan center
            $sheet->getStyle('J' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $row++;
            $no++; // Increment nomor urut
        }

        // Auto-size kolom
        foreach (range('A', 'J') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Tambahkan total grand total
        $sheet->setCellValue('I' . $row, 'Total Penjualan:');
        $sheet->setCellValue('J' . $row, 'Rp ' . number_format($totalGrandTotal, 0, ',', '.'));

        // Simpan Excel dengan nama file yang mengandung tanggal hari ini
        $fileName = 'Laporan_Penjualan_' . Carbon::now()->format('d_m_Y') . '.xlsx';
        $filePath = storage_path($fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Kirim file ke browser untuk diunduh
        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }
}
