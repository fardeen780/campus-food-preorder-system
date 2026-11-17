@extends('layouts.app')
@section('title', 'My Orders')
@section('content')
    @if ($orders->isEmpty())
        <div class="empty-state py-5">
            <i class="bi bi-receipt fs-1 text-success"></i>
            <p class="mb-3 mt-2">You have not placed any orders yet.</p>
            <a href="{{ route('student.menu') }}" class="btn btn-brand">Browse the menu</a>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order</th><th>Items</th><th>Outlet</th><th>Pickup</th><th>Total</th>
                            <th>Status</th><th>Token</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td class="fw-bold">{{ $order->order_number }}</td>
                            <td>
                                <div class="item-line">
                                    <div class="thumb-stack">
                                        @foreach ($order->items->take(3) as $oi)
                                            <x-food-thumb :item="$oi->foodItem" :name="$oi->item_name" :size="40" />
                                        @endforeach
                                        @if ($order->items->count() > 3)
                                            <span class="thumb-more">+{{ $order->items->count() - 3 }}</span>
                                        @endif
                                    </div>
                                    <span class="small">{{ \Illuminate\Support\Str::limit($order->items->map(fn ($i) => $i->quantity.' × '.$i->item_name)->implode(', '), 40) }}</span>
                                </div>
                            </td>
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