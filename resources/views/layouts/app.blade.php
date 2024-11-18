<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/x-icon" href="/image_system/system/ligo.png">

    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.ts'])
</head>

<body>
    <div id="app" style="display: none">

        @if (Auth::check())
            @php
                $configuration_groups = getGroupsOfConfiguration();
            @endphp
        @endif

        @if (Auth::check())
            <div>
                <nav-bar-component url_logout="{{ route('logout', app()->getLocale()) }}"
                    settings_menu="{{ $configuration_groups }}" />
            </div>
        @endif

        @if (Auth::check())
            <div class="mid-seccion" id="mid-seccion">
                <div class="icon-float-menu" id="div-float-menu">
                    <i class="fa-solid fa-bars" id="icon-float-menu"></i>
                </div>
                @include('includes.structure.menu')
                <main class="scroll-content">

                    @yield('content')
                </main>
            </div>
        @else
            <main>
                @yield('content')
            </main>
        @endif



        @if (Auth::check())
            <footer-component />
        @endif

    </div>
    @include('includes.structure.scripts')
</body>

</html>
