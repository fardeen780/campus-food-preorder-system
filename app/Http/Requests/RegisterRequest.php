<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email'))),
            'student_id' => trim((string) $this->input('student_id')),
            'name' => trim((string) $this->input('name')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'student_id' => ['required', 'regex:/^\d{7}$/', 'unique:users,student_id'],
            'phone' => ['nullable', 'regex:/^01\d{9}$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.regex' => 'Student ID must be exactly 7 digits.',
            'student_id.unique' => 'This student ID is already registered.',
            'phone.regex' => 'Enter an 11-digit mobile number starting with 01.',
        ];
    }
}