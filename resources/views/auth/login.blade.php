@extends('layouts.guest')
@section('title', 'Log in')
@section('content')
    <h2 class="h4 mb-3">Log in</h2>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input id="password" type="password" name="password" required class="form-control">
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>
        <button class="btn btn-brand w-100" type="submit">Log in</button>
    </form>
    <p class="text-center text-muted mt-3 mb-0">New student? <a href="{{ route('register') }}">Create an account</a></p>
@endsection