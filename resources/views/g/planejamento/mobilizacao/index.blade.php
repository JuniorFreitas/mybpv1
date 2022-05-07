@extends('layouts.sistema')
@section('title', 'Mobilização')
@section('content_header','Mobilização')
@section('content')
    <mobilizacao></mobilizacao>
@stop
@push('js')
    <script src="{{mix('js/g/planejamento/mobilizacao/app.js')}}"></script>
@endpush
