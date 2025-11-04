<?php

declare(strict_types=1);

use App\Http\Api\V1\User\UserAuthController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], static function () {
    Route::group(['middleware' => 'auth:api'], static function () {
    });

    Route::post('/register', [UserAuthController::class, 'register']);
    Route::post('/login', [UserAuthController::class, 'login']);
    Route::post('/logout', [UserAuthController::class, 'logout']);
});
