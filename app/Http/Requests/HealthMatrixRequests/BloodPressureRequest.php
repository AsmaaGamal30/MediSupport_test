<?php

namespace App\Http\Requests\HealthMatrixRequests;

use Illuminate\Foundation\Http\FormRequest;

class BloodPressureRequest extends FormRequest
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
            'user_id' => 'exists:users,id',
            'pressure_advice_id' => 'exists:pressure_advice,id',
            'systolic' => 'required|integer',
            'diastolic' => 'required|integer',
        ];
    }
}
