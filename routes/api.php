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
Route::get('view-profile', [UserController::class, 'viewProfile'])->middleware('auth:sanctum');
Route::post('edit-profile', [UserController::class, 'editProfile'])->middleware('auth:sanctum');
Route::post('edit-mobile-no-verification', [OtpController::class, 'verifyNew'])->middleware('auth:sanctum');
Route::post('sign-out', [UserController::class, 'signOut'])->middleware('auth:sanctum');
Route::post('current-location-premission', [UserController::class, 'addLocation'])->middleware('auth:sanctum');


Route::post('initiate-ride', [RideController::class, 'newRide']);


Route::post('sign-up', [UserController::class, 'createUser']);
Route::post('sign-in', [UserController::class, 'signIn']);
Route::post('resend-code', [UserController::class, 'signIn']);


Route::post('sign-up/verification-code', [OtpController::class, 'verify']); //->middleware('auth:sanctum');
Route::post('sign-in/verification-code', [OtpController::class, 'verify']);


Route::get('users/{id}', [UserController::class, 'show']);

Route::post('users', [UserController::class, 'store']);

Route::post('users/{id}', [UserController::class, 'update']);

Route::delete('users/{id}', [UserController::class, 'destroy']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
