<!DOCTYPE html>
<html lang="{{app()->getLocale()}}">
@include('includes.head')
<body id="page-top">
<div id="wrapper">
    @include('includes.menu')
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div id="app" >
                <div style="height: 100%; ">
                    @include('includes.navbar')
                                        @yield('content')
                                    </div>
                                </div>
                        </div>

                    @include('includes.footer')
            </div>
        </div>

        @include('includes.scripts')
</body>
</html>
