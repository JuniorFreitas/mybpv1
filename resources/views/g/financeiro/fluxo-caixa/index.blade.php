@extends('layouts.sistema')
@section('content_header', 'Fluxo de caixa')

@section('breadcrumb')
    <li class="breadcrumb-item active">Financeiro - Fluxo de caixa</li>
@endsection
@section('content')

    <fluxo-caixa :id="{{auth()->user()->empresa_id}}"></fluxo-caixa>

@stop
@push('js')
    <script src="{{mix('js/g/fluxo-caixa/app.js')}}"></script>
@endpush
