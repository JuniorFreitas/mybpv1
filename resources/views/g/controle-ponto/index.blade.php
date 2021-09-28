@extends('layouts.sistema')
@section('title', 'Controle de ponto')
@section('content_header', 'Controle de ponto')
@section('content')
    <div class="row camera" >
        <div class="col-12" id="areaVideo">
            <div v-show="achou==null && cameraSelecionada">
                <i class="fas fa-spinner fa-spin"></i> Carregando módulos
            </div>
            <div class="alert " role="alert" :class="{'alert-success':achou,'alert-danger':!achou}" v-show="achou!=null">
                <span v-show="achou">Felipe encontrado (@{{precisao}} %)</span>
                <span v-show="!achou">Felipe não encontrado !!!</span>
            </div>
            <video playsinline autoplay id="video" width="720" height="560"></video>
        </div>
        <select name="" @change="mudaCamera" v-model="cameraSelecionada">
            <option :value="null">Selecione...</option>
            <option :value="camera.deviceId" v-for="camera in listaCameras">@{{ camera.label }} (@{{ camera.kind }} - @{{ camera.deviceId }})</option>
        </select>

    </div>
@stop
@push('js')
<!--    <script src="https://webrtc.github.io/adapter/adapter-latest.js"></script>-->
    <script src="{{mix('js/g/controle-ponto/camera/adapter-latest.js')}}"></script>
    <script src="{{mix('js/g/controle-ponto/camera/face-api.min.js')}}"></script>
    <script src="{{mix('js/g/controle-ponto/camera/app.js')}}"></script>

@endpush

@push('css')
    <style type="text/css">
        canvas{
            position: absolute;
            border: 1px solid #dd0000;
            /*background-color: #dd0000;*/
        }
    </style>
@endpush
