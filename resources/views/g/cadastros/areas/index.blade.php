@extends('layouts.sistema')
@section('title', 'Áreas')
@section('content_header','Áreas')
@section('content')
<areas></areas>
@stop
@push('js')
@vite(['resources/js/g/cadastros/areas/app.js'])
@endpush
