<?php

namespace App\Http\Requests\AuthRequests;

use Illuminate\Foundation\Http\FormRequest;

class UserRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|between:2,20',
            'last_name' => 'required|string|between:2,30',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            'photo' => 'image|mimes:jpg,png,jpeg',
        ];
    }
}
