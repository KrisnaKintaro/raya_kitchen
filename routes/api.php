<?php

use App\Http\Controllers\Api\Pembeli\KatalogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('pembeli')->group(function () {
    Route::get('/katalog', [KatalogController::class, 'index']);
});
