@extends('layouts.pdf_filial')
@section('title','COMUNICADO AOS COLABORADORES')
@section('conteudo')
    <style>
        @page {
            margin: 10mm 2mm 8mm 2mm;
        }
    </style>
    <div style="margin-left: 9px">
        @include('layouts.cabecalioFilialEmpresaJob')
    </div>
    <div style="position: fixed; left: 20px; bottom: 0; text-align: left; width: 90%; padding-bottom: 2px;">
        @include('layouts.rodapePdfFilialJob')
    </div>
    <div style="margin-left: 2.5%; width: 93%; padding-bottom: 28px;">
        <p class="f12"
           style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase">
            <br><br>
            <strong>COMUNICADO AOS COLABORADORES</strong><br>
        </p>
        <br><br>
        <p class="f11" style="line-height: 18pt; text-align: justify">
            A empresa <strong>{{ $dados['dados_empresa']['razao_social'] ?? '' }}</strong>,
            inscrita no CNPJ sob o nº <strong>{{ $dados['dados_empresa']['cnpj'] ?? '' }}</strong>,
            informa que, conforme previsto na legislação trabalhista brasileira, os colaboradores têm direito à ausência justificada
            para realização de exames preventivos de saúde, podendo utilizar até <strong>3 (três) dias</strong>,
            dentro do período de <strong>12 meses</strong>.
        </p>

        <p class="f11" style="line-height: 18pt;">
            Para utilização do benefício, orientamos que:
        </p>
        <ul class="f11" style="line-height: 18pt; margin-top: 0;">
            <li>O gestor imediato seja comunicado com antecedência, sempre que possível;</li>
            <li>O colaborador apresente comprovante de comparecimento ou atestado emitido pela clínica/hospital;</li>
            <li>As ausências sejam destinadas exclusivamente à realização de exames preventivos de saúde.</li>
        </ul>

        <p class="f11" style="line-height: 18pt; text-align: justify">
            Essa medida reforça a importância do cuidado com a saúde e do acompanhamento médico preventivo.
        </p>

        <p class="f11" style="line-height: 18pt; text-align: justify">
            Em caso de dúvidas, o setor de Recursos Humanos permanece à disposição.
        </p>

        <br>
        <div class="f11" style="line-height: 26pt">
            São Luís - MA, {{ (new \MasterTag\DataHora())->dataCompletaExt() }}.
        </div>

        <br><br><br><br><br><br><br>
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; text-align: center; border: none; vertical-align: top;">
                    <hr style="width: 7cm; margin: 0 auto; border: none; border-top: 1px solid #333">
                    <div class="f11" style="margin-top: 4px;">
                        {{ ($dados['dados_empresa']['razao_social']) }}<br>
                        <span style="font-size: 9pt;">RECURSOS HUMANOS</span>
                    </div>
                </td>
                <td style="width: 50%; text-align: center; border: none; vertical-align: top;">
                    <hr style="width: 7cm; margin: 0 auto; border: none; border-top: 1px solid #333">
                    <div class="f11" style="margin-top: 4px;">
                        {{ $dados['dados_colaborador']->Curriculo->nome ?? '' }}<br>
                        <span style="font-size: 9pt;">COLABORADOR(A) – CIENTE</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
@stop

@push('style')
    <style type="text/css">
        .obs {
            font-size: 8.4pt;
            color: #444444;
            margin-bottom: 10px;
        }
    </style>
@endpush
