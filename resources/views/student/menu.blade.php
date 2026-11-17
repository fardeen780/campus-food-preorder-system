@extends('layouts.app')
@section('title', 'Food Menu')
@section('content')
    <div class="section-head">
        <div>
            <h1 class="h3 mb-1">Food menu</h1>
            <p class="text-muted mb-0">Add food to your cart, then choose a pickup time at checkout.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('student.menu') }}" class="row g-2 mb-3" role="search">
        @if (request('category'))
            <input type="hidden" name="category" value="{{ (int) request('category') }}">
        @endif
        <div class="col-12 col-md-6">
            <input type="search" name="q" value="{{ request('q') }}" maxlength="50"
                   class="form-control" placeholder="Search for a dish" aria-label="Search for a dish">
        </div>
        <div class="col-8 col-md-4">
            <select name="outlet" class="form-select" aria-label="Outlet">
                <option value="">All outlets</option>
                @foreach ($outlets as $outlet)
                    <option value="{{ $outlet->id }}" @selected((int) request('outlet') === $outlet->id)>{{ $outlet->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-4 col-md-2 d-flex gap-2">
            <button class="btn btn-brand flex-fill" type="submit">Search</button>
        </div>
    </form>

    <div class="chips">
        <a class="chip {{ request('category') ? '' : 'active' }}"
           href="{{ route('student.menu', array_filter(['q' => request('q'), 'outlet' => request('outlet')])) }}">All food</a>
        @foreach ($categories as $category)
            <a class="chip {{ (int) request('category') === $category->id ? 'active' : '' }}"
               href="{{ route('student.menu', array_filter(['q' => request('q'), 'outlet' => request('outlet'), 'category' => $category->id])) }}">{{ $category->name }}</a>
        @endforeach
        @if (request()->hasAny(['q', 'outlet', 'category']))
            <a class="chip" href="{{ route('student.menu') }}">Clear filters</a>
        @endif
    </div>

    @if ($items->isEmpty())
        <div class="empty-state">Nothing matches your search. Try a different word or clear the filters.</div>
    @else
        <div class="row g-3">
            @foreach ($items as $item)
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <x-food-card :item="$item" />
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-4">{{ $items->links('pagination::bootstrap-5') }}</div>
@endsection