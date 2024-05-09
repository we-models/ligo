<head>

    <title>{{getConfigValue('SYSTEM_NAME')}}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="shortcut icon" type="image/png" href="{{asset('images/logo.png')}}"/>

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{asset('css/sb-admin.css')}}" rel="stylesheet">
    <link href="{{asset('css/panel.css')}}" rel="stylesheet">
    <link href="{{asset('css/quill.snow.css')}}" rel="stylesheet">
    <link href="{{asset('css/quill.core.css')}}" rel="stylesheet">

    <script>
        localStorage.setItem('base', "{{url('/', app()->getLocale())}}");
        fetch("{{route('font.all', app()->getLocale())}}" + '?all=true')
            .then(response => response.json())
            .then((response)=> {
                let all_fonts = [];
                response.data.forEach((font) => {
                    const linkElement = document.querySelector(`link[href="${font.url}"]`);
                    if (!linkElement) {
                        const newLinkElement = document.createElement('link');
                        newLinkElement.rel = 'stylesheet';
                        newLinkElement.href = font.url;
                        document.head.appendChild(newLinkElement);
                    }
                    all_fonts.push(font.name);
                });
                window['all_fonts'] =  all_fonts;
            });
    </script>

    @yield('custom_styles')
</head>
