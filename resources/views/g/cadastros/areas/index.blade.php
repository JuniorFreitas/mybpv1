@extends('layouts.sistema')
@section('title', 'Áreas')
@section('content_header','Áreas')
@section('content')
<areas></areas>
@stop
@push('js')
<script src="{{mix('js/g/areas/app.js')}}"></script>
@endpush
