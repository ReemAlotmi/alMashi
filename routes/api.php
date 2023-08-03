<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\RideController;
use App\Http\Controllers\UserController;
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

Route::get('users', [UserController::class, 'index']);
Route::get('classifications', [CarClassificationController::class, 'index']);
Route::get('view-profile/{id}', [UserController::class, 'viewProfile']);
Route::post('initiate-ride/{id}', [RideController::class, 'newRide']);


Route::post('sign-up', [UserController::class, 'createUser']);
//Route::post('verification-code', [UserController::class, 'verify']);

Route::post('sign-up/verification-code', [OtpController::class, 'verify']);

//sign-in/verification-code

Route::get('users/{id}', [UserController::class, 'show']);

Route::post('users', [UserController::class, 'store']);

Route::post('users/{id}', [UserController::class, 'update']);

Route::delete('users/{id}', [UserController::class, 'destroy']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
