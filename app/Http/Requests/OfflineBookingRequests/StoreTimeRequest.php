<?php

namespace App\Http\Requests\OfflineBookingRequests;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\FailedValidationResponse;

class StoreTimeRequest extends FormRequest
{
    use FailedValidationResponse;
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
            'time' => 'required',
            'date_id' =>'required|numeric|exists:dates,id',
        ];
    }
}
