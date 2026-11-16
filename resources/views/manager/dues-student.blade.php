@extends('layouts.app')
@section('title', 'Dues: '.$student->name)
@section('content')
    <p class="text-muted">Student ID {{ $student->student_id }} · {{ $student->email }}</p>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Order</th><th>Outlet</th><th>Items</th><th>Collected by</th><th>Collected</th><th class="text-end">Due</th><th></th></tr></thead>
                <tbody>
                @forelse ($orders as $o)
                    <tr>
                        <td class="fw-bold">{{ $o->order_number }}</td>
                        <td>{{ $o->outlet->name }}</td>
                        <td>{{ $o->items->map(fn ($i) => $i->quantity.' × '.$i->item_name)->implode(', ') }}</td>
                        <td>{{ $o->token?->currentOwner?->name }}</td>
                        <td>{{ $o->completed_at?->format('j M, g:i A') }}</td>
                        <td class="text-end fw-bold">৳{{ number_format((float) $o->due_amount, 2) }}</td>
                        <td class="text-end"><a href="{{ route('manager.orders.show', $o->id) }}" class="btn btn-light btn-sm">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">This student has no outstanding dues.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <a href="{{ route('manager.dues') }}" class="btn btn-light mt-3">Back to dues</a>
@endsection