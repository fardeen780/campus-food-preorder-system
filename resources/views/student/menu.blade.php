@extends('layouts.app')
@section('title', 'Food Menu')
@section('content')
    <form method="GET" action="{{ route('student.menu') }}" class="row g-2 mb-4">
        <div class="col-12 col-md-4">
            <input type="search" name="q" value="{{ request('q') }}" maxlength="50"
                   class="form-control" placeholder="Search food..." aria-label="Search food">
        </div>
        <div class="col-6 col-md-3">
            <select name="outlet" class="form-select" aria-label="Outlet">
                <option value="">All outlets</option>
                @foreach ($outlets as $outlet)
                    <option value="{{ $outlet->id }}" @selected((int) request('outlet') === $outlet->id)>
                        {{ $outlet->icon }} {{ $outlet->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select name="category" class="form-select" aria-label="Category">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) request('category') === $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button class="btn btn-brand flex-fill" type="submit">Filter</button>
            <a href="{{ route('student.menu') }}" class="btn btn-light">Reset</a>
        </div>
    </form>

    @if ($items->isEmpty())
        <div class="alert alert-info">No food items match your search.</div>
    @endif

    <div class="row g-3">
        @foreach ($items as $item)
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="fs-1">{{ $item->icon ?? '🍽️' }}</div>
                        <h3 class="h6 mt-2 mb-0">{{ $item->name }}</h3>
                        <div class="text-muted small mb-2">{{ $item->outlet->name }} · {{ $item->category->name }}</div>
                        <p class="small text-muted flex-grow-1">{{ $item->description }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>৳{{ number_format((float) $item->price, 2) }}</strong>
                            <form method="POST" action="{{ route('student.cart.add') }}" class="d-flex gap-2">
                                @csrf
                                <input type="hidden" name="food_item_id" value="{{ $item->id }}">
                                <input type="number" name="quantity" value="1" min="1" max="20"
                                       class="form-control form-control-sm" style="width:70px" aria-label="Quantity">
                                <button class="btn btn-brand btn-sm" type="submit"><i class="bi bi-cart-plus"></i> Add</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">{{ $items->links('pagination::bootstrap-5') }}</div>
@endsection