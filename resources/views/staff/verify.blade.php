@extends('layouts.app')
@section('title', 'Verify Token')
@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted">Type the token code the student shows you to find their order. Only tokens for your own outlet are found.</p>
                    <form method="POST" action="{{ route('staff.verify.lookup') }}">
                        @csrf
                        <input name="token_code" value="{{ old('token_code') }}" required maxlength="20" autofocus autocomplete="off"
                               class="form-control form-control-lg mb-3 text-uppercase @error('token_code') is-invalid @enderror"
                               placeholder="e.g. K7M2QX9A" aria-label="Token code">
                        @error('token_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <button class="btn btn-brand w-100" type="submit">Find order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection