@extends('layouts.app')
@section('title', 'My Orders')
@section('content')
    @if ($orders->isEmpty())
        <div class="text-center py-5">
            <div class="fs-1">🧾</div>
            <p class="text-muted">You have not placed any orders yet.</p>
            <a href="{{ route('student.menu') }}" class="btn btn-brand">Browse the menu</a>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order</th><th>Outlet</th><th>Pickup</th><th>Total</th>
                            <th>Status</th><th>Token</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td class="fw-bold">{{ $order->order_number }}</td>
                            <td>{{ $order->outlet->name }}</td>
                            <td>{{ $order->pickup_datetime->format('D j M, g:i A') }}</td>
                            <td>৳{{ number_format((float) $order->total_amount, 2) }}</td>
                            <td><x-order-badges :order="$order" /></td>
                            <td><code>{{ $order->token?->token_code }}</code></td>
                            <td class="text-end">
                                <a href="{{ route('student.orders.show', $order->id) }}" class="btn btn-light btn-sm">View</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
    @endif
@endsection