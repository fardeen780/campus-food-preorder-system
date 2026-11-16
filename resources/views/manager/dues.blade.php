@extends('layouts.app')
@section('title', 'Outstanding Dues')
@section('content')
    <div class="row g-3 mb-4">
        <x-stat-card label="Total outstanding" :value="'৳'.number_format((float) $grandTotal, 2)" icon="bi-cash-stack" />
        <x-stat-card label="Students with dues" :value="$students->total()" icon="bi-people" />
    </div>
    <p class="text-muted small">Dues always stay with the student who placed the order, even if the token was transferred.</p>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Student</th><th>Student ID</th><th>Orders with dues</th><th class="text-end">Amount due</th><th></th></tr></thead>
                <tbody>
                @forelse ($students as $s)
                    <tr>
                        <td>{{ $s->name }}</td>
                        <td>{{ $s->student_id }}</td>
                        <td>{{ $s->due_orders }}</td>
                        <td class="text-end fw-bold">৳{{ number_format((float) $s->due_total, 2) }}</td>
                        <td class="text-end"><a href="{{ route('manager.dues.student', $s->id) }}" class="btn btn-light btn-sm">Details</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No outstanding dues.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $students->links('pagination::bootstrap-5') }}</div>
@endsection