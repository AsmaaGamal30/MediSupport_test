<?php

namespace App\Http\Requests\AuthRequests;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\FailedValidationResponse;
use Illuminate\Contracts\Validation\Validator;

class UserSocialAuthRequest extends FormRequest
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
            'provider' => 'required|string|in:google,facebook',
            'access_provider_token' => 'required|string',
        ];
    }

}
