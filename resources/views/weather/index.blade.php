@extends('layouts.app')

@section('content')

<div class="body-grid">

    <!-- LEFT: weather info + forecast -->
    <div class="left-panel">
        <div class="weather-label">Weather Forecast</div>

        <div id="condition" class="weather-title">Loading...</div>
        <div id="weather-desc" class="weather-desc"><span id="date"></span></div>

        <div id="forecast-days" class="forecast-row"></div>
    </div>

    <!-- RIGHT: current weather + favorites -->
    <div class="right-panel">

        <!-- Search bar -->
        <div class="glass-card search-container">
            <input
                type="text"
                id="city-input"
                class="search-input"
                placeholder="Enter a city or country..." />
            <button class="search-btn">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     width="20" height="20">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
            <button id="fav-btn" class="fav-btn">
                <svg id="heart-icon" viewBox="0 0 24 24" width="20" height="20">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
                          fill="none" stroke="white" stroke-width="1.5" />
                </svg>
            </button>
            <div class="spinner" id="spinner"></div>
        </div>

        <!-- Current weather card -->
        <div class="glass-card main-weather-card" id="current-weather-card">

            <div class="location-row">&#128205; <span id="city-name">--</span></div>

            <div class="big-temp" id="temperature"><span id="temp">--</span>°C</div>

            <div id="weather-icon"></div>

            <div class="stats-row">
                <span class="stat-item">
                    <span class="stat-label">Wind</span>
                    <span id="wind-speed">--</span>
                </span>
                <span class="stat-item">
                    <span class="stat-label">Humidity</span>
                    <span id="humidity">--</span>
                </span>
                <span class="stat-item">
                    <span class="stat-label">Cloud</span>
                    <span id="cloudy">--</span>
                </span>
                <span class="stat-item">
                    <span class="stat-label">Pressure</span>
                    <span id="pressure">--</span>
                </span>
            </div>
        </div>

        <!-- Favorites list -->
        <div class="glass-card">
            <div class="weather-label">
                <span class="label-pill">Favorites</span>
                <svg class="heart-icon-small" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                </svg>
            </div>
            <div id="favorites-list"></div>
        </div>

        <!-- Save to favorites form -->
        <div class="glass-card">
            <input
                type="text"
                id="notes-input"
                class="notes-input"
                placeholder="Add note (optional)" />

            <input type="hidden" id="city-hidden" />

            <button class="save-btn">
                + Save current city to favorites
            </button>
        </div>

    </div>
</div>

@endsection
