<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('weather/{city}', [ApiController::class, 'getWeather']);
Route::get('weather/{city}/cached', [ApiController::class, 'getWeatherCached']);
