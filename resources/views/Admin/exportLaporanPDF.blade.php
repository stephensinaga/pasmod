<!DOCTYPE html>
<html>

<head>
    <title>Sales Report</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>

<body>
    <h1>Laporan Penjualan</h1>

    @foreach ($mainOrders as $mainOrder)
        <h2>Pesanan Utama #{{ $mainOrder->id }}</h2>
        <p>Kasir: {{ $mainOrder->cashier }}</p>
        <p>Pelanggan: {{ $mainOrder->customer }}</p>
        <p>Total Keseluruhan: {{ $mainOrder->grandtotal }}</p>
        @if ($mainOrder->payment == 'cash')
            <p>Tunai: {{ $mainOrder->cash }}</p>
            <p>Kembalian: {{ $mainOrder->changes }}</p>
        @endif
        <p>Metode Pembayaran: {{ ucfirst($mainOrder->payment) }}</p>

        <table>
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Kode Produk</th>
                    <th>Kategori</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mainOrder->orders as $order)
                    <tr>
                        <td>{{ $order->product_name }}</td>
                        <td>{{ $order->product_code }}</td>
                        <td>{{ $order->product_category }}</td>
                        <td>{{ $order->qty }}</td>
                        <td>{{ $order->product_price }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <hr>
    @endforeach
</body>

</html>
