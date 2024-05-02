<?php

namespace App\Http\Requests\OfflineBookingRequests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\FailedValidationResponse;

class UpdateAppointmentRequest extends FormRequest
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
            'time_id' => 'required|exists:times,id', 
            'date_id' => 'required|exists:dates,id', 
            'new_time' => 'required|string', 
            'new_date' => 'required|date|after_or_equal:today' ,
         
        ];
    }
}
