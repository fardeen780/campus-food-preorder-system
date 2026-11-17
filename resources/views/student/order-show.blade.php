@extends('layouts.app')
@section('title', 'Order '.$order->order_number)
@section('content')
    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-shop"></i> {{ $order->outlet->name }}</span>
                    <span><x-order-badges :order="$order" /></span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Item</th><th>Price</th><th>Qty</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>
                                    <div class="item-line">
                                        <x-food-thumb :item="$item->foodItem" :name="$item->item_name" :size="56" />
                                        <span class="fw-semibold">{{ $item->item_name }}</span>
                                    </div>
                                </td>
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
        </div>

        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="text-muted small">Food token</div>
                    <div class="display-6 fw-bold" style="letter-spacing:.15em">{{ $order->token?->token_code }}</div>
                    <div class="small text-muted">
                        Currently held by {{ $order->token?->currentOwner?->name }}
                        @if ($order->token && $order->token->current_owner_id !== $order->original_student_id)
                            (you are still responsible for the payment)
                        @endif
                    </div>
                    <hr>
                    <div class="small">
                        <div><strong>Order:</strong> {{ $order->order_number }}</div>
                        <div><strong>Pickup:</strong> {{ $order->pickup_datetime->format('D j M Y, g:i A') }}</div>
                        <div><strong>Cancel until:</strong> {{ $order->cancellation_deadline->format('D j M, g:i A') }}</div>
                    </div>
                </div>
            </div>

            @if ($order->order_status->isActive())
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        @if ($blockReason === null)
                            <form method="POST" action="{{ route('student.orders.cancel', $order->id) }}"
                                  onsubmit="return confirm('Cancel order {{ $order->order_number }}? This cannot be undone.')">
                                @csrf
                                <button class="btn btn-outline-danger w-100" type="submit">Cancel order</button>
                            </form>
                        @else
                            <div class="small text-muted">{{ $blockReason }}</div>
                        @endif
                    </div>
                </div>
            @endif

            <a href="{{ route('student.orders') }}" class="btn btn-light w-100 mt-3">Back to my orders</a>
        </div>
    </div>
@endsection