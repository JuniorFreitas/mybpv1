@extends('layouts.pdf')
@section('title','RELATÓRIO DE ATAS E PENDÊNCIAS')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <h5 class="text-center text-uppercase" style="margin-top: 30px">RELATÓRIO DE ATAS E PENDÊNCIAS</h5>
    <table width="100%" border="0" class="tabela" style="margin-top: 20px">
        <tr class="topo">
            @foreach($headers as $header)
                <td>{{ $header }}</td>
            @endforeach
        </tr>
        @forelse($rows as $row)
            <tr class="linha">
                @foreach($row as $value)
                    <td>{{ $value }}</td>
                @endforeach
            </tr>
        @empty
            <tr class="linha">
                <td colspan="{{ count($headers) }}">Nenhum registro encontrado</td>
            </tr>
        @endforelse
    </table>
    @include('layouts.rodapePdf')
@endsection
@push('style')
    <style type="text/css">
        .tabela { font-family: Helvetica, Arial, sans-serif; font-size: 7pt; border-collapse: collapse; }
        tr.topo td { border-bottom: 1px solid #ddd; font-weight: bold; text-transform: uppercase; color: #000; padding: 3px; background-color: #ccc; }
        tr.linha { color: #000; background-color: #F0F0F0; }
        tr.linha td { border-bottom: 1px solid #acacac; padding: 4px; }
    </style>
@endpush
