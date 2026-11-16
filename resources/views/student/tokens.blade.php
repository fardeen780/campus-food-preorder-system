@extends('layouts.app')
@section('title', 'My Food Tokens')
@section('content')
    @if ($tokens->isEmpty())
        <div class="text-center py-5">
            <div class="fs-1">🎟️</div>
            <p class="text-muted">You do not hold any food tokens yet.</p>
            <a href="{{ route('student.menu') }}" class="btn btn-brand">Order some food</a>
        </div>
    @else
        <div class="row g-3">
            @foreach ($tokens as $token)
                @php
                    $order = $token->order;
                    $reason = $reasons->get($token->id);
                    $pendingTransfer = $pending->get($token->id);
                    $fromOther = (int) $order->original_student_id !== (int) $token->current_owner_id;
                @endphp
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="fw-bold">{{ $order->outlet->icon }} {{ $order->outlet->name }}</div>
                                    <div class="small text-muted">{{ $order->order_number }}</div>
                                </div>
                                <div><x-order-badges :order="$order" :payment="! $fromOther" /></div>
                            </div>

                            <div class="display-6 fw-bold" style="letter-spacing:.15em">{{ $token->token_code }}</div>

                            <div class="small mt-2">
                                <div><strong>Food:</strong> {{ $order->items->map(fn ($i) => $i->quantity.' × '.$i->item_name)->implode(', ') }}</div>
                                <div><strong>Pickup:</strong> {{ $order->pickup_datetime->format('D j M, g:i A') }}</div>
                                @if ($fromOther)
                                    <div class="text-muted mt-1">
                                        Ordered by {{ $order->originalStudent->name }}, who remains responsible for the payment.
                                    </div>
                                @endif
                            </div>

                            @if ($pendingTransfer)
                                <div class="alert alert-warning py-2 small mt-3 mb-0">
                                    Waiting for {{ $pendingTransfer->receiver->name }} to respond
                                    (expires {{ $pendingTransfer->expires_at->format('D j M, g:i A') }}).
                                </div>
                            @elseif ($reason === null)
                                <a href="{{ route('student.tokens.transfer', $token->id) }}" class="btn btn-brand btn-sm mt-3">
                                    <i class="bi bi-send"></i> Transfer token
                                </a>
                            @else
                                <div class="small text-muted mt-3">{{ $reason }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-3">{{ $tokens->links('pagination::bootstrap-5') }}</div>
    @endif
@endsection