@extends('layouts.app')
@section('title', 'All Orders')
@section('content')
    <form method="GET" action="{{ route('manager.orders') }}" class="row g-2 mb-3">
        <div class="col-12 col-md-3">
            <input type="search" name="q" value="{{ request('q') }}" maxlength="20" class="form-control" placeholder="Order number" aria-label="Order number">
        </div>
        <div class="col-6 col-md-2">
            <select name="status" class="form-select" aria-label="Order status">
                <option value="">Any status</option>
                @foreach (\App\Enums\OrderStatus::cases() as $s)
                    <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="payment" class="form-select" aria-label="Payment status">
                <option value="">Any payment</option>
                @foreach (\App\Enums\PaymentStatus::cases() as $s)
                    <option value="{{ $s->value }}" @selected(request('payment') === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select name="outlet" class="form-select" aria-label="Outlet">
                <option value="">All outlets</option>
                @foreach ($outlets as $o)
                    <option value="{{ $o->id }}" @selected((int) request('outlet') === $o->id)>{{ $o->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2"><button class="btn btn-light w-100" type="submit">Filter</button></div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Order</th><th>Outlet</th><th>Ordered by</th><th>Token holder</th><th>Pickup</th><th>Total</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="fw-bold">{{ $order->order_number }}</td>
                        <td>{{ $order->outlet->name }}</td>
                        <td>{{ $order->originalStudent->name }}</td>
                        <td>{{ $order->token?->currentOwner?->name }}</td>
                        <td>{{ $order->pickup_datetime->format('j M, g:i A') }}</td>
                        <td>৳{{ number_format((float) $order->total_amount, 2) }}</td>
                        <td><x-order-badges :order="$order" /></td>
                        <td class="text-end"><a href="{{ route('manager.orders.show', $order->id) }}" class="btn btn-light btn-sm">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No orders found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
@endsection