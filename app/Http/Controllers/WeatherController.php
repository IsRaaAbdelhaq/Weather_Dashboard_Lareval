<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WeatherController extends Controller
{
    /**
     * Get weather data for a city
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWeather(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'city' => 'required|string|max:100',
        ]);

        $city = $validated['city'];
        $apiKey = env('WEATHER_API_KEY');

        // Check if API key is configured
        if (!$apiKey) {
            return response()->json([
                'error' => 'Weather API key is not configured'
            ], 500);
        }

        try {
            // Get current weather
            $currentWeather = $this->fetchCurrentWeather($city, $apiKey);
            if (!$currentWeather) {
                return response()->json([
                    'error' => 'City not found'
                ], 404);
            }

            // Get forecast
            $forecast = $this->fetchForecast($city, $apiKey);

            // Build response
            $response = [
                'location_name' => $currentWeather['name'] ?? null,
                'country' => $currentWeather['sys']['country'] ?? null,
                'temp_c' => $currentWeather['main']['temp'] ?? null,
                'condition_text' => $currentWeather['weather'][0]['main'] ?? null,
                'icon' => $currentWeather['weather'][0]['icon'] ?? null,
                'humidity' => $currentWeather['main']['humidity'] ?? null,
                'wind_kph' => ($currentWeather['wind']['speed'] ?? 0) * 3.6, // Convert m/s to km/h
                'cloud' => $currentWeather['clouds']['all'] ?? null,
                'pressure_mb' => $currentWeather['main']['pressure'] ?? null,
                'forecast' => $forecast,
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch weather data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch current weather from OpenWeatherMap API
     */
    private function fetchCurrentWeather($city, $apiKey)
    {
        $url = "https://api.openweathermap.org/data/2.5/weather";

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url . "?q=" . urlencode($city) . "&appid=" . urlencode($apiKey) . "&units=metric",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 200) {
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Fetch 5-day forecast from OpenWeatherMap API
     */
    private function fetchForecast($city, $apiKey)
    {
        $url = "https://api.openweathermap.org/data/2.5/forecast";

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url . "?q=" . urlencode($city) . "&appid=" . urlencode($apiKey) . "&units=metric",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 200) {
            return [];
        }

        $data = json_decode($response, true);

        // Extract forecast days (5 day forecast - one entry every 3 hours, so 8 per day)
        $forecastDays = [];
        $dailyForecasts = [];

        if (isset($data['list'])) {
            foreach ($data['list'] as $forecast) {
                $date = substr($forecast['dt_txt'], 0, 10);

                if (!isset($dailyForecasts[$date])) {
                    $dailyForecasts[$date] = [
                        'temps' => [],
                        'condition' => $forecast['weather'][0]['main'] ?? null,
                        'icon' => $forecast['weather'][0]['icon'] ?? null,
                    ];
                }

                $dailyForecasts[$date]['temps'][] = $forecast['main']['temp'];
            }

            foreach ($dailyForecasts as $date => $forecast) {
                $forecastDays[] = [
                    'date' => $date,
                    'max_temp_c' => max($forecast['temps']),
                    'min_temp_c' => min($forecast['temps']),
                    'avg_temp_c' => array_sum($forecast['temps']) / count($forecast['temps']),
                    'condition' => $forecast['condition'],
                    'icon' => $forecast['icon'],
                ];
            }
        }

        return $forecastDays;
    }
}
