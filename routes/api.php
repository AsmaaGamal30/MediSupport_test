<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{AdminAuthController, UserAuthController, DoctorAuthController, SocialLoginController};


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

//user auth
Route::controller(UserAuthController::class)->prefix('auth/user')->group(function () {
    Route::post('/login',  'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout');
    Route::post('/refresh',  'refresh');
    Route::get('/user-profile', 'userProfile');
});

//user social auth
Route::controller(SocialLoginController::class)->prefix('auth/user')->group(function () {
    Route::get('/{provider}/redirect',  'redirect');
    Route::get('/{provider}/callback',  'callback');
    Route::post('/social-login', 'handleProviderCallback');
});
