<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    {{-- CSRF token — required for all POST/PUT/DELETE AJAX calls --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Weather App</title>

    {{-- Link to CSS file in public/css/ --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

{{-- Background image wrapper --}}
<div class="bg-wrapper">
    <div class="bg-overlay"></div>
</div>

<div class="app-layout">

    {{-- Sidebar — desktop only --}}
    <aside class="sidebar">
        <div class="logo">&#127786;</div>
        <div class="nav-icon active" id="nav-dashboard">⊞</div>
        <div class="nav-icon" id="nav-search">&#128269;</div>
        <div class="nav-icon" id="nav-favorites">&#10084;</div>
        <div class="nav-icon" id="nav-settings">&#9881;</div>
    </aside>

    <div class="main-content">

        {{-- Header included from partials/header.blade.php --}}
        @include('partials.header')

        {{-- Message box for success/error --}}
        <div id="msg-box" class="msg"></div>

        {{-- This is where each page injects its content --}}
        @yield('content')

        {{-- Footer included from partials/footer.blade.php --}}
        @include('partials.footer')

    </div>
</div>

{{-- JS files loaded at bottom — app.js must be first --}}
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/API_Ops.js') }}"></script>
<script src="{{ asset('js/DB_Ops.js') }}"></script>

</body>
</html>