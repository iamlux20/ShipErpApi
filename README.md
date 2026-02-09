# ShipErpApi

## Installing and Running the Project

```bash
composer setup
```

The API will be available at `http://localhost:8000`

#### Behind The Scenes

```bash
composer setup (configurable in composer.json)

"composer install",
"@php -r \"file_exists('.env') || copy('.env.example', '.env');\"",
"@php artisan key:generate",
"@php artisan migrate --force",
"@php artisan serve"
```

## Running Tests

```bash
php artisan test
```

## High-Level Process

1. **Request received** - API receives city name as parameter
2. **Validation** - Validates API key configuration and city name
3. **Data retrieval** - Fetches weather data from OpenWeather API or cache
4. **Response formatting** - Transforms external API response into simplified JSON format
5. **Caching** (cached endpoint only) - Stores response for 10 minutes to reduce external API calls

### Available Endpoints

- `GET /api/weather/{city}` - Fetch weather data for a city
- `GET /api/weather/{city}/cached` - Fetch cached weather data
