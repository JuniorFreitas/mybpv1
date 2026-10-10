<!doctype html>
<html lang="pt-Br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ env('APP_NAME') }}</title>
    @if(\App\Models\Sistema::verificaHdev())
        <meta name="robots" content="noindex">
    @endif
    <meta name="msapplication-TileColor" content="#072433">
    <meta name="theme-color" content="#072433">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/icons.min.css') }}">
    @include('layouts.favicon')
    <link rel="manifest" href="{{asset('manifest.json')}}">
    @if(env('APP_ENV') !== 'local')
        <script type="text/javascript">
            (function (c, l, a, r, i, t, y) {
                c[a] = c[a] || function () {
                    (c[a].q = c[a].q || []).push(arguments)
                };
                t = l.createElement(r);
                t.async = 1;
                t.src = "https://www.clarity.ms/tag/" + i;
                y = l.getElementsByTagName(r)[0];
                y.parentNode.insertBefore(t, y);
            })(window, document, "clarity", "script", "mltvhh6s7v");
        </script>
    @endif
</head>
<body style="background: url({{ asset('images/bg_login_bpin_mybp.jpg') }}) no-repeat #072333; background-size: cover;">
<div id="app" class="container mt-5" v-cloak>
    <div class="col-md-6 m-auto">
        <recupera-senha token=""></recupera-senha>
    </div>
</div>
<script src="{{ mix('js/app.js') }}"></script>
<script src="{{ mix('js/funcoes.js') }}"></script>
<script src="{{mix('js/recuperasenha/app.js')}}"></script>
</body>
</html>
{{--100.24.12.79--}}
