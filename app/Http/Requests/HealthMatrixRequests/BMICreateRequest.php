<?php

namespace App\Http\Requests\HealthMatrixRequests;

use Illuminate\Foundation\Http\FormRequest;

class BMICreateRequest extends FormRequest
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
            'bmi_advices_id' => 'exists:bmi_advices,id',
            'gender' => 'required|boolean',
            'age' => 'required|integer|min:1',
            'height' => 'required|numeric|min:1',
            'weight' => 'required|numeric|min:1',
            'result' => 'required|numeric|min:1', // Assuming result field is still required, adjust as needed
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Calculate BMI and include it in the request data
        $this->merge([
            'result' => $this->calculateBMI($this->height, $this->weight)
        ]);
    }

    /**
     * Calculate BMI based on height and weight.
     *
     * @param float $height
     * @param float $weight
     * @return float
     */
    private function calculateBMI($height, $weight)
    {
        // BMI Formula: weight (kg) / (height (m) * height (m))
        return $weight / (($height / 100) * ($height / 100));
    }
}
