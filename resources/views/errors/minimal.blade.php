<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <style>
        .center{
            text-align: center;
            min-height: 100vh;
            width: 100%;
            background-color: #2d66af;
            overflow-x: hidden;
            display: flex;
            justify-content: center;
            flex-direction: column;
             
            color: #ffffff;
        }
        .error-code{
            font-size: 80px;
            margin: 0;
        }
        .msg-error{
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .msg-error a{
            color: #6370e0;
            font-size: 18px;
            font-weight: 700;
            text-underline: none;
            text-decoration: none;
        }
    </style>

</head>
<body class="center">
    <div class="">
        <div class="">
           <h1 class="error-code"> @yield('code')</h1>
        </div>

        <div class="msg-error">
            @yield('message')
            <a href="{{ route('home', app()->getLocale())  }}">
               {{ __('Go to home')}}
            </a>
        </div>
    </div>
</body>
</html>
