@extends('layouts.app')
@section('title', 'Order '.$order->order_number)
@section('content')
    @php
        $holder = $order->token?->currentOwner;
        $transferred = $holder && (int) $holder->id !== (int) $order->original_student_id;
    @endphp
    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold">{{ $order->outlet->icon }} {{ $order->outlet->name }}</span>
                    <span><x-order-badges :order="$order" /></span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Item</th><th>Price</th><th>Qty</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>{{ $item->item_name }}</td>
                                <td>৳{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td class="text-end">৳{{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr><td colspan="3" class="text-end">Total</td><td class="text-end">৳{{ number_format((float) $order->total_amount, 2) }}</td></tr>
                            <tr><td colspan="3" class="text-end">Paid</td><td class="text-end">৳{{ number_format((float) $order->paid_amount, 2) }}</td></tr>
                            <tr class="fw-bold"><td colspan="3" class="text-end">Still due</td><td class="text-end">৳{{ number_format((float) $order->due_amount, 2) }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @if ($order->payments->isNotEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">Payments</div>
                    <ul class="list-group list-group-flush small">
                        @foreach ($order->payments as $p)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $p->method->label() }}{{ $p->reference ? ' · '.$p->reference : '' }} · {{ $p->paid_at->format('j M, g:i A') }}</span>
                                <span>৳{{ number_format((float) $p->amount, 2) }} ({{ $p->status->label() }})</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body small">
                    <div><strong>Order:</strong> {{ $order->order_number }}</div>
                    <div><strong>Pickup:</strong> {{ $order->pickup_datetime->format('D j M Y, g:i A') }}</div>
                    <div><strong>Ordered by (responsible for payment):</strong> {{ $order->originalStudent->name }} (ID {{ $order->originalStudent->student_id }})</div>
                    <div>
                        <strong>Token holder:</strong> {{ $holder?->name }} (ID {{ $holder?->student_id }})
                        @if ($transferred) <span class="badge text-bg-info">Transferred</span> @endif
                    </div>
                </div>
            </div>
            <a href="{{ url()->previous() === url()->current() ? route('manager.orders') : url()->previous() }}" class="btn btn-light w-100">Back</a>
        </div>
    </div>
@endsection