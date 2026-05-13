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

        $city = trim($validated['city']);

        $openWeatherKey = env('OPENWEATHER_API_KEY');
        $weatherApiKey  = env('WEATHERAPI_KEY');

        if (!$openWeatherKey || !$weatherApiKey) {
            return response()->json([
                'error' => 'API keys are not configured properly'
            ], 500);
        }

        try {
            // ── 1. OpenWeatherMap — current weather
            $current = $this->fetchWithCurl(
                "https://api.openweathermap.org/data/2.5/weather?q="
                . urlencode($city) . "&appid={$openWeatherKey}&units=metric"
            );

            if (!$current || !isset($current['name'])) {
                return response()->json([
                    'error' => 'City not found. Please try again.'
                ], 404);
            }

            // ── 2. OpenWeatherMap — 5 day forecast (every 3 hours) 
            $forecastData = $this->fetchWithCurl(
                "https://api.openweathermap.org/data/2.5/forecast?q="
                . urlencode($city) . "&appid={$openWeatherKey}&units=metric"
            );

            // ── 3. WeatherAPI — current icon 
            $weatherApiCurrent = $this->fetchWithCurl(
                "https://api.weatherapi.com/v1/current.json?key={$weatherApiKey}&q="
                . urlencode($city)
            );

            $currentIcon = $weatherApiCurrent['current']['condition']['icon']
                ?? '//cdn.weatherapi.com/weather/64x64/day/113.png';

            // ── 4. WeatherAPI — forecast icons (3 days max on free plan) 
            $weatherApiForecast = $this->fetchWithCurl(
                "https://api.weatherapi.com/v1/forecast.json?key={$weatherApiKey}&q="
                . urlencode($city) . "&days=3"
            );

            $forecastIcons = [];
            foreach ($weatherApiForecast['forecast']['forecastday'] ?? [] as $day) {
                $forecastIcons[$day['date']] = $day['day']['condition']['icon'];
            }

            // ── 5. Build daily forecast from OpenWeatherMap 3-hour data 
            $daily = [];
            foreach ($forecastData['list'] ?? [] as $item) {
                $date = explode(' ', $item['dt_txt'])[0];
                if (!isset($daily[$date])) {
                    $daily[$date] = ['temps' => []];
                }
                $daily[$date]['temps'][] = $item['main']['temp'];
            }

            // ── 6. Filter days with enough readings + build array
            $forecastDays = [];
            foreach ($daily as $date => $data) {
                if (count($data['temps']) >= 4) {
                    $forecastDays[] = [
                        'date'      => $date,
                        'avgtemp_c' => round(
                            array_sum($data['temps']) / count($data['temps']), 2
                        ),
                        'icon'      => $forecastIcons[$date]
                            ?? '//cdn.weatherapi.com/weather/64x64/day/113.png'
                    ];
                }
            }

            $forecastDays = array_slice($forecastDays, 0, 5);

            // ── 7. Return clean response 
            return response()->json([
                'location_name'  => $current['name'],
                'country'        => $current['sys']['country'],
                'temp_c'         => number_format($current['main']['temp'], 2, '.', ''),
                'condition_text' => $current['weather'][0]['description'],
                'icon'           => $currentIcon,
                'humidity'       => $current['main']['humidity'],
                'wind_kph'       => round($current['wind']['speed'] * 3.6, 1),
                'cloud'          => $current['clouds']['all'],
                'pressure_mb'    => $current['main']['pressure'],
                'forecast'       => $forecastDays,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Failed to fetch weather data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ── Reusable CURL helper 
    private function fetchWithCurl(string $url): ?array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $raw = curl_exec($ch);
        curl_close($ch);

        if (!$raw) return null;

        return json_decode($raw, true);
    }
}