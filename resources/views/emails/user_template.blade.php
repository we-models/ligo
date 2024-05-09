<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $content->subject }}</title>

    <!-- Font Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
        rel="stylesheet" />


    <style>
        :root {
            --primary: #3e3c7d;
            --secondary: #6370e0;
            --accent: #c5d1ff;
            --grey: #b1b0b4;
            --white: #ffffff;
            --black: #000000;
            --shade: #737293;
            --shadeSecondary: #00000040;
            --links: #3e3c7dcf;
            --background: #f8fafc;
            --headerEmail: #e9edff;
        }

        .body {
            margin: 0;
            padding: 0;
            font-family: "Roboto", sans-serif;
            background-color: #f8fafc;
            color: #3e3c7d;

            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header-email-user-template,
        .footer-email-user-template {
            text-align: center;
            padding: 20px 0;
            background-color: #e9edff;
        }

        .img-header,
        .img-footer {
            width: 115px;
            height: 44.5px;
        }

        .content-email-user-template {
            padding: 85px;
            background-color: #ffffff;
        }

        .title {
            margin: 0 0 10px;
        }

        .title-typography {
            font-size: 20px;
            font-weight: 700;
            line-height: 23px;
            letter-spacing: 0.02em;
        }

        .primary-text {
            margin: 0 0 26px;
        }

        .text-body {
            margin: 0 0 30px;
        }

        .button-email {
            text-align: center;
            margin-bottom: 20px;
        }

        .link-button-a {
            padding: 10px 20px;
            background-color: #3e3c7d;
            color: #c5d1ff;
            text-decoration: none;
            border-radius: 60px;
            /* Typography */
            font-size: 20px;
            font-weight: 700;
            line-height: 30px;
            letter-spacing: 0em;
        }

        .text-body-strong {
            font-size: 15px;
            font-weight: 700;
            line-height: 23px;
            letter-spacing: 0.02em;
        }
    </style>
</head>

<body>
    <div class="body">
        <div class="header-email-user-template">
            <img class="img-header" src="{{ asset('image_system/navbar/ligo.png') }}" alt="LIGO" />
        </div>
        <div class="content-email-user-template">
            <!-- title -->
            <div class="title">
                <p class="title-typography">
                    {{ $content->titleText }}
                </p>
            </div>

            <!-- primary text -->
            <div class="primary-text">
                <p>
                    {{ $content->primaryText }}
                </p>
            </div>


            <!-- text-body -->
            <div class="text-body">
                {!! $content->mail !!}
            </div>

            <!-- button -->
            <div class="button-email">
                <a class="link-button-a" href="{{ route('home', app()->getLocale()) }}">{{ $content->buttonText }}</a>
            </div>

            <div class="final-text">
                <p>
                    {{ $content->finalText }}
                </p>
            </div>
        </div>
        <div class="footer-email-user-template">
            <img class="img-footer" src="{{ asset('image_system/navbar/ligo.png') }}" alt="LIGO" />
        </div>
    </div>
</body>

</html>
