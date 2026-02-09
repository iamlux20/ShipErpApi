<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function getWeather($city, $source = 'external')
    {
        if (trim(config('app.owm_api')) === '')
            return response()->json(['error' => 'API key is not set'], 500);

        if (trim($city) === '')
            return response()->json(['error' => 'City name is required'], 400);

        $apiUrl = "https://api.openweathermap.org/data/2.5/weather?q={$city}&units=metric&lang=en&appid=" . config('app.owm_api');
        $response = Http::get($apiUrl);

        if ($response->failed())
            return response($response['message'], $response['cod']);

        $weatherData = [
            'city' => $response['name'],
            'temperature' => $response['main']['temp'],
            'weather_description' => $response['weather'][0]['description'],
            'timestamp' => Carbon::parse($response['dt'])->format('Y-m-d H:i:s'),
            'source' => $source,
        ];
        return response()->json($weatherData, 200);
    }

    public function getWeatherCached($city)
    {
        if (trim(config('app.owm_api')) === '')
            return response()->json(['error' => 'API key is not set'], 500);

        if (trim($city) === '')
            return response()->json(['error' => 'City name is required'], 400);


        if (Cache::has("city-weather-{$city}")) {
            return Cache::get("city-weather-{$city}");
        }

        return Cache::remember('city-weather-' . $city, now()->addMinutes(10), function () use ($city) {
            return $this->getWeather($city, 'cache');
        });
    }
}
