@extends('layouts.app')
@section('title', 'Food Items')
@section('content')
    <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
        <form method="GET" action="{{ route('manager.foods') }}" class="row g-2 flex-grow-1">
            <div class="col-12 col-md-4">
                <input type="search" name="q" value="{{ request('q') }}" maxlength="50" class="form-control" placeholder="Search..." aria-label="Search">
            </div>
            <div class="col-6 col-md-3">
                <select name="outlet" class="form-select" aria-label="Outlet">
                    <option value="">All outlets</option>
                    @foreach ($outlets as $o)
                        <option value="{{ $o->id }}" @selected((int) request('outlet') === $o->id)>{{ $o->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select" aria-label="Status">
                    @foreach (['all' => 'All', 'available' => 'Available', 'unavailable' => 'Unavailable', 'removed' => 'Removed'] as $k => $label)
                        <option value="{{ $k }}" @selected($status === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2"><button class="btn btn-light w-100" type="submit">Filter</button></div>
        </form>
        <a href="{{ route('manager.foods.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Add item</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Item</th><th>Outlet</th><th>Category</th><th>Price</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $item->icon }} {{ $item->name }}</td>
                        <td>{{ $item->outlet->name }}</td>
                        <td>{{ $item->category->name }}</td>
                        <td>৳{{ number_format((float) $item->price, 2) }}</td>
                        <td>
                            @if ($item->trashed())
                                <span class="badge text-bg-dark">Removed</span>
                            @elseif ($item->is_available)
                                <span class="badge text-bg-success">Available</span>
                            @else
                                <span class="badge text-bg-secondary">Unavailable</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end flex-wrap">
                                @if ($item->trashed())
                                    <form method="POST" action="{{ route('manager.foods.restore', $item->id) }}">
                                        @csrf
                                        <button class="btn btn-light btn-sm" type="submit">Restore</button>
                                    </form>
                                @else
                                    <a href="{{ route('manager.foods.edit', $item->id) }}" class="btn btn-light btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('manager.foods.toggle', $item->id) }}">
                                        @csrf
                                        <button class="btn btn-light btn-sm" type="submit">{{ $item->is_available ? 'Mark unavailable' : 'Mark available' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('manager.foods.destroy', $item->id) }}"
                                          onsubmit="return confirm('Remove {{ e($item->name) }} from the menu?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm" type="submit" aria-label="Remove"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No items found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $items->links('pagination::bootstrap-5') }}</div>
@endsection