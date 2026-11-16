@extends('layouts.app')
@section('title', 'Received Transfer Requests')
@section('content')
    @if ($transfers->isEmpty())
        <div class="text-center py-5">
            <div class="fs-1">📥</div>
            <p class="text-muted">Nobody has sent you a transfer request yet.</p>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr><th>From</th><th>Food</th><th>Pickup</th><th>Received</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                    @foreach ($transfers as $transfer)
                        @php $order = $transfer->foodToken->order; @endphp
                        <tr class="{{ $transfer->isPending() ? 'table-warning' : '' }}">
                            <td>{{ $transfer->sender->name }}<div class="small text-muted">ID {{ $transfer->sender->student_id }}</div></td>
                            <td>
                                {{ $order->items->map(fn ($i) => $i->quantity.' × '.$i->item_name)->implode(', ') }}
                                <div class="small text-muted">{{ $order->outlet->name }} · {{ $order->order_number }}</div>
                            </td>
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
                                    <div class="d-flex gap-2 justify-content-end">
                                        <form method="POST" action="{{ route('student.transfers.accept', $transfer->id) }}"
                                              onsubmit="return confirm('Accept this token? You will become its holder and can collect the food. The sender stays responsible for the payment.')">
                                            @csrf
                                            <button class="btn btn-success btn-sm" type="submit">Accept</button>
                                        </form>
                                        <form method="POST" action="{{ route('student.transfers.reject', $transfer->id) }}"
                                              onsubmit="return confirm('Reject this transfer request?')">
                                            @csrf
                                            <button class="btn btn-outline-danger btn-sm" type="submit">Reject</button>
                                        </form>
                                    </div>
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