@extends('layouts.sistema')
@section('title', 'Notificações')
@section('content_header', 'Notificações')
@section('content')
<notificacoes-config></notificacoes-config>
@stop
@push('js')
@vite(['resources/js/g/configuracoes/notificacoes/app.js'])
@endpush
