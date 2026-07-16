@extends('layouts.pdf')
@section('title',$dados->motivoRescisao->descricao)
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')

    <p class="f14"
       style="text-align: center; font-weight: bold; margin-bottom: 1.5cm; text-transform: uppercase">
        COMUNICADO DE RESCISÃO CONTRATUAL<br>
        {{ $dados->motivoRescisao->descricao }}
    </p>
    <p class="f12">
        A(o)<br>
        Sr(a). <strong>{{ $dados->Feedback->Curriculo->nome }}</strong>
    </p>
    <br>
    <p class="f12" style="line-height: 22pt; text-align: justify">
        Pela presente, comunicamos que a partir do
        dia {{ (new \MasterTag\DataHora($dados->data_desmobilizacao))->dataCompleta() }},
        fica rescindido o contrato de trabalho firmado com a empresa
        <strong>{{ $dados->Feedback->Empresa->razao_social }}</strong>.
    </p>
    <br>
    <p class="f12" style="line-height: 22pt; text-align: justify">
        <strong>Motivo da rescisão:</strong>
        {{ $dados->outro_motivo ?: ($dados->comentario ?: $dados->motivoRescisao->descricao) }}
    </p>
    @if($dados->tipoAviso)
        <br>
        <p class="f12" style="line-height: 22pt; text-align: justify">
            O aviso prévio será <strong>{{ strtolower($dados->tipoAviso->descricao) }}</strong>.
        </p>
    @endif
    @if($dados->solicitado_por)
        <br>
        <p class="f12" style="line-height: 22pt; text-align: justify">
            Solicitado por: <strong>{{ $dados->solicitado_por }}</strong>
        </p>
    @endif
    <br><br>
    <p class="f12" style="line-height: 22pt; text-align: justify">
        Solicitamos o seu comparecimento ao setor de Recursos Humanos para as devidas providências
        e quitação das parcelas a que fizer jus, conforme a legislação vigente.
    </p>
    <br>
    <div class="f12" style="line-height: 26pt;text-align: center">
        São Luís-MA, {{ (new \MasterTag\DataHora($dados->data_desmobilizacao))->dataCompletaExt() }}
        <br>
        <br>
        <br>
        <hr style="width: 10cm; margin-left: 24%; border:none; border-top: 1px solid #333">
        {{ $dados->Feedback->Empresa->razao_social }}
        <br><br>
        <hr style="width: 10cm; margin-top: 5px; margin-left: 24%; border:none; border-top: 1px solid #333">
        {{ $dados->Feedback->Curriculo->nome }}
    </div>

    @include('layouts.rodapePdf')
@endsection

@push('style')
    <style type="text/css">
        .footer {
            position: absolute;
            bottom: 0px;
            font-size: 8.4pt;
        }

        .f14 {
            font-size: 14pt;
        }

        .f12 {
            font-size: 12pt;
        }

        .obs {
            font-size: 8.4pt;
            color: #444444;
            margin-bottom: 10px;
        }
    </style>
@endpush
