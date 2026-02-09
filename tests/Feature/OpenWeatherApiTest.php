<?php

namespace Tests\Feature;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class OpenWeatherApiTest extends TestCase
{
    protected $weatherService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->weatherService = $this->app->make(ApiController::class);
    }

    public function test_getWeather_successfully_fetches_weather_data()
    {
        Config::set('app.owm_api', 'test-api-key');

        Http::fake([
            'https://api.openweathermap.org/data/2.5/weather*' => Http::response([
                'name' => 'London',
                'main' => ['temp' => 15.5],
                'weather' => [['description' => 'Cloudy']],
                'dt' => 1609459200,
            ]),
        ]);

        $result = $this->weatherService->getWeather('London');

        $this->assertEquals(200, $result->status());
        $data = $result->getData(true);
        $this->assertEquals('London', $data['city']);
        $this->assertEquals(15.5, $data['temperature']);
    }

    public function test_getWeather_returns_error_when_api_key_missing()
    {
        Config::set('app.owm_api', '');

        $result = $this->weatherService->getWeather('London');

        $this->assertEquals(500, $result->status());
    }

    public function test_getWeatherCached_returns_cached_data()
    {
        Config::set('app.owm_api', 'test-api-key');

        $cachedData = [
            'city' => 'London',
            'temperature' => 15.5,
            'weather_description' => 'Cloudy',
            'timestamp' => '2021-01-01 00:00:00',
            'source' => 'cache',
        ];

        Cache::shouldReceive('has')->once()->andReturn(true);
        Cache::shouldReceive('get')->once()->andReturn($cachedData);

        $result = $this->weatherService->getWeatherCached('London');

        $this->assertEquals($cachedData, $result);
    }

    public function test_getWeatherCached_fetches_data_when_not_cached()
    {
        Config::set('app.owm_api', 'test-api-key');

        Http::fake([
            'https://api.openweathermap.org/data/2.5/weather*' => Http::response([
                'name' => 'Paris',
                'main' => ['temp' => 12.0],
                'weather' => [['description' => 'Rainy']],
                'dt' => 1609459200,
            ]),
        ]);

        Cache::shouldReceive('has')->once()->andReturn(false);
        Cache::shouldReceive('remember')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        $result = $this->weatherService->getWeatherCached('Paris');

        $this->assertEquals(200, $result->status());
        $data = $result->getData(true);
        $this->assertEquals('Paris', $data['city']);
        $this->assertEquals('cache', $data['source']);
    }
}
