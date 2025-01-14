@extends('layouts.app')

@section('contents')

        <!-- Filter Form -->
        <div class="filter-section bg-light p-4 mb-4 rounded shadow-sm">
            <h1 class="text-center mb-4">Sales Report</h1>
            <form method="GET" action="{{ route('LaporanPenjualan') }}" class="mb-4">
                <div class="row">
                    <!-- Payment Method Filter -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select class="form-control form-control-sm" id="payment_method" name="payment_method">
                                <option value="">All</option>
                                <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                            </select>
                        </div>
                    </div>

                    <!-- Cashier Filter -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cashier">Cashier</label>
                            <select class="form-control form-control-sm" id="cashier" name="cashier">
                                <option value="">All</option>
                                @foreach ($cashiers as $id => $name)
                                    <option value="{{ $name }}" {{ request('cashier') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!-- Date Filter -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="date" class="form-control" id="date" name="date"
                                value="{{ request('date') }}">
                        </div>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="start_date">Date Range</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                    value="{{ request('start_date') }}">
                                <span class="input-group-text">s/d</span>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                    value="{{ request('end_date') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-sm form-control">Filter</button>
                        </div>
                    </div>

                    <!-- Reset Button -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <a href="{{ route('LaporanPenjualan') }}" class="btn btn-secondary btn-sm form-control">Reset
                                Filter</a>
                        </div>
                    </div>

                    <!-- Download Button -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <a href="{{ route('ExportLaporanPenjualan', request()->all()) }}"
                                class="btn btn-primary btn-sm form-control">Download Report</a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Data Table -->
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cashier</th>
                        <th>Grand Total</th>
                        <th>Payment Method</th>
                        <th>Cash</th>
                        <th>Changes</th>
                        <th>Status</th>
                        <th>Transfer Image</th>
                        <th>Order Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mainOrders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->cashier }}</td>
                            <td>{{ number_format($order->grandtotal, 0, ',', '.') }}</td>
                            <td>{{ ucfirst($order->payment) }}</td>
                            <td>{{ number_format($order->cash, 0, ',', '.') }}</td>
                            <td>{{ number_format($order->changes, 0, ',', '.') }}</td>
                            <td>{{ ucfirst($order->status) }}</td>
                            <td>
                                @if ($order->transfer_image)
                                <a href="{{ asset($order->transfer_image) }}" target="_blank">View Image</a>
                            @else
                                N/A
                            @endif
                            </td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                            <td>
                                <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#detailModal{{ $order->id }}" data-id="{{ $order->id }}">Detail</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Modal -->
        @foreach ($mainOrders as $order)
            <div class="modal fade" id="detailModal{{ $order->id }}" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel{{ $order->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="detailModalLabel{{ $order->id }}">
                                Detail Product for Order #{{ $order->id }}
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Product Code</th>
                                        <th>Category</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($order->orders->isNotEmpty())
                                        @foreach ($order->orders as $detail)
                                            <tr>
                                                <td>{{ $detail->product_name }}</td>
                                                <td>{{ $detail->product_code }}</td>
                                                <td>{{ $detail->product_category }}</td>
                                                <td>{{ $detail->qty }}</td>
                                                <td>{{ number_format($detail->product_price, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5">No orders available</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        </div>

        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
@endsection
