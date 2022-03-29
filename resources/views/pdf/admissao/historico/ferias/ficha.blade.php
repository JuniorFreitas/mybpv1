@extends('layouts.pdf')
@section('title','FÉRIAS')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <h5 class="text-center">FÉRIAS</h5>
    <h5 style="margin-top: 5px; margin-bottom: 5px;">INFORMAÇÕES:</h5>

    <table width="100%" style="border: 1px solid #666666; padding: 8px 17px 15px">
        <tr>
            <td>
                <p style="line-height: 15pt; font-size: 10pt;">

                    Nome: <strong>{{ $ferias->Feedback->Curriculo->nome }}</strong>
                    ({{ $ferias->Feedback->Curriculo->idade }} anos)<br>
                    CPF: <strong>{{ $ferias->Feedback->Curriculo->cpf }}</strong><br>
                    Vaga selecionada: <strong>{{ $ferias->Feedback->VagaAberta->VagaSelecionada->nome  . ' - ' . $ferias->Feedback->VagaAberta->Municipio->uf }}</strong> <br>
                    Data admissão: <strong>{{ $ferias->Feedback->Admissao->data_admissao }}</strong>
                    <br>
                    Craidor por: <strong>{{ $ferias->Usuario->nome }}</strong><br>
                    Inicio da Férias:
                    <strong>{{ (new \MasterTag\DataHora($ferias->data_inicio))->dataCompleta() }}</strong><br>
                    Fim da Férias:
                    <strong>{{ (new \MasterTag\DataHora($ferias->data_fim))->dataCompleta() }}</strong><br>
                </p>
                <br>
                <br>
                <br>

                <div style="font-size: 10pt;width: 6cm; margin: 0 auto; text-align: center;border:none; border-top: 1px solid #000000; line-height: 10px">
                    <br>
                    {{ $ferias->Usuario->nome }}
                </div>
            </td>
        </tr>
    </table>

    <br>
    <br>
    <p style="font-size: 9pt; color: #666666">Data da Emissão: {{ (new \MasterTag\DataHora())->dataCompleta()}}
        às {{ (new \MasterTag\DataHora())->horaCompleta()}}</p>
    <p style="font-size: 9pt; color: #666666">Emitido por: {{ \Illuminate\Support\Facades\Auth::user()->nome }}</p>

@endsection
