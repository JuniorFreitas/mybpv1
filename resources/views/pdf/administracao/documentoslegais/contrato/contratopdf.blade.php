@extends('layouts.pdf')
@section('title','Contrato')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <h5 class="text-center" style="text-transform: uppercase; text-decoration: underline">CONTRATO {{$dados->dados_cadastrais->tipo}}</h5>
    <br><h5 style="margin-top: 5px; margin-bottom: 5px; text-decoration: underline">DADOS CADASTRAIS</h5>
    <h5>
        @if ($dados->dados_cadastrais->tipo == \App\Models\Cliente::TIPO_PESSOA_JURIDICA)
            Razão Social: <span
                style="font-weight: normal; line-height: 20px">{{ $dados->dados_cadastrais->razao_social }}</span> <br>
            Nome Fantasia: <span
                style="font-weight: normal; line-height: 20px">{{ $dados->dados_cadastrais->razao_social }}</span> <br>
            CNPJ: <span style="font-weight: normal; line-height: 20px">{{ $dados->dados_cadastrais->cnpj }}</span> <br>
        @else
            Nome: <span style="font-weight: normal; line-height: 20px">{{ $dados->dados_cadastrais->nome }}</span> <br>
            CPF: <span style="font-weight: normal; line-height: 20px">{{ $dados->dados_cadastrais->cpf }}</span> <br>
        @endif
        Área de Atuação: <span
            style="font-weight: normal; line-height: 20px">{{ \App\Models\Area::getArea($dados->dados_cadastrais->area_id)->label }}</span> |
        Ramo: <span style="font-weight: normal; line-height: 20px">{{ $dados->dados_cadastrais->ramo }}</span><br>
        @php
            $endereco = [];
            $endereco['logradouro'] = $dados->dados_cadastrais->logradouro;
            $endereco['complemento'] = $dados->dados_cadastrais->complemento;
            $endereco['numero'] = $dados->dados_cadastrais->numero;
            $endereco['bairro'] = $dados->dados_cadastrais->bairro;
            $endereco['cep'] = $dados->dados_cadastrais->cep;
            $endereco['municipio'] = $dados->dados_cadastrais->municipio;
            $endereco['uf'] = $dados->dados_cadastrais->uf;

//            $endereco = json_encode($endereco, JSON_PRETTY_PRINT);
            $endereco = (object) $endereco;
        @endphp
            Endereço: <span
            style="font-weight: normal; line-height: 20px">{{ \App\Models\DocumentoContratos::getEnderecoCompleto($endereco) }}</span><br>
    </h5>

    <br><h5 style="margin-top: 10px; margin-bottom: 5px; text-decoration: underline">CONTATOS</h5>

    <h5>
        Nome do Responsável:
        <span style="font-weight: normal; line-height: 20px">
                        {{ $dados->dados_cadastrais->responsavel }}</span> <br>
{{--        Aniversário: <span style="font-weight: normal; line-height: 20px">--}}
{{--                        {{ (new \MasterTag\DataHora($dados->aniversario))->dataCompletaExt() }}</span> <br>--}}
        E-mail: <span style="font-weight: normal; line-height: 20px">
                        {{ $dados->dados_cadastrais->email }}</span> <br>

        @foreach($dados->dados_cadastrais->telefones as $tel)
            Telefone: <span style="font-weight: normal; line-height: 20px">
                        {{ $tel->numero }} ({{ $tel->tipo }})</span> <br>
        @endforeach
    </h5>

    <h5>
        Fez upload da Logo:
        <span style="font-weight: normal; line-height: 20px">{{ count($dados->dados_cadastrais->logo) > 0 ? 'Sim' : 'Não' }}</span>
    </h5>

{{--    @if ($dados->tipo_cliente == 'Cliente')--}}
        <br><h5 style="margin-top: 10px; margin-bottom: 5px; text-decoration: underline">SERVIÇOS</h5>
        <h5>
            @foreach($dados->dados_cadastrais->servicos_contrato as $key => $item)
                @php
                    $dataI = new \MasterTag\DataHora($item->data_inicio);
                    $dataF = new \MasterTag\DataHora($item->data_encerramento);
                    $totalD = \MasterTag\DataHora::diferencaDias($item->data_inicio,$item->data_encerramento);
                @endphp
                Período do Contrato:
                <span style="font-weight: normal; line-height: 20px">
                                {{ $dataI->dataCompleta() }} à {{ $dataF->dataCompleta() }} ({{$totalD}} dias)</span>
                <br>
                Tipo de Serviço:
                <span style="font-weight: normal; line-height: 20px">
                                {{ \App\Models\Servico::getTipoServico($item->select_tipo_servico)->titulo }}
                            </span> <br>
                Valor:
                <span style="font-weight: normal; line-height: 20px">
                                R$ {{ $item->valor }}
                            </span> <br>
                Tipo do Faturamento:
                <span style="font-weight: normal; line-height: 20px">
                                 {{ $item->tipo_faturamento }}
                            </span> <br>
                Tipo de Contrato:
                <span style="font-weight: normal; line-height: 20px">
                                 {{ \App\Models\FormaContrato::getFormaContrato($item->tipo_contrato)->titulo }}
                            </span> <br>
                Observação:
                <span style="font-weight: normal; line-height: 20px">
                                 {{ $item->observacao }}
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
                                 {{ count($item->anexos) > 0 ? 'Sim' : 'Não' }}
                            </span> <br>
{{--                @if($key < $dados->ServicosCliente->count()-1)--}}
{{--                    <hr style="border-bottom: 1px dashed #ccc; border-top: none;border-right: none;border-left: none; height: 1px; margin-top: 10px; margin-bottom: 10px; ">--}}
{{--                @endif--}}
            @endforeach
        </h5>
{{--    @endif--}}
    </h5>
    @include('layouts.rodapePdf')
@endsection
