@extends('layouts.sistema')
@section('title', 'Avaliação de Experiência')
@section('content_header', 'Avaliação de Experiência')
@section('content')
    <avaliacao-experiencia
        :api-base="'{{ url('g/relatorios/avaliacao-de-experiencia') }}'"
        :current-user-id="{{ auth()->id() ?? 'null' }}"
    ></avaliacao-experiencia>
@stop
@push('js')
    <script src="{{ mix('js/g/relatorios/avaliacao-experiencia/app.js') }}"></script>
@endpush
