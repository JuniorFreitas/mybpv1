<?php

namespace App\Tenant\Scopes;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ScopeClientesEmpresa implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        /*
         * USER (TIPOS)
         * EMPRESA CLIENTE (USER,USER)
         * FUNCIONARIO CLIENTE (USER,USER)
         *
         * CLIENTE PERTENCE A EMPRESA
         *              FUNCIONARIO PERTENCE A CLIENTE
         *
         * */

        //Todos os funcionarios do Cliente
//        if(auth()->user()->tipo == 'Funcionario'){

        $cliente = auth()->user()->ClienteFuncionarios->first();

        //Se for funcionario do Cliente
        if($cliente){
//            $cliente = auth()->user()->ClienteFuncionarios->first();
            return $builder->where('cliente_id', $cliente->id);
        }

        //Todos os clientes da empresa
        return $builder->whereIn('cliente_id', auth()->user()->ClientesEmpresa->pluck('id'));

    }
}
