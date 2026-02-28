@extends('assinatura.layout')

@section('title', 'Documento já assinado')

@section('content')
    <div class="card assin-card">
        <div class="card-header">
            <h5 class="mb-0">Documento já assinado</h5>
        </div>
        <div class="card-body text-center py-5">
            <div class="assin-status-icon assin-status-info">OK</div>
            <p class="lead">Você já assinou este documento.</p>
            <div class="assin-origin mb-3 text-left">
                A empresa <strong>{{ $empresaNome ?? 'Empresa' }}</strong> enviou o documento <strong>{{ $nomeDocumento ?? 'Documento' }}</strong> para ser assinado digitalmente.
            </div>
            <p class="text-muted">Signatário: <strong>{{ $signatario->nome }}</strong> ({{ $signatario->email }})</p>
            @if($doc->status === \App\Models\DocumentoParaAssinatura::STATUS_CONCLUIDO)
                <p class="text-success mt-2">Todos os signatários já assinaram. O documento está concluído.</p>
            @endif
            @if(isset($pdfUrl))
                <p class="mt-4">
                    <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="btn btn-primary">Ver documento assinado</a>
                    <a href="{{ $pdfUrl }}" download class="btn btn-outline-primary ml-2">Baixar PDF</a>
                </p>
            @endif
        </div>
    </div>
@endsection
