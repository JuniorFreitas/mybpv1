@extends('layouts.sistema')
@section('title', 'Manual do usuário')
@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 8px;">
        <h4 class="text-default mb-0">Manual do usuário</h4>
        <a href="{{ route('g.ajuda.manual.pdf') }}" class="btn btn-sm btn-outline-primary" download>
            <i class="fa fa-file-pdf"></i> Baixar PDF
        </a>
    </div>
    <hr class="bg-default" style="margin-top: 8px;">
@stop
@section('content')
    <iframe
        src="{{ route('g.ajuda.manual.conteudo') }}"
        title="Manual do usuário MyBPIN"
        class="ajuda-manual-frame"
    ></iframe>
@stop
@push('css')
    <style>
        .page-content {
            padding-bottom: 0 !important;
        }

        .ajuda-manual-frame {
            display: block;
            width: 100%;
            height: calc(100vh - 160px);
            min-height: 560px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            background: #fff;
        }
    </style>
@endpush
@push('js')
    <script>
        (function () {
            var app = Vue.createApp({});
            if (typeof window.registerGlobals === 'function') {
                window.registerGlobals(app);
            }
            app.mount('#app');
        })();
    </script>
@endpush
