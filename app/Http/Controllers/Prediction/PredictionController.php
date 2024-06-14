<?php

namespace App\Http\Controllers\Prediction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Prediction;

class PredictionController extends Controller
{
    public function predict(Request $request)
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

        // Get the current authenticated user
        $user = auth()->user();

        // Make a request to FastAPI to get the prediction
        $response = Http::post('http://127.0.0.1:8001/predict', [
            'BMI' => $request->BMI,
            'PhysicalHealth' => $request->PhysicalHealth,
            'MentalHealth' => $request->MentalHealth,
            'SleepTime' => $request->SleepTime,
            'AgeCategory' => $request->AgeCategory,
            'Race' => $request->Race,
            'Diabetic' => $request->Diabetic,
            'GenHealth' => $request->GenHealth,
            'Sex' => $request->Sex,
            'Smoking' => $request->Smoking,
            'AlcoholDrinking' => $request->AlcoholDrinking,
            'Stroke' => $request->Stroke,
            'DiffWalking' => $request->DiffWalking,
            'PhysicalActivity' => $request->PhysicalActivity,
            'Asthma' => $request->Asthma,
            'KidneyDisease' => $request->KidneyDisease,
            'SkinCancer' => $request->SkinCancer,
        ]);

        if ($response->successful()) {
            // Get prediction result
            $prediction = $response->json()['prediction'];

            // Determine the message based on prediction value
            if ($prediction <= 0.5) {
                $message = "Sorry, You may have a heart disease";
            } else {
                $message = "Congratulations, We think you don't have a heart disease";
            }

            // Save the prediction result in the database
            $result = new Prediction();
            $result->user_id = $user->id;
            $result->prediction = $prediction;
            $result->save();

            return response()->json([
                'message' => $message,
                'prediction' => $prediction
            ]);
        }

        return response()->json([
            'message' => 'Error making prediction',
            'error' => $response->json()
        ], $response->status());
    }
}
