@extends('layouts.pdf')
@section('title','AFASTAMENTO')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <h5 class="text-center">AFASTAMENTO</h5>
    <h5 style="margin-top: 5px; margin-bottom: 5px;">INFORMAÇÕES:</h5>

    <table width="100%" style="border: 1px solid #666666; padding: 8px 17px 15px">
        <tr>
            <td>
                <p style="line-height: 15pt; font-size: 10pt;">

                    Nome: <strong>{{ $afastamento->Feedback->Curriculo->nome }}</strong>
                    ({{ $afastamento->Feedback->Curriculo->idade }} anos)<br>
                    CPF: <strong>{{ $afastamento->Feedback->Curriculo->cpf }}</strong><br>
                    Vaga selecionada: <strong>{{ $afastamento->Feedback->VagaAberta->VagaSelecionada->nome }}</strong> <br>
                    Data admissão: <strong>{{ $afastamento->Feedback->Admissao->data_admissao }}</strong>
                    <br>
                    Craidor por: <strong>{{ $afastamento->Usuario->nome }}</strong><br>
                    Inicio do Afastamento:
                    <strong>{{ (new \MasterTag\DataHora($afastamento->data_inicio))->dataCompleta() }}</strong><br>
                    Fim do Afastamento:
                    <strong>{{ (new \MasterTag\DataHora($afastamento->data_fim))->dataCompleta() }}</strong><br>
                </p>
                <br>
                <br>
                <br>

                <div style="font-size: 10pt;width: 6cm; margin: 0 auto; text-align: center;border:none; border-top: 1px solid #000000; line-height: 10px">
                    <br>
                    {{ $afastamento->Usuario->nome }}
                </div>
            </td>
        </tr>
    </table>

    @include('layouts.rodapePdf')

@endsection
