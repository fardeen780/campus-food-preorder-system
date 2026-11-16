@extends('layouts.app')
@section('title', 'Sent Transfer Requests')
@section('content')
    @if ($transfers->isEmpty())
        <div class="text-center py-5">
            <div class="fs-1">📤</div>
            <p class="text-muted">You have not sent any transfer requests.</p>
            <a href="{{ route('student.tokens') }}" class="btn btn-brand">Go to my food tokens</a>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr><th>Order</th><th>Food</th><th>Sent to</th><th>Pickup</th><th>Requested</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                    @foreach ($transfers as $transfer)
                        @php $order = $transfer->foodToken->order; @endphp
                        <tr>
                            <td class="fw-bold">{{ $order->order_number }}</td>
                            <td>{{ $order->items->map(fn ($i) => $i->quantity.' × '.$i->item_name)->implode(', ') }}</td>
                            <td>{{ $transfer->receiver->name }}<div class="small text-muted">ID {{ $transfer->receiver->student_id }}</div></td>
                            <td>{{ $order->pickup_datetime->format('D j M, g:i A') }}</td>
                            <td>{{ $transfer->requested_at->format('j M, g:i A') }}</td>
                            <td>
                                <x-transfer-badge :status="$transfer->status" />
                                @if ($transfer->isPending())
                                    <div class="small text-muted">expires {{ $transfer->expires_at->format('j M, g:i A') }}</div>
                                @elseif ($transfer->responded_at)
                                    <div class="small text-muted">{{ $transfer->responded_at->format('j M, g:i A') }}</div>
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($transfer->isPending())
                                    <form method="POST" action="{{ route('student.transfers.cancel', $transfer->id) }}"
                                          onsubmit="return confirm('Cancel this transfer request?')">
                                        @csrf
                                        <button class="btn btn-outline-danger btn-sm" type="submit">Cancel request</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $transfers->links('pagination::bootstrap-5') }}</div>
    @endif
@endsection