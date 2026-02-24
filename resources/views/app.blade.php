<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="site-language" content="{{ app()->getLocale() }}">
    <!-- Styles -->
    <link rel="icon" type="image/png" href="{{ url('/fav.png') }}" sizes="42x42">

    @role('admin')
        <link rel="stylesheet" href="{{ url('alternative/css/app.css?v=' . config('app.user_assets_hash')) }}">
        <link rel="stylesheet" href="{{ url('alternative/css/protected-file-3HnJAidsKJ1.css?v=' . config('app.user_assets_hash')) }}">
    @else

        @if($mobileApp)
            <link rel="stylesheet" href="{{ url('dependencies/css/app.css?v=' . config('app.user_assets_hash')) }}">
            <link rel="stylesheet" href="{{ url('dependencies/css/mobile.css?v=' . config('app.user_assets_hash')) }}">
        @else
            <link rel="stylesheet" href="{{ url('frontend/css/app.css?v=' . config('app.user_assets_hash')) }}">
            <link rel="stylesheet" href="{{ url('frontend/css/site.css?v=' . config('app.user_assets_hash')) }}">
        @endif
    @endrole

    <!-- Scripts -->
    <script>
        @php
            $path = resource_path("/lang/". app()->getLocale() .".json");
            $translations = preg_replace( "/\r|\n/", "", file_get_contents($path));
        @endphp
        window.LANGUAGE = "{{ app()->getLocale() }}";
        window.TRANSLATIONS = {!! json_encode($translations); !!}
    </script>

    @if(config('app.analytics'))
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('app.analytics') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', '{{ config('app.analytics') }}');
    </script>
    @endif
</head>

@if($mobileApp)
<body id="body" class="dark" style="background-color:#1d2234;">
@else
<body id="body" class="{{ $themeMode }}">
@endif
@inertia
</body>

@role('admin')
    <script src="{{ url('js/type.js?v=' . config('app.user_assets_hash')) }}" defer></script>
    <script src="{{ url('alternative/js/protected-file-3HnJAidsKJ1.js?v=' . config('app.user_assets_hash')) }}" defer></script>
@else

    @if($mobileApp)
        <script src="{{ url('dependencies/js/mobile.js?v=' . config('app.user_assets_hash')) }}" defer></script>
    @else
        <script src="{{ url('js/type.js?v=' . config('app.user_assets_hash')) }}" defer></script>
        <script src="{{ url('frontend/js/app.js?v=' . config('app.user_assets_hash')) }}" defer></script>
    @endif

@endrole
</html>
