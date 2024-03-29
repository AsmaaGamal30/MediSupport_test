<?php

namespace App\Http\Requests\AuthRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
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
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:doctors,email,' . $this->route('id'),
            'avatar' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'specialization' => 'sometimes|string|max:255',
            'bio' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'clinic_location' => 'sometimes|string|max:255',
        ];
    }
}