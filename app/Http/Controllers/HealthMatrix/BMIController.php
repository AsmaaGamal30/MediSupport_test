<?php

namespace App\Http\Controllers\HealthMatrix;

use App\Models\BMI;
use App\Models\BMIAdvice;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\BMI\BMIResource;
use App\Http\Resources\BMI\BMIHistoryResource;
use App\Http\Requests\HealthMatrixRequests\BMICreateRequest;

class BMIController extends Controller
{
    use ApiResponse;

    public function store(BMICreateRequest $request) 
    {
        // Check if the user is authenticated
        if (!Auth::guard('user')->check()) {
            return $this->error('Unauthorized', 401);
        }
        $userId = Auth::guard('user')->user()->id;

        $validatedData = $request->validated();

        $height = $validatedData['height'];
        $weight = $validatedData['weight'];

        // Calculate BMI
        $bmi = $this->calculateBMI($height, $weight);

        // Get BMI result category
        $result = $this->getResult($height, $weight);

        // Get advice based on BMI result
        $advice = $this->getAdvice($result);

        // Store BMI record for the current user
        $this->storeBMIRecord($userId, $validatedData['gender'], $validatedData['age'], $height, $weight, $bmi, $advice);

        return $this->success('BMI data stored successfully');
    }

    private function calculateBMI($height, $weight)
    {
        // Convert height to meters
        $heightInMeters = $height / 100; // Assuming height is in centimeters

        // Calculate BMI
        $bmi = $weight / ($heightInMeters * $heightInMeters);

        // Round BMI to two decimal places
        $bmi = round($bmi, 2);

        return $bmi;
    }

    private function getResult($height, $weight)
    {
        $bmi = $this->calculateBMI($height, $weight);

        if ($bmi < 18.5) {
            return 'Underweight';
        } elseif ($bmi >= 18.5 && $bmi <= 24.9) {
            return 'Normal weight';
        } elseif ($bmi >= 25 && $bmi <= 29.9) {
            return 'Overweight';
        } elseif ($bmi >= 30 && $bmi <= 34.9) {
            return 'Obesity (Class 1)';
        } elseif ($bmi >= 35 && $bmi <= 39.9) {
            return 'Obesity (Class 2)';
        } else {
            return 'Extreme Obesity (Class 3)';
        }
    }

    private function getAdvice($result)
{
    // Fetch advice from database based on result category
    $advice = BMIAdvice::where('key', str_replace(' ', '_', strtolower($result)))->first();

    // If advice is not found for the specific result category, fetch the default advice
    if (!$advice) {
        // Fetch default advice from database
        $defaultAdvice = BMIAdvice::where('key', 'default')->first();

        // If default advice is not found, create a new one
        if (!$defaultAdvice) {
            $defaultAdvice = BMIAdvice::create([
                'key' => 'default',
                'advice' => 'Default advice message',
            ]);
        }

        $advice = $defaultAdvice;
    }

    return $advice;
}



    private function storeBMIRecord($userId, $gender, $age, $height, $weight, $bmi, $advice)
    {
        $bmiRecord = new BMI();
        $bmiRecord->user_id = $userId;
        $bmiRecord->gender = $gender;
        $bmiRecord->age = $age;
        $bmiRecord->height = $height;
        $bmiRecord->weight = $weight;
        $bmiRecord->result = $bmi; // Store the calculated BMI value

        // Check if $advice is null
        if ($advice !== null) {
            $bmiRecord->bmi_advice_id = $advice->id;
        } else {
            // Handle the case where $advice is null (no advice found)
            // For example, you can set a default value or log the issue
            $bmiRecord->bmi_advice_id = null; // Set to null or provide a default value
            // Log the issue or provide a default advice message
        }

        $bmiRecord->save();
    }

    public function getLastRecord()
{
    // Check if the user is authenticated
    if (!Auth::guard('user')->check()) {
        return $this->unauthorizedResponse();
    }

    $userId = Auth::guard('user')->user()->id;

    // Select the last BMI record
    $lastRecord = BMI::latest()->first();

    // Transform the data using a resource class
    $resource = new BMIResource($lastRecord);

// BMIController.php

return $this->successData('Last BMI record retrieved successfully', $resource);
}

public function getAllRecords(Request $request)
{
    // Check if the user is authenticated
    if (!Auth::guard('user')->check()) {
        return $this->unauthorizedResponse();
    }

    $userId = Auth::guard('user')->user()->id;

    // Retrieve all BMI records paginated with 5 records per page
    $records = BMI::where('user_id', $userId)->paginate(10);

    // Transform the data using a resource class
    $resource = BMIHistoryResource::collection($records);

    // Add links for all pages
    $paginationLinks = [
        'first_page_url' => $records->url(1),
        'last_page_url' => $records->url($records->lastPage()),
        'prev_page_url' => $records->previousPageUrl(),
        'next_page_url' => $records->nextPageUrl(),
        'total' => $records->total(),
    ];

    // Append pagination links to the response data
    $responseData = [
        'data' => $resource,
        'links' => $paginationLinks,
    ];

    return $this->successData('BMI records retrieved successfully', $responseData);
}


public function getThreeLastRecords()
{
    // Check if the user is authenticated
    if (!Auth::guard('user')->check()) {
        return $this->unauthorizedResponse();
    }

    $userId = Auth::guard('user')->user()->id;

    // Select the three most recent BMI records
    $lastRecords = BMI::where('user_id', $userId)->latest()->take(3)->get();

    // Transform the data using a resource class
    $resource = BMIHistoryResource::collection($lastRecords);

    return $this->successData('Three most recent BMI records retrieved successfully', $resource);
}
public function getLastSevenBMIRecords()
{
    try {
        $userId = auth()->guard('user')->user()->id;

        $bmiRecords = BMI::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(7)
            ->get();


        $data = BMIResource::collection($bmiRecords);

        return $this->successData('Last seven BMI records selected', $data);

    } catch (\Exception $e) {
        return $this->error('Failed to fetch last seven records', 500);
    }
}




    }