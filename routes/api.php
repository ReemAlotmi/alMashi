<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\RideController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DriverRateController;
use App\Http\Controllers\RequestRideController;
use App\Http\Controllers\PassengerRateController;
use App\Http\Controllers\PassengerRideController;
use App\Http\Controllers\CarClassificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('view-profile', [UserController::class, 'viewProfile'])->middleware('auth:sanctum');
Route::post('edit-profile', [UserController::class, 'editProfile'])->middleware('auth:sanctum');
Route::post('edit-profile/resend-code', [UserController::class, 'editProfile'])->middleware('auth:sanctum');
Route::post('sign-out', [UserController::class, 'signOut'])->middleware('auth:sanctum');
Route::post('current-location-premission', [UserController::class, 'addLocation'])->middleware('auth:sanctum');
Route::post('sign-up', [UserController::class, 'createUser']);
Route::post('sign-in', [UserController::class, 'signIn']);
Route::post('resend-code', [UserController::class, 'signIn']);


Route::post('sign-up/verification-code', [OtpController::class, 'verify']); //->middleware('auth:sanctum');
Route::post('sign-in/verification-code', [OtpController::class, 'verify']);
Route::post('edit-mobile-no-verification', [OtpController::class, 'verifyNew'])->middleware('auth:sanctum');


Route::get('classifications', [CarClassificationController::class, 'index']);


Route::get('all-rides', [RideController::class, 'allRides']);
Route::post('initiate-ride', [RideController::class, 'newRide'])->middleware('auth:sanctum');
Route::post('driver-info', [RideController::class, 'driverInfo'])->middleware('auth:sanctum');
Route::post('edit-price', [RideController::class, 'editPrice'])->middleware('auth:sanctum');
Route::post('accept-ride', [RideController::class, 'acceptRide'])->middleware('auth:sanctum');
Route::get('activate-ride', [RideController::class, 'activateRide'])->middleware('auth:sanctum');
Route::post('terminate', [RideController::class, 'terminate'])->middleware('auth:sanctum');


Route::post('reserve-ride', [PassengerRideController::class, 'reserveRide'])->middleware('auth:sanctum');


Route::post('rate-driver', [DriverRateController::class, 'rateDriver'])->middleware('auth:sanctum');
Route::post('rate-passenger', [PassengerRateController::class, 'ratePassenger'])->middleware('auth:sanctum');


Route::post('passneger-request', [RequestRideController::class, 'passengerInfo'])->middleware('auth:sanctum');
Route::get('my-orders-driver', [RequestRideController::class, 'myOrdersDriver'])->middleware('auth:sanctum');
Route::post('cancel-request', [RequestRideController::class, 'requestCancel'])->middleware('auth:sanctum');
Route::post('request-ride', [RequestRideController::class, 'requestRide'])->middleware('auth:sanctum');
Route::get('check-request-status', [RequestRideController::class, 'requestStatus'])->middleware('auth:sanctum');



//for admin
Route::get('users', [UserController::class, 'index']);
Route::get('rides', [RideController::class, 'index']);




// Route::get('users/{id}', [UserController::class, 'show']);
// Route::post('users', [UserController::class, 'store']);
// Route::post('users/{id}', [UserController::class, 'update']);
// Route::delete('users/{id}', [UserController::class, 'destroy']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
