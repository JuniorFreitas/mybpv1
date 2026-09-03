@extends('layouts.sistema')
@section('title', 'Documentos da Pré-admissão')
@section('content_header','Documentos da Pré-admissão')
@section('content')
    <documentos-preadmissao
        :can-insert="{{ $canInsert ? 'true' : 'false' }}"
        :can-update="{{ $canUpdate ? 'true' : 'false' }}"
    ></documentos-preadmissao>
@stop
@push('js')
    <script src="{{mix('js/g/documentospreadmissao/app.js')}}"></script>
@endpush
