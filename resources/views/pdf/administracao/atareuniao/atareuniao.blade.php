@extends('layouts.pdf')
@section('title','ATA DE REUNIÃO')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <h5 class="text-center text-uppercase" style="margin-top: 30px">ATA DE REUNIÃO<br>
    </h5><br>

{{--    <table style="border: 1px solid #666666; padding: 8px 17px 15px; text-align: center">--}}
{{--        <tr>--}}
{{--            <td>--}}
                <p style="line-height: 17pt; font-size: 9.5pt; text-align: center">
                    Local: <strong>{{ $atareuniao->local }}  |</strong>
                    Data Inicio: <strong>{{ $atareuniao->data_inicio }}  |</strong>
                    Data Fim: <strong>{{ $atareuniao->data_fim }}  |</strong>
                    Quem Cadastrou: <strong>{{ $atareuniao->QuemCadastrou->nome }}</strong>
                </p>
{{--            </td>--}}
{{--        </tr>--}}
{{--    </table>--}}

    <table width="100%" border="0" class="tabela" style="margin-top: 30px">

        <tr class="topo">
            <td class="text-center">Assuntos Tratados (Pautas)</td>
        </tr>
        @foreach($atareuniao['Assuntos'] as $a)
            <tr class="linha">
                <td class="text-left">{{ $a->assunto }}</td>
            </tr>
        @endforeach

    </table>
    <br>
    <br>

    <table width="100%" border="0" class="tabela" style="margin-top: 30px">

        <tr class="topo">
            <td class="text-center">Comentários / Assuntos Pendentes / Próxima Reunião</td>
        </tr>
        @foreach($atareuniao['Tipos'] as $t)
            <tr class="linha">
                <td class="text-left">
                    @if($t->tipo == 'comentarios')Comentário:
                    @elseif($t->tipo == 'assuntos_pendentes')Assunto Pendente:
                    @else Próxima Reunião:
                    @endif{{ $t->observacao }}</td>
            </tr>
        @endforeach
    </table>
    <br>
    <br>

    <h5 class="text-center" style="margin-top: 30px">AÇÕES - PRÓXIMOS PASSOS
    </h5>
    <table width="100%" border="0" class="tabela" style="margin-top: 30px">

        <tr class="topo">
            <td class="text-center">Responsável</td>
            <td class="text-center">Ações</td>
            <td class="text-center">Prazo</td>
            <td class="text-center">Status</td>
        </tr>
        @foreach($atareuniao['Acoes'] as $ac)
            <tr class="linha">
                <td class="text-center">{{ $ac->responsavel }}</td>
                <td class="text-center">{{ $ac->acao }}</td>
                <td class="text-center">
                    @if($ac->prazo){{$ac->prazo}}
                    @elseif($ac->continuo == true)Contínuo
                @endif
                <td class="text-center">
                    @if($ac->status == 'andamento')Andamento
                    @elseif($ac->status == 'concluido')Concluído
                    @else Pendente
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
    <br>
    <br>


    <h5 class="text-center" style="margin-top: 30px">PARTICIPANTES
    </h5>
    <table width="100%" border="0" class="tabela" style="margin-top: 30px">

        <tr class="topo">
            <td class="text-center">Nome</td>
            <td class="text-center">Função</td>
            <td class="text-center">Assinatura</td>
        </tr>
        @foreach($atareuniao['Participantes'] as $p)
            <tr class="linha">
                <td class="text-center">{{ $p->nome }}</td>
                <td class="text-center">{{ $p->funcao }}</td>
                <td class="text-center"></td>
            </tr>
        @endforeach
    </table>
    <br>
    <br>

    @include('layouts.rodapePdf')

@endsection

@push('style')
    <style type="text/css">
        .tabela {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 9pt;
            border-collapse: collapse;
        }

        tr.topo td {
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            text-transform: uppercase;
            font-family: Helvetica, Arial, sans-serif;
            color: #000;
            padding: 3px;
            background-color: #ccc;

        }

        tr.linha {
            color: #000;
            background-color: #F0F0F0;
        }

        tr.linha td {
            border-bottom: 1px solid #acacac;
            padding: 4px;
        }

        .proximaPagina {
            page-break-before: always;
        }
    </style>
@endpush
