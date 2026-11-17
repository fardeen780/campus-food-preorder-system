@extends('layouts.app')
@section('title', 'Order '.$order->order_number)
@section('content')
    @php
        $holder = $order->token?->currentOwner;
        $transferred = $holder && (int) $holder->id !== (int) $order->original_student_id;
        $next = [
            'pending' => ['confirmed', 'Confirm order'],
            'confirmed' => ['preparing', 'Start preparing'],
            'preparing' => ['ready', 'Mark as ready'],
        ][$order->order_status->value] ?? null;
        $canPay = $order->order_status->value !== 'cancelled' && (float) $order->due_amount > 0;
    @endphp

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-shop"></i> {{ $order->outlet->name }} · {{ $order->order_number }}</span>
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
                                        <x-food-thumb :item="$item->foodItem" :name="$item->item_name" :size="48" />
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

            @if ($order->payments->isNotEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">Payments on this order</div>
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
                    <div><strong>Pickup:</strong> {{ $order->pickup_datetime->format('D j M Y, g:i A') }}</div>
                    <div><strong>Ordered by:</strong> {{ $order->originalStudent->name }}</div>
                    <div>
                        <strong>Token holder:</strong> {{ $holder?->name }} (ID {{ $holder?->student_id }})
                        @if ($transferred) <span class="badge text-bg-info">Transferred</span> @endif
                    </div>
                </div>
            </div>

            @if ($next)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <form method="POST" action="{{ route('staff.orders.advance', $order->id) }}">
                            @csrf
                            <input type="hidden" name="to" value="{{ $next[0] }}">
                            <button class="btn btn-brand w-100" type="submit">{{ $next[1] }}</button>
                        </form>
                    </div>
                </div>
            @endif

            @if ($order->order_status->value === 'ready')
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h3 class="h6">Collection</h3>
                        <p class="small text-muted">Ask the student for the food token code and type it below.</p>
                        <form method="POST" action="{{ route('staff.orders.collect', $order->id) }}">
                            @csrf
                            <input name="token_code" required maxlength="20" autocomplete="off"
                                   class="form-control mb-2 text-uppercase" placeholder="Token code" aria-label="Token code">
                            <button class="btn btn-success w-100" type="submit">Verify and mark collected</button>
                        </form>
                        @if ((float) $order->due_amount > 0)
                            <div class="small text-muted mt-2">
                                ৳{{ number_format((float) $order->due_amount, 2) }} is still unpaid. If you collect without payment,
                                it is recorded as due on {{ $order->originalStudent->name }}.
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if ($canPay)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h3 class="h6">Record a payment</h3>
                        <form method="POST" action="{{ route('staff.orders.pay', $order->id) }}">
                            @csrf
                            <input name="amount" value="{{ old('amount') }}" required inputmode="decimal"
                                   class="form-control mb-2 @error('amount') is-invalid @enderror" placeholder="Amount (৳)" aria-label="Amount">
                            <select name="method" id="payMethod" class="form-select mb-2" aria-label="Payment method">
                                @foreach (\App\Enums\PaymentMethod::cases() as $m)
                                    <option value="{{ $m->value }}" @selected(old('method') === $m->value)>{{ $m->label() }}</option>
                                @endforeach
                            </select>
                            <input name="reference" id="payRef" value="{{ old('reference') }}" maxlength="100"
                                   class="form-control mb-2" placeholder="Transaction reference (bKash / Nagad)" aria-label="Reference">
                            <button class="btn btn-brand w-100" type="submit">Record payment</button>
                        </form>
                    </div>
                </div>
            @endif

            <a href="{{ route('staff.orders') }}" class="btn btn-light w-100">Back to queue</a>
        </div>
    </div>

    <script>
        const method = document.getElementById('payMethod');
        const ref = document.getElementById('payRef');
        if (method && ref) {
            const sync = () => { ref.disabled = method.value === 'cash'; ref.required = method.value !== 'cash'; };
            method.addEventListener('change', sync);
            sync();
        }
    </script>
@endsection