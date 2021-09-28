<?php

namespace App\Exports;

use App\Models\Funcionarios;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class FuncionariosExport implements FromView
{
    public function view(): View
    {
//        $data = Funcionarios::with('clientepj', 'clientepf', 'servicos')->get();
        $emp = request('empresa_id');
        $cargo = request('cargo_id');
        $cliente = request('cliente_id');
        if (!empty($emp) && empty($cargo) && empty($cliente)) {
            $resultado = Funcionarios::select()
                ->with('Cargo', 'Cargo:id,titulo', 'Empresa:id,razao_social,fantasia', 'Dependentes','Banco','Escolaridade','Deficiencia')
                ->with(['Cliente' => function ($query) {
                    $query->with('Clientepj', 'Clientepf');
                }])
                ->where('empresa_id', $emp)
                ->orderBy('nome')->get();
        }

        // se empresa e cargo for preenchido
        if (!empty($emp) && !empty($cargo) && empty($cliente)) {
            $resultado = Funcionarios::select()
                ->with('Cargo', 'Cargo:id,titulo', 'Empresa:id,razao_social,fantasia', 'Dependentes','Banco','Escolaridade','Deficiencia')
                ->with(['Cliente' => function ($query) {
                    $query->with('Clientepj', 'Clientepf');
                }])
                ->where(['empresa_id' => $emp, 'cargo_id' => $cargo])
                ->orderBy('nome')->get();
        }

        // Se somente Cargo for preenchido
        if (empty($emp) && !empty($cargo) && empty($cliente)) {
            $resultado = Funcionarios::select()
                ->with('Cargo', 'Cargo:id,titulo', 'Empresa:id,razao_social,fantasia', 'Dependentes','Banco','Escolaridade','Deficiencia')
                ->with(['Cliente' => function ($query) {
                    $query->with('Clientepj', 'Clientepf');
                }])
                ->where('cargo_id', $cargo)
                ->orderBy('nome')->get();
        }
        // se cargo e cliente for preenchido
        if (empty($emp) && !empty($cargo) && !empty($cliente)) {
            $resultado = Funcionarios::select()
                ->with('Cargo', 'Cargo:id,titulo', 'Empresa:id,razao_social,fantasia', 'Dependentes','Banco','Escolaridade','Deficiencia')
                ->with(['Cliente' => function ($query) {
                    $query->with('Clientepj', 'Clientepf');
                }])
                ->where(['cargo_id' => $cargo, 'empresa_id' => $emp])
                ->orderBy('nome')->get();
        }

        // Se somente Cliente for preenchido
        if (empty($emp) && empty($cargo) && !empty($cliente)) {
            $resultado = Funcionarios::select()
                ->with('Cargo', 'Cargo:id,titulo', 'Empresa:id,razao_social,fantasia', 'Dependentes','Banco','Escolaridade','Deficiencia')
                ->with(['Cliente' => function ($query) {
                    $query->with('Clientepj', 'Clientepf');
                }])
                ->where('cliente_id', $cliente)
                ->orderBy('nome')->get();
        }
        // Se empresa Cliente for preenchido
        if (!empty($emp) && empty($cargo) && !empty($cliente)) {
            $resultado = Funcionarios::select()
                ->with('Cargo', 'Cargo:id,titulo', 'Empresa:id,razao_social,fantasia', 'Dependentes','Banco','Escolaridade','Deficiencia')
                ->with(['Cliente' => function ($query) {
                    $query->with('Clientepj', 'Clientepf');
                }])
                ->where(['cliente_id' => $cliente, 'empresa_id' => $emp])
                ->orderBy('nome')->get();
        }

        // Se Cliente e Cargos for preenchido
        if (empty($emp) && !empty($cargo) && !empty($cliente)) {
            $resultado = Funcionarios::select()
                ->with('Cargo', 'Cargo:id,titulo', 'Empresa:id,razao_social,fantasia', 'Dependentes','Banco','Escolaridade','Deficiencia')
                ->with(['Cliente' => function ($query) {
                    $query->with('Clientepj', 'Clientepf');
                }])
                ->where(['cliente_id' => $cliente, 'cargo_id' => $cargo])
                ->orderBy('nome')->get();
        }

        //se todos forem preenchido
        if (!empty($emp) && !empty($cargo) && !empty($cliente)) {
            $resultado = Funcionarios::select()
                ->with('Cargo', 'Cargo:id,titulo', 'Empresa:id,razao_social,fantasia', 'Dependentes','Banco','Escolaridade','Deficiencia')
                ->with(['Cliente' => function ($query) {
                    $query->with('Clientepj', 'Clientepf');
                }])
                ->where(['empresa_id' => $emp, 'cargo_id' => $cargo, 'cliente_id' => $cliente])
                ->orderBy('nome')->get();
        }

        // Se nao houver filtro
        if (empty($emp) && empty($cargo) && empty($cliente)) {
            $resultado = Funcionarios::select()
                ->with('Cargo', 'Cargo:id,titulo', 'Empresa:id,razao_social,fantasia', 'Dependentes','Banco','Escolaridade','Deficiencia')
                ->with(['Cliente' => function ($query) {
                    $query->with('Clientepj', 'Clientepf');
                }])->orderBy('nome')->get(); // senao busca tudo
        }
        return view('excel.funcionarioXml',compact('resultado'));
    }
}
