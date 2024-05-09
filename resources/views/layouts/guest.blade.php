<!DOCTYPE html>
<html lang="es">
@include('includes_guest.head')
<body id="page-top">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{route('home', app()->getLocale())}}">
                <img src="{{asset('images/logo.png')}}" alt="{{env('APP_NAME')}}" height="80px">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav">
                    <a class="nav-link site-name" href="#">{{env('APP_NAME')}}</a>
                </div>
            </div>
        </div>
        <div class="d-flex social_media">
            <a href="#" class="social-network">
                <i class="fa-brands fa-facebook"></i>
            </a>

            <a href="#" class="social-network">
                <i class="fa-brands fa-twitter"></i>
            </a>

            <a href="#" class="social-network">
                <i class="fa-brands fa-instagram"></i>
            </a>

            <a href="#" class="social-network">
                <i class="fa-brands fa-linkedin-in"></i>
            </a>

        </div>
    </nav>
    <div id="wrapper">

        @yield('content')
    </div>
    @include('includes_guest.footer')
    @include('includes_guest.scripts')
</body>
</html>
