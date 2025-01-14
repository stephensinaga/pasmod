<?php

namespace App\Http\Controllers;

use App\Models\MainOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class CashierController extends Controller
{
    public function CashierView(Request $request)
    {
        $search = $request->input('search');
        $category = $request->input('category');

        // Mulai dari query builder
        $productQuery = Product::query();

        // Filter berdasarkan search input
        if ($search) {
            $productQuery->where(function ($query) use ($search) {
                $query->where('product_name', 'like', '%' . $search . '%')
                    ->orWhere('product_code', 'like', '%' . $search . '%');
            });
        }

        // Filter berdasarkan kategori
        if ($category) {
            $productQuery->where('product_category', $category);
        }

        // Ambil data produk dengan pagination
        $product = $productQuery->get(); // Batasi 12 produk per halaman
        $categories = Category::all();

        // Data lainnya
        $order = Order::whereNull('main_id')->get();
        $invoice = MainOrder::latest()->first();

        return view('Cashier.Cashier', compact('product', 'order', 'invoice', 'category', 'categories'));
    }

    public function Order($id)
    {
        $product = Product::where('id', $id)->first();

        if ($product) {
            $checkItem = Order::where('product_id', $product->id)
                ->whereNull('main_id')
                ->first();

            if ($checkItem) {
                $checkItem->qty += 1;
                $checkItem->save();
            } else {
                $order = new Order();
                $order->product_id = $product->id;
                $order->product_name = $product->product_name;
                $order->product_code = $product->product_code;
                $order->product_category = $product->product_category;
                $order->product_price = $product->product_price;
                $order->qty = 1;

                $order->save();
            }
        }

        return back();
    }
    
    public function updateOrderItem(Request $request, $id)
    {
        $product = Order::where('id', $id)->first();

        if ($product) {
            // Mengambil kuantitas baru dari request
            $newQty = (int) $request->input('qty');

            // Menghapus item jika kuantitasnya 0 atau kurang
            if ($newQty <= 0) {
                $product->delete();
                return response()->json(['success' => true, 'message' => 'Item berhasil dihapus']);
            } else {
                // Update kuantitas jika lebih dari 0
                $product->qty = $newQty;
                $product->save();
                return response()->json(['success' => true, 'message' => 'Kuantitas berhasil diperbarui']);
            }
        }

        return response()->json(['success' => false, 'message' => 'Item tidak ditemukan'], 404);
    }

    public function CheckOut(Request $request)
    {
        $request->validate([
            'payment_type' => 'required',
            'cash' => 'nullable|numeric|min:0',
            'transfer_proof' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $orders = Order::whereNull('main_id')->get();
        $grandtotal = $orders->sum(function ($order) {
            return $order->qty * $order->product_price;
        });

        $cashGiven = $request->cash ?? 0;
        $changes = $cashGiven - $grandtotal;

        $transferImage = null;
        if ($request->hasFile('transfer_proof')) {
            $transfer = $request->file('transfer_proof');
            $transferImageName = time() . '_' . $transfer->getClientOriginalName();
            $transfer->move(public_path('bukti_transfer'), $transferImageName);
            $transferImage = 'bukti_transfer/' . $transferImageName;
        }

        $cashier = Auth::user();

        // Generate invoice number
        $today = now()->format('Y-m-d');
        // Cari pesanan terakhir pada hari ini
        $lastOrder = MainOrder::whereDate('created_at', $today)->orderBy('id', 'desc')->first();
        $newInvoiceNumber = $lastOrder ? ($lastOrder->no_invoice + 1) : 1;

        $Checkout = new MainOrder();
        $Checkout->no_meja = $request->no_meja;
        $Checkout->no_invoice = $newInvoiceNumber;
        $Checkout->cashier = $cashier->name;
        $Checkout->grandtotal = $grandtotal;
        $Checkout->payment = $request->payment_type;
        $Checkout->cash = $cashGiven;
        $Checkout->changes = max($changes, 0);
        $Checkout->transfer_image = $transferImage;
        $Checkout->status = 'checkout';
        $Checkout->save();

        $mainOrderId = $Checkout->id;

        foreach ($orders as $order) {
            $order->main_id = $mainOrderId;
            $order->no_meja = $request->no_meja;
            $order->save();
        }

        $invoice = MainOrder::where('id', $Checkout->id)->first();

        return response()->json([
            'message' => 'Checkout berhasil',
            'invoice' => $invoice,
        ], 200);
    }

    public function SavePendingOrder(Request $request, $id)
    {
        $order = MainOrder::findOrFail($id);
        $cashier = Auth::user();

        if (!$cashier) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Inisialisasi variabel untuk tipe pembayaran, uang yang diberikan, dan kembalian
        $type = $request->payment_type;
        $cashGiven = null;
        $changes = null;

        // Cek metode pembayaran
        if ($type === 'transfer') {
            // Jika metode pembayaran transfer, cashGiven dan changes tetap null
        } else {
            // Jika metode pembayaran cash, hitung perubahan
            $cashGiven = $request->cash;

            // Validasi jumlah uang yang diberikan lebih besar dari atau sama dengan grandtotal
            if ($cashGiven < $order->grandtotal) {
                return response()->json(['error' => 'Cash given is less than total amount'], 400);
            }

            // Hitung perubahan
            $changes = $cashGiven - $order->grandtotal;
        }

        // Update data order berdasarkan input
        $order->cashier = $cashier->name; // Simpan nama kasir yang menangani
        $order->payment = $type;          // Simpan tipe pembayaran
        $order->cash = $cashGiven;         // Simpan uang yang diberikan (jika cash)
        $order->changes = $changes;        // Simpan perubahan (jika cash)

        // Proses upload gambar bukti transfer jika ada
        if ($request->hasFile('img')) {
            $transfer = $request->file('img'); // Ambil file gambar
            $transferImageName = time() . '_' . $transfer->getClientOriginalName(); // Generate nama file unik

            // Simpan gambar langsung ke public/bukti_transfer
            $transfer->move(public_path('bukti_transfer'), $transferImageName);

            // Simpan path gambar transfer di database, tanpa "public/" di depannya
            $order->transfer_image = 'bukti_transfer/' . $transferImageName;
        }

        // Update status menjadi 'checkout'
        $order->status = 'checkout';
        $order->save(); // Simpan perubahan ke database

        // Kembalikan respons JSON sukses dengan data invoice/order
        return response()->json([
            'message' => 'Order processed successfully',
            'invoice' => $order, // Kirim seluruh data order sebagai respons
        ], 200);
    }

    public function showInvoice($id)
    {
        // Temukan main order berdasarkan id dan muat relasi orders
        $mainOrder = MainOrder::with('orders')->find($id);

        if (!$mainOrder) {
            abort(404, 'Invoice not found.');
        }

        // Kirim data ke view untuk di-render
        return view('Cashier.invoice', compact('mainOrder'));
    }

}
