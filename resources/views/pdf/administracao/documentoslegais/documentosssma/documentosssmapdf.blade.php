@extends('layouts.pdf')
@section('title','Documentos Legais - SSMA')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <h5 class="text-center" style="text-transform: uppercase; text-decoration: underline">DOCUMENTOS LEGAIS - SSMA</h5>
    <br><h5 style="margin-top: 5px; margin-bottom: 5px; text-decoration: underline">TIPO DE CADASTRO DOCUMENTO</h5>
    <h5>
        Tipo: <span
            style="font-weight: normal; line-height: 20px">{{ $dados->tipo_ssma == 0 ? 'Contrato' : 'SSMA' }}</span><br>

        @if($dados->tipo_ssma == 0)
            @php
                $contrato = \App\Models\DocumentoContratos::getContrato($dados->contrato_id);
//                dd($contrato)
            @endphp
            Contrato: <span style="font-weight: normal; line-height: 20px">{{ $contrato->dados_cadastrais->tipo == \App\Models\Cliente::TIPO_PESSOA_JURIDICA ? $contrato->dados_cadastrais->razao_social : $contrato->dados_cadastrais->nome }}</span><br>
        @endif
    </h5>
    <br><h5 style="margin-top: 10px; margin-bottom: 5px; text-decoration: underline">DOCUMENTOS LEGAIS</h5>
    <h5>
        @php
            $dataI = new \MasterTag\DataHora($dados->documentos_ssma->data_inicio);
            $dataF = new \MasterTag\DataHora($dados->documentos_ssma->data_encerramento);
            $totalD = \MasterTag\DataHora::diferencaDias($dados->documentos_ssma->data_inicio,$dados->documentos_ssma->data_encerramento);
        @endphp
        Período do Documento:
        <span style="font-weight: normal; line-height: 20px">
                        {{ $dataI->dataCompleta() }} à {{ $dataF->dataCompleta() }} ({{$totalD}} dias)</span>
        <br>
        Tipo de Documento:
        <span style="font-weight: normal; line-height: 20px">
                        {{ $dados->documentos_ssma->tipo_descricao }}
                    </span> <br>
        Observação:
        <span style="font-weight: normal; line-height: 20px">
                         {{ $dados->documentos_ssma->observacao }}
                    </span> <br>
        Status:
        <span style="font-weight: normal; line-height: 20px">
                         {{ $dados->documentos_ssma->status }}
                    </span> |
        Ativo:
        <span style="font-weight: normal; line-height: 20px">
                         {{ $dados->documentos_ssma->ativo == 1 ? 'Sim' : 'Não' }}
                    </span> |
        Possuí Anexo(s):
        <span style="font-weight: normal; line-height: 20px">
                         {{ count($dados->documentos_ssma->anexos) > 0 ? 'Sim' : 'Não' }}
                    </span> <br>
{{--                @if($key < $dados->ServicosCliente->count()-1)--}}
{{--                    <hr style="border-bottom: 1px dashed #ccc; border-top: none;border-right: none;border-left: none; height: 1px; margin-top: 10px; margin-bottom: 10px; ">--}}
{{--                @endif--}}
    </h5>
{{--    @endif--}}
    </h5>
    @include('layouts.rodapePdf')
@endsection
