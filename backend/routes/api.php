<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CryptoPriceController;


Route::group(['prefix' => '/v1'], function () {
    Route::get('/health', function(){    
        return response()->json(['status' => 'ok']);
    });

    Route::get('/prices', [CryptoPriceController::class, 'recent']);
    Route::get('/prices/{datetime}', [CryptoPriceController::class, 'getPricesByDate']);
});