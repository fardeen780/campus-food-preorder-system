@extends('layouts.app')
@section('title', 'Categories')
@section('content')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h3 class="h6">Add a category</h3>
            <form method="POST" action="{{ route('manager.categories.store') }}" class="row g-2">
                @csrf
                <div class="col-12 col-md-6">
                    <input name="name" value="{{ old('name') }}" maxlength="100" required class="form-control" placeholder="Name" aria-label="Name">
                </div>
                <div class="col-6 col-md-3">
                    <input name="sort_order" type="number" min="0" max="999" value="{{ old('sort_order', 0) }}" class="form-control" aria-label="Sort order">
                </div>
                <div class="col-6 col-md-3"><button class="btn btn-brand w-100" type="submit">Add</button></div>
            </form>
        </div>
    </div>

    <div class="list-group shadow-sm">
        @foreach ($categories as $c)
            <form method="POST" action="{{ route('manager.categories.update', $c->id) }}" class="list-group-item">
                @csrf @method('PUT')
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-5">
                        <input name="name" value="{{ $c->name }}" maxlength="100" required class="form-control" aria-label="Name">
                    </div>
                    <div class="col-4 col-md-2">
                        <input name="sort_order" type="number" min="0" max="999" value="{{ $c->sort_order }}" class="form-control" aria-label="Sort order">
                    </div>
                    <div class="col-8 col-md-3 d-flex align-items-center gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="cat{{ $c->id }}" @checked($c->is_active)>
                            <label class="form-check-label" for="cat{{ $c->id }}">Active</label>
                        </div>
                        <span class="small text-muted">{{ $c->food_items_count }} items</span>
                    </div>
                    <div class="col-12 col-md-2"><button class="btn btn-light w-100" type="submit">Save</button></div>
                </div>
            </form>
        @endforeach
    </div>
    <p class="small text-muted mt-2">Deactivating a category hides its items from the student menu.</p>
@endsection