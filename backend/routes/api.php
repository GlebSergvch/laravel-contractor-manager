<?php

declare(strict_types=1);

//use App\Http\Api\V1\Counterparty\CounterpartyController;
use App\Http\Api\V1\Counterparty\CounterpartyCreateController;
use App\Http\Api\V1\Counterparty\CounterpartyListController;
use App\Http\Api\V1\User\UserAuthController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], static function () {
    Route::group(['middleware' => 'auth:sanctum'], static function () {
        Route::get('counterparties', CounterpartyListController::class);
        Route::post('counterparties', CounterpartyCreateController::class);
    });

    Route::post('/register', [UserAuthController::class, 'register']);
    Route::post('/login', [UserAuthController::class, 'login']);
    Route::post('/logout', [UserAuthController::class, 'logout']);
});
