@extends('layouts.sistema')
@section('title', 'Aniversariantes do Mês')
@section('content_header', "Aniversariantes de ".(new \MasterTag\DataHora())->mesExt())
@section('content')
    <aniversariantes></aniversariantes>
@stop
@push('js')
    @vite(['resources/js/g/administracao/aniversariantes/app.js'])
@endpush
