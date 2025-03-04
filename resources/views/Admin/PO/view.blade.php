@extends('layouts.app')

@section('contents')
<div class="container">
    <h2 class="mb-4">Pre Order List</h2>

    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Contact</th>
                <th>Note</th>
                <th>Total Price</th>
                <th>Payment</th>
                <th>Cash</th>
                <th>Transfer Proof</th>
                <th>Progress</th>
                <th>Order Items</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $preOrder)
            <tr>
                <td>{{ $preOrder->id }}</td>
                <td>{{ $preOrder->customer }}</td>
                <td>{{ $preOrder->customer_contact }}</td>
                <td>{{ $preOrder->keterangan ?? '-' }}</td>
                <td>Rp{{ number_format($preOrder->total_price, 2, ',', '.') }}</td>
                <td>{{ ucfirst($preOrder->payment) ?? '-' }}</td>
                <td>Rp{{ number_format($preOrder->cash ?? 0, 2, ',', '.') }}</td>
                <td>
                    @if($preOrder->transfer_img)
                        <a href="{{ asset($preOrder->transfer_img) }}" target="_blank">View</a>
                    @else
                        -
                    @endif
                </td>
                <td>
                    <span class="badge 
                        @if($preOrder->progress == 'pending') badge-warning 
                        @elseif($preOrder->progress == 'inProgress') badge-info 
                        @else badge-success 
                        @endif">
                        {{ ucfirst($preOrder->progress) }}
                    </span>
                </td>
                <td>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#itemsModal{{ $preOrder->id }}">
                        View Items
                    </button>
                </td>
                <td>{{ $preOrder->created_at->format('d M Y H:i') }}</td>
            </tr>

            <!-- Modal untuk Order Items -->
            <div class="modal fade" id="itemsModal{{ $preOrder->id }}" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Order Items (PO #{{ $preOrder->id }})</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Unit</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Grand Total</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $preOrder)
                                        <tr style="background: #f8f9fa; font-weight: bold;">
                                            <td colspan="6">PO #{{ $preOrder->id }} - {{ $preOrder->customer }} ({{ $preOrder->customer_contact }})</td>
                                        </tr>
                                        @foreach($preOrder->poItems as $item)
                                        <tr>
                                            <td>{{ $item->product }}</td>
                                            <td>{{ $item->unit }}</td>
                                            <td>{{ $item->qty }}</td>
                                            <td>Rp{{ number_format($item->price, 2, ',', '.') }}</td>
                                            <td>Rp{{ number_format($item->grandtotal, 2, ',', '.') }}</td>
                                            <td>{{ $item->keterangan ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>                                
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            @endforeach
        </tbody>
    </table>
</div>
@endsection
