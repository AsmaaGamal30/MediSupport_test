<?php

use App\Http\Controllers\Article\ArticleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{AdminAuthController, UserAuthController, DoctorAuthController, UserSocialAuthController};
use App\Http\Controllers\HealthMatrix\{BloodPressureController, BloodSugarController, BMIController, HeartRateController};
use App\Http\Controllers\Password\{UserForgotPassword, UserResetPassword, DoctorForgotPassword, DoctorResetPassword};
use App\Http\Controllers\Rating\{RatingController};
use App\Http\Controllers\contact\{ContactController};
use App\Http\Controllers\OfflineBooking\User\{BookingController,OfflineDoctorsController};
use App\Http\Controllers\Chat\MessagesController;
use App\Http\Controllers\OfflineBooking\Doctor\DoctorOfflineBookingController;

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




//chat

/**
 * Authentication for pusher private channels
 */
Route::post('/user/chat/auth', [MessagesController::class, 'pusherAuth'])->middleware('auth:user');
Route::post('/doctor/chat/auth', [MessagesController::class, 'pusherDoctorAuth'])->middleware('auth:doctor');

/**
 *  Fetch info for specific id [user/doctor]
 */
Route::post('/user/chat/idInfo', [MessagesController::class, 'idDoctorFetchData'])->middleware('auth:user');
Route::post('/doctor/chat/idInfo', [MessagesController::class, 'idFetchData'])->middleware('auth:doctor');

/**
 * Send message route
 */
Route::post('/user/chat/sendMessage', [MessagesController::class, 'send'])->middleware('auth:user');
Route::post('/doctor/chat/sendMessage', [MessagesController::class, 'send'])->middleware('auth:doctor');


/**
 * Fetch messages
 */
Route::post('/user/chat/fetchMessages', [MessagesController::class, 'fetch'])->middleware('auth:user');
Route::post('/doctor/chat/fetchMessages', [MessagesController::class, 'fetch'])->middleware('auth:doctor');


/**
 * Download attachments route to create a downloadable links
 */
Route::get('/user/chat/download/{fileName}', [MessagesController::class, 'download'])->middleware('auth:user');
Route::get('/doctor/chat/download/{fileName}', [MessagesController::class, 'download'])->middleware('auth:doctor');


/**
 * Make messages as seen
 */
Route::post('/user/chat/makeSeen', [MessagesController::class, 'seen'])->middleware('auth:user');
Route::post('/doctor/chat/makeSeen', [MessagesController::class, 'seen'])->middleware('auth:doctor');


/**
 * Get contacts
 */
Route::get('/doctor/chat/getDoctorContacts', [MessagesController::class, 'getDoctorContacts'])->middleware('auth:doctor');
Route::get('/user/chat/getUserContacts', [MessagesController::class, 'getUserContacts'])->middleware('auth:user');



/**
 * Get shared photos
 */
Route::post('/doctor/chat/shared', [MessagesController::class, 'sharedPhotos'])->middleware('auth:doctor');
Route::post('/user/chat/shared', [MessagesController::class, 'sharedPhotos'])->middleware('auth:user');


/**
 * Delete Conversation
 */
Route::post('/user/chat/deleteConversation', [MessagesController::class, 'deleteConversation'])->middleware('auth:user');
Route::post('/doctor/chat/deleteConversation', [MessagesController::class, 'deleteConversation'])->middleware('auth:doctor');







//user blood pressure
Route::controller(BloodPressureController::class)->middleware('auth:user')->prefix('user/blood-pressure')->group(function () {
    Route::post('/store', 'store');
    Route::get('/get-all-systolic', 'getSystolicData');
    Route::get('/get-all-diastolic', 'getDiastolicData');
    Route::get('/get-latest-measurement', 'getLatestMeasurement');
    Route::get('/get-latest-three-measurements', 'getLatestThreeMeasurements');
    Route::get('/get-all-measurements', 'getAllMeasurements');
});

//user bmi
Route::controller(BMIController::class)->middleware('auth:user')->prefix('user/bmi')->group(function () {
    Route::post('/store', 'store');
    Route::get('/get-last-record', 'getLastRecord');
    Route::get('/get-all-records', 'getAllRecords');
});

//user blood sugar
Route::controller(BloodSugarController::class)->middleware(['custom-auth:' . 'user'])->prefix('user/blood-sugar')->group(function () {
    Route::post('/store', 'storeBloodSugar');
    Route::get('/get-all-records', 'getAllBloodSugarRecords');
    Route::get('/get-last-three-records', 'getLastThreeBloodSugarRecords');
    Route::get('/get-last-seven-records', 'getLastSevenBloodSugarRecords');
    Route::get('/get-last-record', 'getLastBloodSugarRecord');
    Route::get('/get-recommended-advice', 'getUserRecommendedAdvice');
    Route::get('/get-all-status', 'getAllBloodSugarStatus');
});

//user heart rate
Route::controller(HeartRateController::class)->middleware(['custom-auth:' . 'user'])->prefix('user/heart-rate')->group(function () {
    Route::post('/store', 'storeHeartRate');
    Route::get('/get-all-records', 'getAllHeartRateRecords');
    Route::get('/get-last-three-records', 'getLastThreeHeartRateRecords');
    Route::get('/get-last-seven-records', 'getLastSevenHeartRateRecords');
    Route::get('/get-last-record', 'getLastHeartRateRecord');
    Route::get('/get-recommended-advice', 'getUserRecommendedAdvice');
});

//Rating
Route::controller(RatingController::class)->prefix('auth/user')->group(function () {
    Route::post('/ratings', 'store');
});

//contact
Route::controller(ContactController::class)->group(function () {
    Route::post('/contact', 'store');
});

Route::controller(OfflineDoctorsController::class)->middleware(['custom-auth:' . 'user'])->prefix('auth/user')->group(function () {
    Route::get('/get-top-doctors', 'selectTopDoctorsByRating');
    Route::get('/get-all-doctors', 'selectDoctors');
    Route::get('/search-doctors', 'searchDoctors');
});

//user offline booking
Route::controller(BookingController::class)->middleware(['custom-auth:' . 'user'])->prefix('user/booking')->group(function () {
    Route::get('/get-doctor-details', 'getDoctorDetails');
    Route::get('/get-times', 'getDoctorDateTimes');
    Route::post('/appointment', 'BookAppointment');
    Route::get('/get-all-booking', 'selectUserBooking');
    Route::delete('/delete-booking', 'deleteBooking');
});



//articles
Route::get('/articles', [ArticleController::class, 'index'])->middleware(['auth:user,admin,doctor']);
Route::get('/articles/{id}', [ArticleController::class, 'show'])->middleware(['auth:user,admin,doctor']);
Route::post('/articles', [ArticleController::class, 'store'])->middleware('auth:doctor');
Route::put('/articles/{id}', [ArticleController::class, 'update'])->middleware('auth:doctor');
Route::delete('articles/{article}', [ArticleController::class, 'destroy'])->middleware('auth:doctor,admin');

//doctor offline booking
Route::controller(DoctorOfflineBookingController::class)->middleware(['custom-auth:' . 'doctor'])->prefix('doctor')->group(function () {
    Route::post('/store-date', 'storeDate');
    Route::post('/store-time', 'storeTime');
    Route::get('/all-booking', 'getAllOfflineBooking');
});