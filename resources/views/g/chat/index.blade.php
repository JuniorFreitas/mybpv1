@extends('layouts.sistema')
@section('content_header', 'Chat')

@section('breadcrumb')
    <li class="breadcrumb-item active">Chat</li>
@endsection
@section('content')

    <chat :id="{{auth()->user()->empresa_id}}"></chat>

@stop
@push('js')
    <script src="{{mix('js/g/chat/app.js')}}"></script>
@endpush
