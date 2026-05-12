<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function getWeather(Request $request)
    {
        $validated = $request->validate([
            'city' => 'required|string|max:100',
        ]);

        $city = $validated['city'];

        $openWeatherKey = env('OPENWEATHER_API_KEY');
        $weatherApiKey  = env('WEATHERAPI_KEY');

        if (!$openWeatherKey || !$weatherApiKey) {
            return response()->json([
                'error' => 'API keys are not configured properly'
            ], 500);
        }

        try {
            $current = $this->fetchCurrentWeather($city, $openWeatherKey);

            if (!$current || !isset($current['main'])) {
                return response()->json([
                    'error' => 'City not found'
                ], 404);
            }

            $url = "http://api.weatherapi.com/v1/forecast.json?key={$weatherApiKey}&q=" . urlencode($city) . "&days=5";

            $response = file_get_contents($url);
            $weatherData = json_decode($response, true);

            if (!$weatherData || isset($weatherData['error'])) {
                return response()->json([
                    'error' => 'Failed to fetch forecast data'
                ], 500);
            }

            $forecastDays = [];

            foreach ($weatherData['forecast']['forecastday'] as $day) {
                $forecastDays[] = [
                    'date' => $day['date'],
                    'avgtemp_c' => $day['day']['avgtemp_c'],
                    'max_temp_c' => $day['day']['maxtemp_c'],
                    'min_temp_c' => $day['day']['mintemp_c'],
                    'condition' => $day['day']['condition']['text'],
                    'icon' => $day['day']['condition']['icon'],
                ];
            }

            return response()->json([
                'location_name' => $current['name'] ?? null,
                'country' => $current['sys']['country'] ?? null,

                // OpenWeather (numeric data)
                'temp_c' => $current['main']['temp'] ?? null,
                'humidity' => $current['main']['humidity'] ?? null,
                'wind_kph' => ($current['wind']['speed'] ?? 0) * 3.6,
                'cloud' => $current['clouds']['all'] ?? null,
                'pressure_mb' => $current['main']['pressure'] ?? null,

                // WeatherAPI (visual + forecast)
                'condition_text' => $weatherData['current']['condition']['text'] ?? null,
                'icon' => $weatherData['current']['condition']['icon'] ?? null,
                'forecast' => $forecastDays,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch weather data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function fetchCurrentWeather($city, $apiKey)
    {
        $url = "https://api.openweathermap.org/data/2.5/weather?q="
            . urlencode($city)
            . "&appid="
            . $apiKey
            . "&units=metric";

        $response = @file_get_contents($url);

        if (!$response) return null;

        return json_decode($response, true);
    }
}