@extends('layouts.pdf')
@section('title','Formulário Avaliação 90 Dias')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <h5 class="text-center">RESULTADO AVALIAÇÃO ANUAL</h5>
    <h5 style="margin-top: 5px; margin-bottom: 5px;">INFORMAÇÕES:</h5>

    <table width="100%" style="border: 1px solid #666666; padding: 8px 17px 15px">
        <tr>
            <td>
                <p style="line-height: 15pt; font-size: 10pt;">

                    Nome: <strong>{{ $informacoes->Feedback->Curriculo->nome }}</strong>
                    ({{ $informacoes->Feedback->Curriculo->idade }} anos)<br>
                    CPF: <strong>{{ $informacoes->Feedback->Curriculo->cpf }}</strong><br>
                    Vaga selecionada: <strong>{{ $informacoes->Feedback->VagaAberta->VagaSelecionada->nome  . ' - ' . $informacoes->Feedback->VagaAberta->Municipio->uf }}</strong> <br>
                    Data admissão: <strong>{{ $informacoes->Feedback->Admissao->data_admissao }}</strong>
                    <br>
                    Avaliado por: <strong>{{ $informacoes->Usuario->nome }}</strong>
                    Gestor imediato: <strong>{{ $informacoes->gestor_imediato }}</strong> <br>
                    Data da avaliação:
                    <strong>{{ (new \MasterTag\DataHora($informacoes->created_at))->dataCompleta() }}</strong>
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" style="border: 1px solid #666666;padding: 8px 17px 15px; border-top: none">
            @foreach($avaliacao as $index=>$a)
                <tr style="margin-bottom: 30px">
                    <td>
                        <p style="line-height: 15pt; font-size: 10pt;margin-bottom: 5px; text-align: justify">
                            {{$index+1}}ª) {!! $a->Pergunta->pergunta !!}
                        </p>
                    </td>
                </tr>
                <tr style="margin-bottom: 30px">
                    <td>
                        <p style=" font-size: 10pt; margin-bottom: 5px">
                            <strong>NOTA: {{ $a->nota}}</strong>
                        </p>
                    </td>
                </tr>
            @endforeach
    </table>

    <br>
    <br>
    <p style="font-size: 9pt; color: #666666">Data da Emissão: {{ (new \MasterTag\DataHora())->dataCompleta()}}
        às {{ (new \MasterTag\DataHora())->horaCompleta()}}</p>
    <p style="font-size: 9pt; color: #666666">Emitido por: {{ \Illuminate\Support\Facades\Auth::user()->nome }}</p>

@endsection
