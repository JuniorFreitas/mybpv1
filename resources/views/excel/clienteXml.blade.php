{{--{{$data}}--}}
<table>
    <thead>
    <tr>
        <td>ID</td>
        <td>TIPO</td>
        <td>CLIENTE</td>
        <td>SERVIÇO CONTRATADO</td>
        <td>DATA INICIO</td>
        <td>DATA ENCERRAMENTO</td>
        <td>ESCOPO</td>
        <td>VALOR R$</td>
        <td>TIPO FATURAMENTO</td>
        <td>STATUS</td>
        <td>FEEDBACK</td>
        <td>DATA DE ENVIO PROPOSTA</td>
        <td>SERVIÇO PROPOSTA</td>
        <td>ESCOPO PROPOSTA</td>
        <td>STATUS PROPOSTA</td>
        <td>CONTATO</td>
        {{--        <td>CARGO</td>--}}
        <td>TELEFONE</td>
        <td>ANIVERSÁRIO DO CLIENTE</td>
        <td>STATUS NO SISTEMA</td>
    </tr>
    </thead>
    <tbody>
    @foreach($dados as $row)
        <tr>
            <td>{{ $row->cliente->id}}</td>
            <td>{{ $row->cliente->tipo_cliente }}</td>
            <td>{{ $row->cliente->tipo == \App\Models\Clientes::TIPO_PESSOA_JURIDICA ? $row->cliente->razao_social : $row->nome }}</td>
            <td>{{ $row->client == 1 ? $row->servico->titulo : '' }}</td>
            <td>{{ $row->client == 1 ? MasterTag\DataHora::dataFormatada($row->data_inicio) : '' }}</td>
            <td>{{ $row->client == 1 ? MasterTag\DataHora::dataFormatada($row->data_encerramento) : '' }}</td>
            <td>{{ $row->client == 1 ? $row->escopo : '' }}</td>
            <td>{{ $row->client == 1 ? $row->valor : '' }}</td>
            <td>{{ $row->client == 1 ? $row->tipo_faturamento : '' }}</td>
            <td>{{ $row->client == 1 ? $row->status : '' }}</td>
            <td>{{ $row->client == 1 ? $row->feedback : '' }}</td>

            <td>{{ isset($row->data_envio_proposta) ? \MasterTag\DataHora::dataFormatada($row->data_envio_proposta) : '' }}</td>
            <td>{{ $row->prospect == 1 ? $row->servico->titulo : '' }}</td>
            <td>{{ $row->prospect == 1 ? $row->escopo : '' }}</td>
            <td>{{ $row->prospect == 1 ? $row->status : '' }}</td>
            <td>
                {{ $row->cliente->contato }}
            </td>
            {{--            <td>{{ $row->cliente->cargo}}</td>--}}
            <td>
                @foreach($row->cliente->telefones as $key => $tel)
                    {{ $tel->numero }} @if($key < $row->cliente->telefones->count()-1), @endif
                @endforeach
            </td>
            <td>{{ $row->cliente->aniversario }}</td>
            <td>{{ $row->cliente->ativo == 'true' ? 'Ativo' : 'Inativo' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
