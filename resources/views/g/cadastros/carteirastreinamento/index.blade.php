@extends('layouts.sistema')
@section('title', 'Carteiras e Etiquetas')
@section('content_header', 'Carteiras e Etiquetas')
@section('content')
    <div class="pt-1">
        <carteiras-etiquetas-templates-cadastro
            :is-admin-global="true"
            aba-inicial="{{ request('aba', 'carteiras') }}"
        ></carteiras-etiquetas-templates-cadastro>
    </div>
@stop
@push('js')
    <script src="{{ mix('js/g/carteirastreinamento/app.js') }}"></script>
@endpush
