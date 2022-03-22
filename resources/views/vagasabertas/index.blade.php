<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="msapplication-TileColor" content="#072433">
    <meta name="msapplication-TileImage" content="{{asset('/')}}ms-icon-144x144.png">
    <meta name="theme-color" content="#072433">
    <title>@yield('title')</title>
{{--    <link rel="preload" href="{{mix('js/app.js')}}" as="script">--}}
{{--    <link rel="preload" href="{{mix('js/funcoes.js')}}" as="script">--}}
{{--    <link rel="apple-touch-icon" sizes="57x57" href="{{asset('/')}}apple-icon-57x57.png">--}}
{{--    <link rel="apple-touch-icon" sizes="60x60" href="{{asset('/')}}apple-icon-60x60.png">--}}
{{--    <link rel="apple-touch-icon" sizes="72x72" href="{{asset('/')}}apple-icon-72x72.png">--}}
{{--    <link rel="apple-touch-icon" sizes="76x76" href="{{asset('/')}}apple-icon-76x76.png">--}}
{{--    <link rel="apple-touch-icon" sizes="114x114" href="{{asset('/')}}apple-icon-114x114.png">--}}
{{--    <link rel="apple-touch-icon" sizes="120x120" href="{{asset('/')}}apple-icon-120x120.png">--}}
{{--    <link rel="apple-touch-icon" sizes="144x144" href="{{asset('/')}}apple-icon-144x144.png">--}}
{{--    <link rel="apple-touch-icon" sizes="152x152" href="{{asset('/')}}apple-icon-152x152.png">--}}
{{--    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('/')}}apple-icon-180x180.png">--}}
{{--    <link rel="icon" type="image/png" sizes="192x192" href="{{asset('/')}}android-icon-192x192.png">--}}
{{--    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('/')}}favicon-32x32.png">--}}
{{--    <link rel="icon" type="image/png" sizes="96x96" href="{{asset('/')}}favicon-96x96.png">--}}
{{--    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('/')}}favicon-16x16.png">--}}
    <link rel="manifest" href="{{asset('/')}}manifest.json">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css"
          integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
    @stack('css')
        <style>
            .my-login-page .footer {
                margin: inherit;
            }

            .footer {
                margin-top: 20px;
                width: 100%;
                height: inherit;
                line-height: inherit;
            }

            .social-links a {
                font-size: 18px;
                display: inline-block;
                background: #0F4C60;
                color: #fff;
                line-height: 1;
                padding: 8px 0;
                margin-right: 4px;
                border-radius: 50%;
                text-align: center;
                width: 36px;
                height: 36px;
                transition: .3s;
            }

            .social-links a:hover {
                background: #031E2D;
                color: #fff;
            }
        </style>
</head>
<body>
<div id="app" v-cloak>

    <vagas-abertas :empresa_id="{{$empresa_id}}" :vaga_aberta_id="{{$vaga_aberta_id}}"></vagas-abertas>

</div>
<script src="{{mix('js/app.js')}}"></script>
<script src="{{mix('js/funcoes.js')}}"></script>
<script src="{{mix('js/vagas-abertas/app.js')}}"></script>
</body>
</html>
