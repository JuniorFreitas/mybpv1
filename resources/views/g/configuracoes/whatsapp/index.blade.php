@extends('layouts.sistema')
@section('title', 'Configurações WhatsApp')
@section('content_header', 'Configurações WhatsApp')
@section('content')
<whatsapp-config></whatsapp-config>
@stop
@push('js')
<script src="{{mix('js/g/configuracoes/whatsapp/app.js')}}"></script>
@endpush
