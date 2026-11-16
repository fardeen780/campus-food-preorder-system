@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
    @if ($notifications->isEmpty())
        <div class="text-center py-5">
            <div class="fs-1">🔔</div>
            <p class="text-muted">You have no notifications.</p>
        </div>
    @else
        <div class="d-flex justify-content-end mb-2">
            <form method="POST" action="{{ route('student.notifications.read-all') }}">
                @csrf
                <button class="btn btn-light btn-sm" type="submit">Mark all as read</button>
            </form>
        </div>
        <div class="list-group shadow-sm">
            @foreach ($notifications as $n)
                @php
                    $icon = match ($n->data['kind'] ?? '') {
                        'requested' => 'bi-inbox',
                        'accepted' => 'bi-check-circle',
                        'rejected' => 'bi-x-circle',
                        'cancelled' => 'bi-slash-circle',
                        'expired' => 'bi-hourglass-bottom',
                        'confirmed' => 'bi-check2',
                        'preparing' => 'bi-fire',
                        'ready' => 'bi-bag-check',
                        'completed' => 'bi-check2-all',
                        default => 'bi-bell',
                    };
                @endphp
                <form method="POST" action="{{ route('student.notifications.open', $n->id) }}" class="list-group-item p-0 {{ $n->read_at ? '' : 'list-group-item-warning' }}">
                    @csrf
                    <button type="submit" class="btn text-start w-100 p-3 border-0">
                        <div class="d-flex gap-3">
                            <i class="bi {{ $icon }} fs-4"></i>
                            <div>
                                <div class="{{ $n->read_at ? '' : 'fw-bold' }}">{{ $n->data['title'] ?? 'Notification' }}</div>
                                <div class="small">{{ $n->data['message'] ?? '' }}</div>
                                <div class="small text-muted">{{ $n->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </button>
                </form>
            @endforeach
        </div>
        <div class="mt-3">{{ $notifications->links('pagination::bootstrap-5') }}</div>
    @endif
@endsection