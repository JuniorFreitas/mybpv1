@extends('layouts.pdf')
@section('title','Documentos Legais - Empresa')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <h5 class="text-center" style="text-transform: uppercase; text-decoration: underline">DOCUMENTOS LEGAIS - EMPRESA</h5>
    <br><h5 style="margin-top: 5px; margin-bottom: 5px; text-decoration: underline">TIPO DE CADASTRO DOCUMENTO</h5>
    <h5>
        Tipo: <span
            style="font-weight: normal; line-height: 20px">{{ $dados->tipo_empresa == 0 ? 'Contrato' : 'Empresa' }}</span><br>

        @if($dados->tipo_empresa == 0)
            @php
                $contrato = \App\Models\DocumentoContratos::getContrato($dados->contrato_id);
            @endphp
            Contrato: <span style="font-weight: normal; line-height: 20px">{{ $contrato->dados_cadastrais->tipo == \App\Models\Cliente::TIPO_PESSOA_JURIDICA ? $contrato->dados_cadastrais->razao_social : $contrato->dados_cadastrais->nome }}</span><br>
        @endif
    </h5>
    <br><h5 style="margin-top: 10px; margin-bottom: 5px; text-decoration: underline">DOCUMENTOS LEGAIS</h5>
    <h5>
        @php
            $dataI = new \MasterTag\DataHora($dados->documentos_empresa->data_inicio);
            $dataF = new \MasterTag\DataHora($dados->documentos_empresa->data_encerramento);
            $totalD = \MasterTag\DataHora::diferencaDias($dados->documentos_empresa->data_inicio,$dados->documentos_empresa->data_encerramento);
        @endphp
        Período do Documento:
        <span style="font-weight: normal; line-height: 20px">
                        {{ $dataI->dataCompleta() }} à {{ $dataF->dataCompleta() }} ({{$totalD}} dias)</span>
        <br>
        Tipo de Documento:
        <span style="font-weight: normal; line-height: 20px">
                        {{ $dados->documentos_empresa->tipo_descricao }}
                    </span> <br>
        Observação:
        <span style="font-weight: normal; line-height: 20px">
                         {{ $dados->documentos_empresa->observacao }}
                    </span> <br>
        Status:
        <span style="font-weight: normal; line-height: 20px">
                         {{ $dados->documentos_empresa->status }}
                    </span> |
        Ativo:
        <span style="font-weight: normal; line-height: 20px">
                         {{ $dados->documentos_empresa->ativo == 1 ? 'Sim' : 'Não' }}
                    </span> |
        Possuí Anexo(s):
        <span style="font-weight: normal; line-height: 20px">
                         {{ count($dados->documentos_empresa->anexos) > 0 ? 'Sim' : 'Não' }}
                    </span> <br>
{{--                @if($key < $dados->ServicosCliente->count()-1)--}}
{{--                    <hr style="border-bottom: 1px dashed #ccc; border-top: none;border-right: none;border-left: none; height: 1px; margin-top: 10px; margin-bottom: 10px; ">--}}
{{--                @endif--}}
    </h5>
{{--    @endif--}}
    </h5>
    @include('layouts.rodapePdf')
@endsection
