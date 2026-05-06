<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255', 'required_without:name'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255', 'required_without:name'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.Auth::id()],
            'bio' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'regex:/^09\d{9}$/'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'address' => ['nullable', 'string', 'max:255'],
            'preferences' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.image' => 'Avatar must be a valid image file.',
            'avatar.mimes' => 'Avatar must be a JPG or PNG image.',
            'avatar.max' => 'Avatar must not be greater than 2MB.',
        ];
    }
}
