<?php

namespace App\Exports;

use App\Models\Cliente;
use App\Models\ServicosCliente;
use App\Models\ServicosProspects;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ClientesExport implements FromView
{
    public function view(): View
    {
        $data = Cliente::pluck('id');

        $servicos = [];
        $prospect = [];
        foreach ($data as $id) {
            foreach (ServicosProspects::whereClienteId($id)->with('Servico:id,titulo',
                'Cliente:id,tipo_cliente,razao_social,nome_fantasia,nome,ramo,tipo,cep,logradouro,numero,complemento,bairro,municipio,uf,contato,email,aniversario,area_id',
                'Cliente.Telefones',
                'Cliente.Area:id,label')->orderBy('cliente_id')->orderByDesc('id')->get()->transform(function ($q) {
                $q->prospect = 1;
                return $q;
            }) as $s) {
                $prospect[] = $s;
            }
            foreach (ServicosCliente::whereClienteId($id)->with('Servico:id,titulo',
                'Cliente:id,tipo_cliente,razao_social,nome_fantasia,nome,ramo,tipo,cep,logradouro,numero,complemento,bairro,municipio,uf,contato,email,aniversario,area_id',
                'Cliente.Telefones',
                'Cliente.Area:id,label')->orderBy('cliente_id')->orderByDesc('id')->get()->transform(function ($q) {
                $q->client = 1;
                return $q;
            }) as $s) {
                $servicos[] = $s;
            }
        }

        $dados = collect(array_merge($servicos, $prospect));

        return view('excel.clienteXml', compact('dados'));
    }
}
