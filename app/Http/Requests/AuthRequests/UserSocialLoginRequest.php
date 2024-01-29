<?php

namespace App\Http\Requests\AuthRequests;

use App\Traits\FailedValidationResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use function PHPUnit\Framework\isTrue;

class UserSocialLoginRequest extends FormRequest
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

    protected function failedValidation(Validator $validator)
    {
        $this->failedValidationResponse($validator);
    }
}
