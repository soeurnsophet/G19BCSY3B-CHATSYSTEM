<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\OAuthController;
use Illuminate\Support\Facades\Route;

Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/signin', [AuthController::class, 'signin']);
Route::get('/verify/email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verify.email');
Route::post('/send/verification-email', [AuthController::class, 'sendVerificationEmail']);
Route::post('/send/reset-password-email', [AuthController::class, 'sendResetPasswordEmail']);
Route::post('/set/new-password', [AuthController::class, 'setNewPassword'])->name('set.new-password');
// OAuth routes for multiple drivers (e.g., Google, Facebook, Twitter, Github, etc.)
Route::get('/{driver}/oauth/redirect', [OAuthController::class, 'OAuthRedirect']);
Route::get('/{driver}/oauth/callback', [OAuthController::class, 'OAuthCallback']);
Route::post('/oauth/exchange/token', [OAuthController::class, 'OAuthExchangeToken'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/signout', [AuthController::class, 'signout']);
    Route::get('/verify', [AuthController::class, 'verify']);
    Route::put('/create/password', [AuthController::class, 'createPassword']);
    Route::put('/change/password', [AuthController::class, 'changePassword']);
    Route::put('/update/profile-image', [AuthController::class, 'updateProfileImage']);
    Route::delete('/delete/profile-image', [AuthController::class, 'deleteProfileImage']);
});