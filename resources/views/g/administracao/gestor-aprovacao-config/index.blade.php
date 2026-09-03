@extends('layouts.sistema')
@section('title', 'Configuração de Gestor Aprovação')
@section('content_header', 'Configuração de Gestor Aprovação')
@section('content')
<gestor-aprovacao-config></gestor-aprovacao-config>
@stop
@push('js')
<script src="{{mix('js/g/gestor-aprovacao-config/app.js')}}"></script>
@endpush
