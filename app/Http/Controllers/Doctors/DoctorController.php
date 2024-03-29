<?php

// App/Http/Controllers/Doctors/DoctorController.php

namespace App\Http\Controllers\Doctors;

use App\Models\Doctor;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\Doctor\DoctorResource;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\AuthRequests\UpdateDoctorRequest;
use App\Http\Requests\AuthRequests\UpdateDocrorsPasswordRequest;

class DoctorController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return DoctorResource::collection(Doctor::query()->paginate(10));
    }

    public function deleteDoctor($id)
    {
        if (!Auth::guard('admin')->check()) {
            return $this->error('Unauthorized', 401);
        }

        $doctor = Doctor::find($id);

        if (!$doctor) {
            return $this->error('Doctor not found', 404);
        }

        $doctor->delete();
        return $this->success('Doctor and their information deleted successfully');
    }

    public function updatePassword(UpdateDocrorsPasswordRequest $request)
    {
        if (!Auth::guard('doctor')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        try {
            $request->validate([
                'current_password' => ['required', 'string'],
                'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $doctorId = Auth::guard('doctor')->id();
            $doctor = Doctor::find($doctorId);

            if (!Hash::check($request->current_password, $doctor->password)) {
                throw ValidationException::withMessages(['current_password' => ['Current password does not match']]);
            }

            $doctor->password = Hash::make($request->new_password);
            $doctor->save();

            return $this->success('Password updated successfully');
        } catch (ValidationException $e) {
            return $this->error($e->getMessage(), 422); // Pass the error message to the error method
        }
    }

    public function getDoctorCount()
    {
        // Check if the user is authenticated as an admin
        if (!Auth::guard('admin')->check()) {
            return $this->error('Unauthorized', 401);
        }

        // Retrieve the count of doctors from the database
        $doctorCount = Doctor::count();

        return $this->successData('Number of Doctors', ['count' => $doctorCount]);
    }

    public function getFirstEightDoctors()
    {
        // Check if the user is authenticated as an admin
        if (!Auth::guard('admin')->check()) {
            return $this->error('Unauthorized', 401);
        }

        // Retrieve the first 8 doctors from the database
        $doctors = Doctor::take(8)->get();

        // Return the doctor resource collection
        return DoctorResource::collection($doctors);
    }
    public function updateDoctor(UpdateDoctorRequest $request, $id)
{
    try {
        // Ensure the user is authenticated as a doctor
        if (!Auth::guard('doctor')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        // Find the doctor by ID
        $doctor = Doctor::find($id);

        // Check if the doctor exists
        if (!$doctor) {
            return $this->error('Doctor not found', 404);
        }

        // Update the doctor's data based on the validated request
        $doctor->update($request->validated());

        // Return success response
        return $this->success('Doctor updated successfully');
    } catch (\Exception $e) {
        // Return error response if an exception occurs
        return $this->error($e->getMessage(), 500); // Internal Server Error
    }
}

}