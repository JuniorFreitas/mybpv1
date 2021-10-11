@extends('layouts.pdf')
@section('title','Prova')
@section('empresa')
@include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
</h5><br>

<p style="line-height: 20pt; font-size: 12pt; text-align: left">
    Nome: __________________________________________________________________________<br>
</p>
<p style="line-height: 20pt; font-size: 12pt; text-align: left">
    Data: _____/____/___________<br>
</p>
<p style="line-height: 20pt; font-size: 12pt; text-align: left">
    Local: ___________________________________________________________________________<br>
</p>
<p style="line-height: 20pt; font-size: 12pt; text-align: left">
    Vaga: {{$vaga['titulo']}}<br>
</p><br><br>


@foreach($prova['Perguntas'] as $pergunta)
    {!! $pergunta['enunciado'] !!}<br>
    @for($i=0; $i < $pergunta['qnt_linhas']; $i++)
        <span style="line-height: 20pt">_________________________________________________________________________________</span>
        <br>
    @endfor
    <br>
    <br>
@endforeach

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
