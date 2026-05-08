<?php

use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

// Main page
Route::get('/', function () {
    return view('weather.index');
});

// Favorites CRUD
Route::get('/favorites',         [FavoriteController::class, 'index']);
Route::post('/favorites',        [FavoriteController::class, 'store']);
Route::put('/favorites/{id}',    [FavoriteController::class, 'update']);
Route::delete('/favorites/{id}', [FavoriteController::class, 'destroy']);

// Weather API
Route::post('/weather', [WeatherController::class, 'getWeather']);
