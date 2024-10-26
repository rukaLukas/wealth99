<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\CryptoPriceController;


Route::group(['prefix' => '/v1'], function () {   
    Route::get('/health', [HealthController::class, 'checkHealth']);
    Route::get('/prices', [CryptoPriceController::class, 'recent']);
    Route::get('/prices/{datetime}', [CryptoPriceController::class, 'getPricesByDate']);
});