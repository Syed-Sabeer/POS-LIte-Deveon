<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
// use App\Http\Controllers\Frontend\EventCalendarController;
use App\Http\Controllers\Auth\LoginController;
// use App\Http\Controllers\Frontend\UserRelationshipController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Auth\AuthController;
use App\Events\BpmBroadcasted;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Broadcast;
// Remove non-existent PusherController
// use App\Http\Controllers\PusherController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes that your application supports.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Remove conflicting route - Laravel handles broadcasting auth automatically
// Route::post('/pusher/auth', [PusherController::class, 'pusherAuth'])
//     ->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->post('/save-fcm-token', function (Request $request) {
    $request->validate([
        'token' => 'required|string',
    ]);

    $user = $request->user();

    \App\Models\FcmToken::updateOrCreate(
        ['user_id' => $user->id],
        ['token' => $request->token]
    );

    return response()->json(['message' => 'Token saved']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/profile/update', [AuthController::class, 'editProfile']);
    Route::get('/profile', [AuthController::class, 'getProfile']);
Route::post('/notify', [NotificationController::class, 'sendNotification']);
// Route::get('/events', [EventCalendarController::class, 'index']);
// Route::post('/events', [EventCalendarController::class, 'store']);
// Route::get('/events/{id}', [EventCalendarController::class, 'show']);
// Route::put('/events/{id}', [EventCalendarController::class, 'update']);
// Route::delete('/events/{id}', [EventCalendarController::class, 'destroy']);

  // Route::get('/relationships', [UserRelationshipController::class, 'index']);
    // Route::post('/relationships', [UserRelationshipController::class, 'store']);
    // Route::delete('/relationships/{id}', [UserRelationshipController::class, 'destroy']);
    // Route::get('/relationship-types', [UserRelationshipController::class, 'getAllRelationshipTypes']);

  Route::post('/bpm', function (Request $request) {
        $request->validate([
            'bpm' => 'required|numeric',
            'ekg' => 'array', // EKG data as array
        ]);

        $user = $request->user();

        $bpmData = [
            'user_id' => $user->id,
            'bpm' => $request->bpm,
            'time' => now()->toDateTimeString(),
            'ekg' => $request->ekg ?? [],
        ];

        // Broadcast BPM data event - pass individual parameters including EKG
        event(new BpmBroadcasted($user->id, $request->bpm, now()->toDateTimeString(), $request->ekg ?? []));

        // Optional: Cache latest BPM for quick GET access
        Cache::put("latest_bpm_user_{$user->id}", $bpmData, now()->addMinutes(10));

        return response()->json(['status' => 'broadcasted']);
    });

    // Optional: Retrieve latest BPM for this user
    Route::get('/bpm/latest', function (Request $request) {
        $user = $request->user();
        $latestBpm = Cache::get("latest_bpm_user_{$user->id}", [
            'user_id' => $user->id,
            'bpm' => null,
            'time' => null,
        ]);
        return response()->json($latestBpm);
    });


});
// Route::get('/hello', function () {
//     return response()->json(['message' => 'Hello']);
// });

// Auth API Routes
Route::post('/register', [RegisterController::class, 'registerAttempt']);
Route::post('/login', [LoginController::class, 'loginAttempt']);

Route::post('/password/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/password/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/password/reset', [AuthController::class, 'resetPasswordWithOtp']);


// Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::options('{any}', function () {
    return response()->json(['status' => 'OK'], 200);
})->where('any', '.*');