<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ config('app.name', 'LIGO') }}</title>

<!-- Fonts -->


<!-- Scripts -->
@vite(['resources/sass/app.scss', 'resources/js/app.ts'])
