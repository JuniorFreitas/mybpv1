@extends('layouts.pdf')
@section('title',$dados['projeto']->nome)
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <div class="center">
        <h3 style="text-transform: uppercase">Projeto: {{ $dados['projeto']->nome }}</h3>
        <h5 style="margin-bottom: -8px">{{ $dados['projeto']->preenchidas }} preenchida(s)
            de {{ $dados['projeto']->qnt_total }} vaga(s)</h5>
    </div>
    <br>
    @foreach($dados['vagas_projeto'] as $item)
        <div class="espaco border-bottom">
            <h4>{{ $item->VagaAberta->titulo }}</h4>
            <h5 style="font-weight: normal">({{ $item->VagaAberta->Vaga->nome }})</h5>
            <br>
            <table style="width: 100%">
                <tr>
                    <td style="width: 50%; font-size: 10pt">
                        Total: <strong>{{  $item->qnt_total }}</strong><br>
                        Preenchidas: <strong>{{  $item->qnt_preenchida }}</strong><br>
                        Em Processo de Seleção: <strong>{{  $item->em_processo_selecao }}</strong><br>
                        Treinamento Fase 1: <strong>{{  $item->treinamento_fase_1 }}</strong><br>
                        Pendente Treinamento: <strong>{{  $item->status_pendente_treinamento }}</strong><br>
                        Exames: <strong>{{  $item->status_encaminhado_exame }}</strong><br>
                        Portaria: <strong>{{  $item->documento_portaria }}</strong><br>
                        Aguardando Qualificação: <strong>{{  $item->status_aguardando_qualificacao }}</strong><br>
                        ASO no Ambulatório: <strong>{{  $item->status_aso_no_ambulatorio }}</strong><br>
                        Cancelados: <strong>{{  $item->status_cancelado }}</strong><br>
                        Desistências: <strong>{{  $item->status_desistencia }}</strong><br>
                    </td>
                    <td style="width: 50%; font-size: 10pt">
                        Encaminhado para Exames: <strong>{{ $item->status_encaminhado_exame }}</strong><br/>
                        Pendente ASO: <strong>{{ $item->status_pendente_aso }}</strong><br/>
                        Pendente Documentos: <strong>{{ $item->status_pendente_documento }}</strong><br/>
                        Pronto para admissão: <strong>{{ $item->status_pronto_para_admissao }}</strong><br/>
                        Admitidos: <strong>{{ $item->status_admitido }}</strong><br/>
                        Entregues na área: <strong>{{ $item->entregue_area }}</strong><br/>
                        StandBy: <strong>{{ $item->status_standby }}</strong><br/>
                        Admissão tipo determinada: <strong>{{ $item->tipo_admissao_determinado }}</strong><br/>
                        Admissão tipo fixo: <strong>{{ $item->tipo_admissao_fixo }}</strong><br/>
                        Admissão tipo intermitente: <strong>{{ $item->tipo_admissao_intermitente }}</strong><br/>
                        Admissão tipo temporária: <strong>{{ $item->tipo_admissao_temporario }}</strong><br/>
                    </td>
                </tr>
            </table>
        </div>
    @endforeach
    <div class="espaco border-bottom">
        <h4>RESUMO GERAL MOBILIZAÇÃO</h4>
        <br>
        <table style="width: 100%">
            <tr>
                <td style="width: 50%; font-size: 10pt">
                    Total de currículos cadastrados:<strong>{{ $dados['total_geral_curriculos'] }}</strong><br />
                    Total de mobilizados:<strong>{{ $dados['total_geral_curriculos_feedbacks'] }}</strong><br />
                    Total de currículos
                    selecionados:<strong>{{ $dados['total_geral_curriculos_selecionados'] }}</strong><br />
                    Total de currículos standby:<strong>{{ $dados['total_geral_curriculos_standby'] }}</strong><br />
                    Total de currículos em Parecer RH:<strong>{{ $dados['total_em_parecer_rh'] }}</strong><br />
                </td>
                <td style="width: 50%; font-size: 10pt">
                    Total de currículos em Parecer Rota -
                    Transporte:<strong>{{ $dados['total_em_parecer_rota'] }}</strong><br />
                    Total de currículos em Parecer Entrevista
                    Técnica:<strong>{{ $dados['total_em_parecer_tecnica'] }}</strong><br />
                    Total de currículos em Parecer Teste Prático:<strong>{{ $dados['total_em_parecer_teste'] }}</strong><br />
                    Total de currículos em Resultado
                    Integrado:<strong>{{ $dados['total_em_resultado_integrado'] }}</strong><br />
                </td>
            </tr>
        </table>
    </div>
    @include('layouts.rodapePdf')
@endsection
@push('style')
    <style type="text/css">
        .espaco {
            padding: 20px 20px;
        }

        .border-bottom {
            border-bottom: 1px solid #ccc;
        }

        .center {
            text-align: center;
        }

        .coluna {
            width: 50%;
            float: left;
        }

        .resetFloat {
            clear: both;
        }

        .text-left {
            text-align: left;
        }

        .footer {
            position: absolute;
            bottom: 0px;
            font-size: 8.4pt;
            /*width: 10cm;*/
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
