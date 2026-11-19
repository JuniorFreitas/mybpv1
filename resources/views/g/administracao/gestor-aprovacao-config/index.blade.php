@extends('layouts.sistema')
@section('title', 'Configuração de Gestor Aprovação')
@section('content_header', 'Configuração de Gestor Aprovação')
@section('content')
<gestor-aprovacao-config></gestor-aprovacao-config>
@stop
@push('js')
@vite(['resources/js/g/administracao/gestor-aprovacao-config/app.js'])
@endpush
