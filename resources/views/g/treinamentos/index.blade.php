@extends('layouts.sistema')
@section('title', 'Treinamento')
@section('content_header')
<h4 class="text-default">Treinamentos</h4>
<hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')

<input type="hidden" id="cliente_id" value="{{ request('cliente_id', '') }}">
<treinamentos-carteira-etiquetas></treinamentos-carteira-etiquetas>

@stop
@push('css')
<style>
    /* Estilos para status dos treinamentos */
    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }

    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }

    .border-left-danger {
        border-left: 4px solid #dc3545 !important;
    }

    .border-left-secondary {
        border-left: 4px solid #6c757d !important;
    }

    /* Backgrounds suaves para headers dos cards */
    .bg-success-light {
        background-color: rgba(40, 167, 69, 0.1) !important;
    }

    .bg-warning-light {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    .bg-danger-light {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }

    /* Estilos para botões de accordion */
    .card-header .btn-link:focus,
    .card-header .btn-link:hover {
        text-decoration: none;
        box-shadow: none;
    }

    .colab-header-meta {
        margin-top: 2px;
    }

    .colab-meta .meta-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .colab-meta .meta-icon {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        background: #f3f5f7;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 0.9rem;
        flex: 0 0 28px;
    }

    .colab-meta .meta-label {
        color: #6c757d;
        font-size: 0.75rem;
        line-height: 1.1;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .colab-meta .meta-value {
        font-weight: 600;
        color: #212529;
        line-height: 1.2;
    }

    /* Animação para ícones de expansão */
    .fa-chevron-down,
    .fa-chevron-right {
        transition: transform 0.2s ease-in-out;
    }
</style>
@endpush
@push('js')
<script src="{{mix('js/g/treinamentos/app.js')}}"></script>
@endpush
