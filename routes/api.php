<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{AdminAuthController, UserAuthController, DoctorAuthController, UserSocialAuthController};
use App\Http\Controllers\HealthMatrix\BloodPressureController;
use App\Http\Controllers\Password\{UserForgotPassword, UserResetPassword, DoctorForgotPassword, DoctorResetPassword};



//admin auth
Route::controller(AdminAuthController::class)->prefix('auth/admin')->group(
    function () {
        Route::post('/login', 'login');
        Route::post('/logout', 'logout');
        Route::post('/refresh', 'refresh');
        Route::get('/user-profile', 'userProfile');
    }
);

//doctor auth
Route::controller(DoctorAuthController::class)->prefix('auth/doctor')->group(
    function () {
        Route::post('/login',  'login');
        //the admin shold register the doctor
        Route::post('/register', 'register')->middleware('auth:admin');
        Route::post('/logout', 'logout');
        Route::post('/refresh', 'refresh');
        Route::get('/user-profile',  'userProfile');
    }
);

//doctor password
Route::controller(DoctorForgotPassword::class)->prefix('auth/doctor')->group(function () {
    Route::post('/forgot-password', 'forgot');
});
Route::controller(DoctorResetPassword::class)->prefix('auth/doctor')->group(function () {
    Route::post('/verfiy-code', 'verifyVerificationCode');
    Route::post('/reset-password', 'resetPassword');
});



//user auth
Route::controller(UserAuthController::class)->prefix('auth/user')->group(
    function () {
        Route::post('/login',  'login');
        Route::post('/register', 'register');
        Route::post('/logout', 'logout');
        Route::post('/refresh',  'refresh');
        Route::get('/user-profile', 'userProfile');
    }
);

//user social auth
Route::controller(UserSocialAuthController::class)->prefix('auth/user')->group(function () {
    Route::get('/{provider}/redirect',  'redirect');
    Route::get('/{provider}/callback',  'callback');
    Route::post('/social-login', 'handleProviderCallback');
});

//user password
Route::controller(UserForgotPassword::class)->prefix('auth/user')->group(function () {
    Route::post('/forgot-password', 'forgot');
});
Route::controller(UserResetPassword::class)->prefix('auth/user')->group(function () {
    Route::post('/verfiy-code', 'verifyVerificationCode');
    Route::post('/reset-password', 'resetPassword');
});

//user blood pressure
Route::controller(BloodPressureController::class)->middleware('auth:user')->prefix('user/blood-pressure')->group(function () {
    Route::post('/store', 'store');
    Route::get('/get-all-systolic', 'getSystolicData');
    Route::get('/get-all-diastolic', 'getDiastolicData');
    Route::get('/get-latest-measurement', 'getLatestMeasurement');
    Route::get('/get-latest-three-measurements', 'getLatestThreeMeasurements');
    Route::get('/get-all-measurements', 'getAllMeasurements');
});