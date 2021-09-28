@extends('layouts.pdf')
@section('title','Ficha de Cliente')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <h5 class="text-center" style="text-transform: uppercase; text-decoration: underline">FICHA
        DE {{$dados->tipo_cliente}}</h5>
    <h5 style="margin-top: 5px; margin-bottom: 5px; text-decoration: underline">DADOS CADASTRAIS</h5>
    <h5>
        @if ($dados->tipo == \App\Models\Cliente::TIPO_PESSOA_JURIDICA)
            Razão Social: <span
                style="font-weight: normal; line-height: 20px">{{ $dados->razao_social }}</span> <br>
            Nome Fantasia: <span
                style="font-weight: normal; line-height: 20px">{{ $dados->razao_social }}</span> <br>
            CNPJ: <span style="font-weight: normal; line-height: 20px">{{ $dados->cnpj }}</span> <br>
        @else
            Nome: <span style="font-weight: normal; line-height: 20px">{{ $dados->nome }}</span> <br>
            CPF: <span style="font-weight: normal; line-height: 20px">{{ $dados->cpf }}</span> <br>
        @endif
        Área de Atuação: <span
            style="font-weight: normal; line-height: 20px">{{ $dados->Area->label }}</span> |
        Ramo: <span style="font-weight: normal; line-height: 20px">{{ $dados->ramo }}</span><br>
        Endereço: <span
            style="font-weight: normal; line-height: 20px">{{ $dados->endereco_completo }}</span><br>
    </h5>

    <h5 style="margin-top: 10px; margin-bottom: 5px; text-decoration: underline">CONTATOS</h5>

    <h5>
        Nome do Responsável:
        <span style="font-weight: normal; line-height: 20px">
                        {{ $dados->contato }}</span> <br>
        Aniversário: <span style="font-weight: normal; line-height: 20px">
                        {{ (new \MasterTag\DataHora($dados->aniversario))->dataCompletaExt() }}</span> <br>
        E-mail: <span style="font-weight: normal; line-height: 20px">
                        {{ $dados->email }}</span> <br>

        @foreach($dados->Telefones as $tel)
            Telefone: <span style="font-weight: normal; line-height: 20px">
                        {{ $tel->numero }} ({{ $tel->tipo }})</span> <br>
        @endforeach

    </h5>

    @if ($dados->tipo_cliente == 'Cliente')
        <h5 style="margin-top: 10px; margin-bottom: 5px; text-decoration: underline">SERVIÇOS</h5>
        <h5>
            @foreach($dados->ServicosCliente as $key => $item)
                @php
                    $dataI = new \MasterTag\DataHora($item->data_inicio);
                    $dataF = new \MasterTag\DataHora($item->data_encerramento);
                    $totalD = \MasterTag\DataHora::distanciaTempo($item->data_inicio,$item->data_encerramento);
                @endphp
                Período do Contrato:
                <span style="font-weight: normal; line-height: 20px">
                                {{ $dataI->dataCompleta() }} à {{ $dataF->dataCompleta() }} ({{$totalD['mes']}} meses)</span>
                <br>
                Tipo de Serviço:
                <span style="font-weight: normal; line-height: 20px">
                                {{ $item->Servico->titulo }}
                            </span> <br>
                Valor:
                <span style="font-weight: normal; line-height: 20px">
                                R$ {{ $item->valor }}
                            </span> <br>
                Tipo do Faturamento:
                <span style="font-weight: normal; line-height: 20px">
                                 {{ $item->tipo_faturamento }}
                            </span> <br>
                Escopo:
                <span style="font-weight: normal; line-height: 20px">
                                 {{ $item->escopo }}
                            </span> <br>
                Status:
                <span style="font-weight: normal; line-height: 20px">
                                 {{ $item->status }}
                            </span> |
                Ativo:
                <span style="font-weight: normal; line-height: 20px">
                                 {{ $item->ativo == 1 ? 'Sim' : 'Não' }}
                            </span> |
                Possuí Anexo(s):
                <span style="font-weight: normal; line-height: 20px">
                                 {{ $item->Anexos()->count() > 0 ? 'Sim' : 'Não' }}
                            </span> <br>
                @if($key < $dados->ServicosCliente->count()-1)
                    <hr style="border-bottom: 1px dashed #ccc; border-top: none;border-right: none;border-left: none; height: 1px; margin-top: 10px; margin-bottom: 10px; ">
                @endif
            @endforeach
        </h5>
    @endif

    <h5 style="margin-top: 10px; margin-bottom: 5px; text-decoration: underline">PROPOSTAS</h5>
    <h5>
        @foreach($dados->ServicosProspect as $key => $item)
            @php
                $dataI = new \MasterTag\DataHora($item->data_envio_proposta);
            @endphp
            Envio da Proposta:
            <span style="font-weight: normal; line-height: 20px">
                                {{ $dataI->dataCompleta() }}</span>
            <br>
            Tipo de Serviço:
            <span style="font-weight: normal; line-height: 20px">
                                {{ $item->Servico->titulo }}
                            </span> <br>
            Escopo:
            <span style="font-weight: normal; line-height: 20px">
                                 {{ $item->escopo }}
                            </span> <br>
            Status:
            <span style="font-weight: normal; line-height: 20px">
                                 {{ $item->status }}
                            </span> |
            Possuí Anexo(s):
            <span style="font-weight: normal; line-height: 20px">
                                 {{ $item->Anexos()->count() > 0 ? 'Sim' : 'Não' }}
                            </span> <br>
            @if($key < $dados->ServicosProspect->count()-1)
                <hr style="border-bottom: 1px dashed #ccc; border-top: none;border-right: none;border-left: none; height: 1px; margin-top: 10px; margin-bottom: 10px; ">
            @endif
        @endforeach
    </h5>

    <h5 style="margin-top: 10px; margin-bottom: 5px; text-decoration: underline">COMO CONHECEU A BPSE?</h5>
    <h5>
        {{ $dados->como_conheceu }}
        @if ($dados->como_conheceu == 'OUTROS')
            <br>{{ $dados->como_conheceu_outro }}
        @endif
    </h5>

@endsection
