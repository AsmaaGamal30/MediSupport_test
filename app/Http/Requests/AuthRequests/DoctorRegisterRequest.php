<?php

namespace App\Http\Requests\AuthRequests;

use Illuminate\Foundation\Http\FormRequest;

class DoctorRegisterRequest extends FormRequest
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
            'admin_id' => 'required|exists:admins,id',
            'first_name' => 'required|string|between:2,20',
            'last_name' => 'required|string|between:2,30',
            'email' => 'required|string|email|max:100|unique:doctors',
            'password' => 'required|string|min:6',
            'avatar' => 'required|image|mimes:jpg,png,jpeg',
            'phone' => 'required|string|max:30',
            'specialization' => 'required|string',
            'bio' => 'required|string',
            'price' => 'required|numeric',
            'clinic_location' => 'required|string',
            //'active_status' => 'boolean',
            'working_hours' => 'required|string',
        ];
    }
}
