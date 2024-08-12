<?php

namespace App\Services\Prediction;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Prediction;
use Exception;

class PredictionService
{
    public function predict(Request $request, $user)
    {
        // Validate request data
        $request->validate([
            'BMI' => 'required|numeric',
            'PhysicalHealth' => 'required|numeric',
            'MentalHealth' => 'required|numeric',
            'SleepTime' => 'required|numeric',
            'AgeCategory' => 'required|integer',
            'Race' => 'required|integer',
            'Diabetic' => 'required|integer',
            'GenHealth' => 'required|integer',
            'Sex' => 'required|integer',
            'Smoking' => 'required|integer',
            'AlcoholDrinking' => 'required|integer',
            'Stroke' => 'required|integer',
            'DiffWalking' => 'required|integer',
            'PhysicalActivity' => 'required|integer',
            'Asthma' => 'required|integer',
            'KidneyDisease' => 'required|integer',
            'SkinCancer' => 'required|integer',
        ]);

        // Make a request to FastAPI to get the prediction
        $response = Http::post('http://127.0.0.1:8001/predict', $request->all());

        if ($response->successful()) {
            $prediction = $response->json()['prediction'];

            $message = $prediction <= 0.5
                ? "Sorry, You may have a heart disease"
                : "Congratulations, We think you don't have a heart disease";

            // Save the prediction result in the database
            Prediction::create([
                'user_id' => $user->id,
                'prediction' => $prediction
            ]);

            return [
                'data' => [
                    'message' => $message,
                    'prediction' => $prediction
                ],
                'status' => 200
            ];
        }

        return [
            'data' => [
                'message' => 'Error making prediction',
                'error' => $response->json()
            ],
            'status' => $response->status()
        ];
    }
}
