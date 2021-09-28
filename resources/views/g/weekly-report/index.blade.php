@extends('layouts.sistema')
@section('content_header', 'Weekly report')

@section('breadcrumb')
    <li class="breadcrumb-item active">Weekly report</li>
@endsection
@section('content')

    <weekly-report :id="{{auth()->user()->empresa_id}}"></weekly-report>

@stop
@push('js')
    <script src="{{mix('js/g/weekly-report/app.js')}}"></script>
@endpush
