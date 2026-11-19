@extends('layouts.sistema')
@section('title', 'Configuração de Aprovações Extras')
@section('content_header', 'Configuração de Aprovações Extras')
@section('content')
<aprovacao-extra-config></aprovacao-extra-config>
@stop
@push('js')
@vite(['resources/js/g/administracao/aprovacao-extra-config/app.js'])
@endpush