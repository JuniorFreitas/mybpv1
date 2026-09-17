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
    <meta name="msapplication-TileImage" content="{{asset('images/icons/ms-icon-144x144.png')}}">
    <meta name="theme-color" content="#072433">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/icons.min.css') }}">
    <link rel="apple-touch-icon" sizes="57x57" href="{{asset('images/icons/apple-icon-57x57.png')}}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{asset('images/icons/apple-icon-60x60.png')}}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{asset('images/icons/apple-icon-72x72.png')}}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{asset('images/icons/apple-icon-76x76.png')}}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{asset('images/icons/apple-icon-114x114.png')}}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{asset('images/icons/apple-icon-120x120.png')}}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{asset('images/icons/apple-icon-144x144.png')}}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{asset('images/icons/apple-icon-152x152.png')}}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('images/icons/apple-icon-180x180.png')}}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{asset('images/icons/android-icon-192x192.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('images/icons/favicon-32x32.png')}}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{asset('images/icons/favicon-96x96.png')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('images/icons/favicon-16x16.png')}}">
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
    @stack('css')
</head>
<body data-sidebar="dark">
<div id="app" v-cloak>

    <div class="layout-wrapper">
        <div class="vertical-menu">
            <div data-simplebar class="h-100">
                <div id="sidebar-menu">
                    <ul class="metismenu list-unstyled" id="side-menu">
                        @include('layouts.menu')
                    </ul>
                </div>
            </div>
        </div>

        <barra-top :usuario="{{ auth()->user()->load('Papel','FotoPerfil') }}"></barra-top>

        <div class="main-content">
            <div class="page-content" style="padding: 15px 15px 15px 7px;">
                <div class="container-fluid pb-5 mt-4 pt-5 ">
                    <div class="row">
                        @if(url()->current() != route('g.dashboard'))
                            <div class="col-12">
                                <div class="page-title-box d-flex align-items-center justify-content-between">
                                    <h4 class="mb-0 font-size-18"> @yield('content_header')</h4>
                                </div>
                            </div>

                            {{--                            <div class="col-12 mb-3">--}}
                            {{--                                <div class="page-title-right">--}}
                            {{--                                    <ol class="breadcrumb m-0">--}}
                            {{--                                        <li class="breadcrumb-item"><a href="{{ route('g.dashboard') }}">Inicio</a></li>--}}
                            {{--                                        @yield('breadcrumb')--}}
                            {{--                                    </ol>--}}
                            {{--                                </div>--}}
                            {{--                            </div>--}}
                        @endif

                        <div class="col-12">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        © 2019 - <?= date('Y') ?> MyBP
                    </div>
                    {{--                    <div class="col-sm-6">--}}
                    {{--                        <div class="text-sm-right d-none d-sm-block">--}}
                    {{--                            <a href="">Suporte <i class="fab fa-whatsapp"></i> (98) 999023762</a>--}}
                    {{--                        </div>--}}
                    {{--                    </div>--}}
                </div>
            </div>
        </footer>
    </div>

    <nps-modal />
</div>

@php
    $bannerChamadosAte = \Carbon\Carbon::parse('2026-09-30 23:59:59', 'America/Sao_Paulo');
    $bannerChamadosAtivo = now('America/Sao_Paulo')->lte($bannerChamadosAte)
        && request()->routeIs('g.dashboard');
    $bannerChamadosUrl = '';
    if ($bannerChamadosAtivo && app(\App\Services\BpChamados\BpChamadosWidgetTokenService::class)->isEnabled()) {
        $bannerChamadosUrl = route('g.bp-chamados.index');
    }
@endphp
@if($bannerChamadosAtivo)
    <div id="bannerChamadosRoot" class="banner-chamados-overlay" hidden>
        <div class="banner-chamados-backdrop" data-banner-close></div>
        <div class="banner-chamados-dialog" role="dialog" aria-modal="true" aria-label="Novo canal de suporte no MyBP">
            <button type="button" class="banner-chamados-close" aria-label="Fechar banner" data-banner-close>&times;</button>
            @if($bannerChamadosUrl !== '')
                <a href="{{ $bannerChamadosUrl }}" class="banner-chamados-link" data-banner-close>
                    <img
                        src="{{ asset('img/banner/banner_chamado.webp') }}"
                        alt="Novo canal de suporte já disponível no MyBP. Acesse o menu Chamados."
                        class="banner-chamados-img"
                        width="1200"
                        height="630"
                    >
                </a>
            @else
                <img
                    src="{{ asset('img/banner/banner_chamado.webp') }}"
                    alt="Novo canal de suporte já disponível no MyBP. Acesse o menu Chamados."
                    class="banner-chamados-img"
                    width="1200"
                    height="630"
                >
            @endif
        </div>
    </div>
    <style>
        .banner-chamados-overlay {
            position: fixed;
            inset: 0;
            z-index: 10050;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .banner-chamados-overlay[hidden] { display: none !important; }
        .banner-chamados-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(7, 36, 51, 0.55);
        }
        .banner-chamados-dialog {
            position: relative;
            z-index: 1;
            max-width: min(960px, 96vw);
            max-height: 92vh;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 16px 48px rgba(7, 36, 51, 0.35);
            background: #fff;
        }
        .banner-chamados-close {
            position: absolute;
            top: 0.4rem;
            right: 0.5rem;
            z-index: 2;
            width: 2.25rem;
            height: 2.25rem;
            border: 0;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.92);
            color: #072433;
            font-size: 1.5rem;
            line-height: 1;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        .banner-chamados-link { display: block; line-height: 0; }
        .banner-chamados-img {
            display: block;
            width: 100%;
            height: auto;
            max-height: 92vh;
            object-fit: contain;
        }
        body.banner-chamados-open { overflow: hidden; }
    </style>
    <script>
        (function () {
            var root = document.getElementById('bannerChamadosRoot');
            if (!root) return;

            function fechar() {
                root.setAttribute('hidden', 'hidden');
                document.body.classList.remove('banner-chamados-open');
            }

            root.querySelectorAll('[data-banner-close]').forEach(function (el) {
                el.addEventListener('click', fechar);
            });
            document.addEventListener('keydown', function (ev) {
                if (ev.key === 'Escape' && !root.hasAttribute('hidden')) fechar();
            });

            root.removeAttribute('hidden');
            document.body.classList.add('banner-chamados-open');
        })();
    </script>
@endif

<script src="{{ mix('js/app.js') }}"></script>
<script src="{{ asset('js/funcoes.js') }}"></script>
<script>
    window.MYBP_ASSINATURA_DIGITAL_HABILITADA = {{ \App\Models\Sistema::assinaturaDigitalHabilitada() ? 'true' : 'false' }};
</script>
<script>
    (function ($) {
        "use strict";

        function initActiveMenu() {/* === following js will activate the menu in left side bar based on url ====*/
            $("#sidebar-menu a").each(function () {
                var pageUrl = window.location.href.split(/[?#]/)[0];
                if (this.href === pageUrl) {
                    $(this).addClass("active");
                    $(this).parent().addClass("mm-active"); /* add active to li of the current link*/
                    $(this).parent().parent().addClass("mm-show");
                    $(this).parent().parent().prev().addClass("mm-active"); /* add active class to an anchor*/
                    $(this).parent().parent().parent().addClass("mm-active");
                    $(this).parent().parent().parent().parent().addClass("mm-show"); /* add active to li of the current link*/
                    $(this).parent().parent().parent().parent().parent().addClass("mm-active");
                }
            });
        }

        function initMenuItemScroll() {/* focus active menu in left sidebar*/
            $(document).ready(function () {
                if ($("#sidebar-menu").length > 0 && $("#sidebar-menu .mm-active .active").length > 0) {
                    var activeMenu = $("#sidebar-menu .mm-active .active").offset().top;
                    if (activeMenu > 300) {
                        activeMenu = activeMenu - 300;
                        $(".simplebar-content-wrapper").animate({scrollTop: activeMenu}, "slow");
                    }
                }
            });
        }

        function initDropdownMenu() {
            $(".dropdown-menu a.dropdown-toggle").on("click", function (e) {
                if (!$(this).next().hasClass("show")) {
                    $(this).parents(".dropdown-menu").first().find(".show").removeClass("show");
                }
                var $subMenu = $(this).next(".dropdown-menu");
                $subMenu.toggleClass("show");
                return false;
            });
        }

        function initComponents() {
            $(function () {
                $("[data-toggle=\"tooltip\"]").tooltip();
            });
            $(function () {
                $("[data-toggle=\"popover\"]").popover();
            });
        }

        function init() {
            initActiveMenu();
            initMenuItemScroll();
            initDropdownMenu();
            initComponents();
            Waves.init();
        }

        init();
    })(jQuery);
</script>
@stack('js')
<script>
    $("#side-menu").metisMenu();
</script>
</body>
</html>
