@extends('assinatura.layout')

@section('title', 'Documento concluído')

@section('content')
<div class="card assin-card">
    <div class="card-header">
        <h5 class="mb-0">Documento concluído</h5>
    </div>
    <div class="card-body text-center py-5">
        <div class="assin-status-icon assin-status-ok">OK</div>
        <p class="lead">Este documento foi assinado por todos os signatários e está concluído.</p>

        <p class="text-muted">
            Signatário: <strong>{{ $signatario->nome }}</strong> ({{ $signatario->email }})
            @if($signatario->cpf)
            @php
            $cpf = preg_replace('/\D/', '', $signatario->cpf);
            $cpfFormatado = strlen($cpf) === 11 ? substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2) : $signatario->cpf;
            @endphp
            — CPF: {{ $cpfFormatado }}
            @endif
        </p>
        @if(isset($pdfUrl))
        <p class="mt-4">
            <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="btn btn-primary">Ver documento assinado</a>
            <a href="{{ $pdfUrl }}" download class="btn btn-outline-primary ml-2">Baixar PDF</a>
        </p>
        @endif
    </div>
</div>
@endsection