@extends('layouts.sistema')
@section('title', 'Configuração de Aprovações Extras')
@section('content_header', 'Configuração de Aprovações Extras')
@section('content')
<aprovacao-extra-config></aprovacao-extra-config>
@stop
@push('js')
<script src="{{mix('js/g/aprovacao-extra-config/app.js')}}"></script>
@endpush