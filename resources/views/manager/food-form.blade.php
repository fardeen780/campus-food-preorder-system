@extends('layouts.app')
@section('title', $item->exists ? 'Edit Food Item' : 'Add Food Item')
@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ $item->exists ? route('manager.foods.update', $item->id) : route('manager.foods.store') }}">
                        @csrf
                        @if ($item->exists) @method('PUT') @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="outlet_id">Outlet</label>
                                <select id="outlet_id" name="outlet_id" class="form-select @error('outlet_id') is-invalid @enderror" required>
                                    <option value="">Choose...</option>
                                    @foreach ($outlets as $o)
                                        <option value="{{ $o->id }}" @selected((int) old('outlet_id', $item->outlet_id) === $o->id)>{{ $o->name }}{{ $o->is_active ? '' : ' (inactive)' }}</option>
                                    @endforeach
                                </select>
                                @error('outlet_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="category_id">Category</label>
                                <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">Choose...</option>
                                    @foreach ($categories as $c)
                                        <option value="{{ $c->id }}" @selected((int) old('category_id', $item->category_id) === $c->id)>{{ $c->name }}{{ $c->is_active ? '' : ' (inactive)' }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="name">Name</label>
                                <input id="name" name="name" value="{{ old('name', $item->name) }}" maxlength="150" required
                                       class="form-control @error('name') is-invalid @enderror">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="icon">Emoji icon</label>
                                <input id="icon" name="icon" value="{{ old('icon', $item->icon) }}" maxlength="16" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="price">Price (৳)</label>
                                <input id="price" name="price" value="{{ old('price', $item->price) }}" inputmode="decimal" required
                                       class="form-control @error('price') is-invalid @enderror">
                                @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="description">Description</label>
                                <textarea id="description" name="description" rows="2" maxlength="500"
                                          class="form-control">{{ old('description', $item->description) }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_available" id="is_available" value="1"
                                           @checked(old() ? old('is_available') : ($item->exists ? $item->is_available : true))>
                                    <label class="form-check-label" for="is_available">Available for ordering</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button class="btn btn-brand" type="submit">{{ $item->exists ? 'Save changes' : 'Add item' }}</button>
                            <a href="{{ route('manager.foods') }}" class="btn btn-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection