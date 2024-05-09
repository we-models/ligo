<!doctype html>
<html lang="{{app()->getLocale()}}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{$content->subject}}</title>
    <style>
        .container{
            padding:2em;
        }
        .center{
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="center">{{__('New Account registered')}}</h1>
        <p>
            {{__('A new account has been created for you at ')}}
            <a href="{{route('home', app()->getLocale())}}">{{route('home', app()->getLocale())}}</a>
        </p>
        <p>
            <strong>{{__('Email') }}: </strong> {{$content->mail["email"]}} <br>
            <strong>{{__('Password')}}: </strong> {{$content->mail["password"]}}
        </p>
        <p>
            {{__('It is recommended that you change your password as soon as possible.')}}
        </p>
    </div>
</body>
</html>
