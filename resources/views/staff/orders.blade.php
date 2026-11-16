@extends('layouts.app')
@section('title', 'Order Queue')
@section('content')
    @if (! auth()->user()->outlet_id)
        <div class="alert alert-warning">An administrator must assign you to an outlet before you can see orders.</div>
    @else
        <ul class="nav nav-pills mb-3 gap-1 flex-wrap">
            @foreach (['active' => 'Active', 'today' => 'Pickup today', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'all' => 'All'] as $key => $label)
                <li class="nav-item">
                    <a class="nav-link {{ $filter === $key ? 'active' : '' }}" href="{{ route('staff.orders', ['status' => $key]) }}">{{ $label }}</a>
                </li>
            @endforeach
        </ul>

        @if ($orders->isEmpty())
            <div class="text-center py-5 text-muted">No orders here.</div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr><th>Order</th><th>Items</th><th>Pickup</th><th>Token holder</th><th>Status</th><th class="text-end">Action</th></tr>
                        </thead>
                        <tbody>
                        @foreach ($orders as $order)
                            @php
                                $next = [
                                    'pending' => ['confirmed', 'Confirm'],
                                    'confirmed' => ['preparing', 'Start preparing'],
                                    'preparing' => ['ready', 'Mark ready'],
                                ][$order->order_status->value] ?? null;
                                $holder = $order->token?->currentOwner;
                            @endphp
                            <tr>
                                <td class="fw-bold">{{ $order->order_number }}</td>
                                <td>{{ $order->items->map(fn ($i) => $i->quantity.' × '.$i->item_name)->implode(', ') }}</td>
                                <td>{{ $order->pickup_datetime->format('D j M, g:i A') }}</td>
                                <td>{{ $holder?->name }}<div class="small text-muted">ID {{ $holder?->student_id }}</div></td>
                                <td><x-order-badges :order="$order" /></td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        @if ($next)
                                            <form method="POST" action="{{ route('staff.orders.advance', $order->id) }}">
                                                @csrf
                                                <input type="hidden" name="to" value="{{ $next[0] }}">
                                                <button class="btn btn-brand btn-sm" type="submit">{{ $next[1] }}</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('staff.orders.show', $order->id) }}" class="btn btn-light btn-sm">
                                            {{ $order->order_status->value === 'ready' ? 'Collect' : 'Open' }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
        @endif
    @endif
@endsection