<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Chat\MessagesController;
use App\Http\Controllers\Doctors\DoctorController;
use App\Http\Controllers\Article\ArticleController;
use App\Http\Controllers\Rating\{RatingController};
use App\Http\Controllers\contact\{ContactController};
use App\Http\Controllers\VideoCall\UserVideoCallController;
use App\Http\Controllers\VideoCall\DoctorVideoCallController;
use App\Http\Controllers\OnlineBooking\OnlineDoctorController;
use App\Http\Controllers\OnlineBooking\OnlineBookingController;
use App\Http\Controllers\Notifications\UserNotificationController;
use App\Http\Controllers\Notifications\DoctorNotificationController;
use App\Http\Controllers\Password\{UserForgotPassword, UserResetPassword,};
use App\Http\Controllers\OfflineBooking\Doctor\DoctorOfflineBookingController;
use App\Http\Controllers\OfflineBooking\User\{BookingController, OfflineDoctorsController};
use App\Http\Controllers\Auth\{AdminAuthController, UserAuthController, DoctorAuthController, UserSocialAuthController};
use App\Http\Controllers\HealthMatrix\{BloodPressureController, BloodSugarController, BMIController, HeartRateController};
use App\Http\Controllers\OnlineBooking\HandleStripeWebhooksController;
use App\Http\Controllers\OnlineBooking\PaymentController;
use App\Http\Controllers\Users\UserController;

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
        Route::post('/login', 'login');
        //the admin shold register the doctor
        Route::post('/register', 'register')->middleware('auth:admin');
        Route::post('/logout', 'logout');
        Route::post('/refresh', 'refresh');
        Route::get('/user-profile', 'userProfile');
    }
);





//user auth
Route::controller(UserAuthController::class)->prefix('auth/user')->group(
    function () {
        Route::post('/login', 'login');
        Route::post('/register', 'register');
        Route::post('/logout', 'logout');
        Route::post('/refresh', 'refresh');
        Route::get('/user-profile', 'userProfile');
        Route::post('/update-profile', 'updateUser');
        Route::delete('/delete-account', 'deleteAccount');
    }
);

//user social auth
Route::controller(UserSocialAuthController::class)->prefix('auth/user')->group(function () {
    Route::get('/{provider}/redirect', 'redirect');
    Route::get('/{provider}/callback', 'callback');
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
    Route::get('/get-last-three-records', 'getThreeLastRecords');
    Route::get('/get-last-seven-records', 'getLastSevenBMIRecords');


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
Route::post('/articles/{id}', [ArticleController::class, 'update'])->middleware('auth:doctor');
Route::delete('articles/{article}', [ArticleController::class, 'destroy'])->middleware('auth:doctor,admin');

//doctor offline booking
Route::controller(DoctorOfflineBookingController::class)->middleware(['custom-auth:' . 'doctor'])->prefix('doctor')->group(function () {
    Route::post('/store-date', 'storeDate');
    Route::post('/store-time', 'storeTime');
    Route::get('/all-booking', 'getAllOfflineBooking');
});


//get all doctors for admin
Route::get('all-doctors', [DoctorController::class, 'index'])->middleware('auth:admin');
//delete doctor
Route::delete('/doctors/{id}', [DoctorController::class, 'deleteDoctor'])->middleware('auth:admin');
//count doctor
Route::get('/doctors/count', [DoctorController::class, 'getDoctorCount'])->middleware('auth:admin');
//get first eight doctors
Route::get('/doctors/first-eight', [DoctorController::class, 'getFirstEightDoctors'])->middleware('auth:admin');

// update password doctor
Route::put('/doctor-password', [DoctorController::class, 'updatePassword'])->middleware('auth:doctor');
//update doctor info
Route::post('/update-doctors', [DoctorController::class, 'updateDoctor'])->middleware('auth:doctor');

// update password admin
Route::post('/admin-password', [AdminAuthController::class, 'updatePassword'])->middleware('auth:admin');

//get all contact for admin
Route::get('/all-contact', [ContactController::class, 'index'])->middleware('auth:admin');
//get first eight contact for admin
Route::get('/contacts/first-eight', [ContactController::class, 'getFirstEightContacts'])->middleware('auth:admin');

//user online booking
Route::prefix('auth/user')->group(function () {
    Route::post('/online-bookings', [OnlineBookingController::class, 'store']);
    Route::get('/all-bookings', [OnlineBookingController::class, 'getUserBookings']);
    Route::delete('/delete-bookings/{id}',[OnlineBookingController::class, 'deleteBooking']);

});


//user notifications
Route::prefix('auth/user')->group(function () {
    Route::get('/notifications', [UserNotificationController::class, 'index']);
    Route::put('/notifications/{id}', [UserNotificationController::class, 'update']);
    Route::post('/notifications/mark-all-read', [UserNotificationController::class, 'markAsRead']);
});

//online doctors
Route::prefix('auth/user')->group(function () {
    Route::get('/online-doctors', [OnlineDoctorController::class, 'getOnlineDoctors']);
    Route::get('/ten-online-doctors', [OnlineDoctorController::class, 'getFirstTenOnlineDoctors']);
    Route::get('/online-doctor/{id}', [OnlineDoctorController::class, 'getOnlineDoctorById']);

});

//doctor online booking
Route::prefix('auth/doctor')->group(function () {
    Route::post('/bookings-accept', [OnlineDoctorController::class, 'acceptBooking']);
    Route::get('/all-bookings', [OnlineDoctorController::class, 'getDoctorBookings']);

});


//doctor notifications
Route::prefix('auth/doctor')->group(function () {
    Route::get('/notifications', [DoctorNotificationController::class, 'index']);
    Route::put('/notifications/{id}', [DoctorNotificationController::class, 'update']);
    Route::post('/notifications/mark-all-read', [DoctorNotificationController::class, 'markAsRead']);
});

//user payment
Route::post('/user/online-booking/payment/{bookingId}', [PaymentController::class, 'makePayment'])->middleware('auth:user');
Route::post('/stripe/webhook', [HandleStripeWebhooksController::class, 'handleWebhook']);


//doctor video call
Route::prefix('auth/doctor')->group(function () {
    Route::post('/video-call/token', [DoctorVideoCallController::class, 'generateToken']);
    Route::post('/video-call/start', [DoctorVideoCallController::class, 'startCall']);
    Route::post('/video-call/end', [DoctorVideoCallController::class, 'endCall']);
    Route::post('/call/accept', [DoctorVideoCallController::class, 'acceptCall']);
});


//user video call
Route::prefix('auth/user')->group(function () {
    Route::post('/video-call/token', [UserVideoCallController::class, 'generateToken']);
    Route::post('/video-call/start', [UserVideoCallController::class, 'startCall']);
    Route::post('/video-call/end', [UserVideoCallController::class, 'endCall']);
    Route::post('/call/accept', [UserVideoCallController::class, 'acceptCall']);
});

//fetch users medical data

Route::prefix('auth/doctor')->middleware('auth:doctor')->group(function () {
    Route::get('/fetchLatestMedicalData', [DoctorController::class, 'fetchLatestMedicalData']);
    Route::get('/fetchMedicalData/{userId}', [DoctorController::class, 'fetchMedicalData']);
});

//users data for admin panel
Route::get('all-users', [UserController::class, 'index'])->middleware('auth:admin');
Route::get('/user/count', [UserController::class, 'getUsersCount'])->middleware('auth:admin');
Route::get('/users/first-eight', [UserController::class, 'getFirstEightUsers'])->middleware('auth:admin');
