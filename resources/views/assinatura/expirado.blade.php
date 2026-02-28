@extends('assinatura.layout')

@section('title', 'Prazo expirado')

@section('content')
    <div class="card assin-card">
        <div class="card-header">
            <h5 class="mb-0">Prazo expirado</h5>
        </div>
        <div class="card-body text-center py-5">
            <div class="assin-status-icon assin-status-warn">!</div>
            <p class="lead">O prazo para assinatura deste documento expirou.</p>
            <div class="assin-origin mb-3 text-left">
                A empresa <strong>{{ $empresaNome ?? 'Empresa' }}</strong> enviou o documento <strong>{{ $nomeDocumento ?? 'Documento' }}</strong> para ser assinado digitalmente.
            </div>
            <p class="text-muted">Entre em contato com o solicitante se precisar de um novo envio.</p>
        </div>
    </div>
@endsection
