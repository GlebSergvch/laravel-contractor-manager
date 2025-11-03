<?php
declare(strict_types=1);

use App\Http\Api\V1\User\SigninController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], static function () {
    Route::group(['middleware' => 'auth:api'], static function () {
    });

    Route::post('/register', [SigninController::class, 'register']);
    Route::post('/login', [SigninController::class, 'login']);
    Route::post('/logout', [SigninController::class, 'logout']);
});
