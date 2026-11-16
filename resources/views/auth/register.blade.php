@extends('layouts.guest')
@section('title', 'Register')
@section('content')
    <h2 class="h4 mb-3">Student registration</h2>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        @foreach ([
            ['name', 'Full name', 'text'],
            ['email', 'Email', 'email'],
            ['student_id', 'Student ID (7 digits)', 'text'],
            ['phone', 'Mobile number (optional)', 'text'],
            ['password', 'Password (min 8 characters)', 'password'],
            ['password_confirmation', 'Confirm password', 'password'],
        ] as [$field, $label, $type])
            <div class="mb-3">
                <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                <input id="{{ $field }}" type="{{ $type }}" name="{{ $field }}"
                       value="{{ $type === 'password' ? '' : old($field) }}"
                       class="form-control @error($field) is-invalid @enderror">
                @error($field) <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        @endforeach
        <button class="btn btn-brand w-100" type="submit">Create account</button>
    </form>
    <p class="text-center text-muted mt-3 mb-0">Already registered? <a href="{{ route('login') }}">Log in</a></p>
@endsection