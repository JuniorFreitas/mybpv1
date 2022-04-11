@extends('layouts.pdf')
@section('empresa')
    @include('layouts.cabecalioEmpresa',['empresa' => $pedidos[0]->pedido->empresa])
@endsection
@section('conteudo')
    <h3 style="text-align: center; text-decoration: underline; text-transform: uppercase">
        Fardamento: Pedido
    </h3>
    <h4>
        Fornecedor: {{$pedidos[0]->pedido->fornecedor->fornecedorpj ? $pedidos[0]->pedido->fornecedor->fornecedorpj->razaosocial .' - '.$pedidos[0]->pedido->fornecedor->fornecedorpj->cnpj : $pedidos[0]->pedido->fornecedor->fornecedorpf->nome.' - '.$pedidos[0]->pedido->fornecedor->fornecedorpf->cpf }}
        <br>
        Dia do pedido: {{(new \MasterTag\DataHora($pedidos[0]->pedido->data_pedido))->dataCompleta()}}<br>
        Prazo de entrega: {{(new \MasterTag\DataHora($pedidos[0]->pedido->prazo_entrega))->dataCompleta()}}
    </h4>
    <table class="custom" width="100%" cellpadding="5">
        <thead>
        <tr class="bg-default">
            <th class="text-center">Qnt</th>
            <th class="text-center">Item</th>
            <th class="text-center">Tamanho</th>
            <th class="text-center">Valor Unitário</th>
            <th class="text-center">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($pedidos as $p)
            <tr style="text-align: center; ">
                <td class="text-center">{{$p->quantidade}}</td>
                <td class="text-center">{{$p->fardamento->tipo}}</td>
                <td class="text-center">{{$p->tamanho}}</td>
                <td class="text-center">R$ {{number_format($p->valor_unitario,'2',',','.')}}</td>
                <td class="text-center">R$ {{number_format($p->quantidade * $p->valor_unitario,'2',',','.')}}</td>
            </tr>
        @endforeach

        </tbody>
    </table>
    <h3>Valor Total: R$ {{$pedidos[0]->pedido->valor_total}}</h3>
    @include('layouts.rodapePdf')
@endsection
