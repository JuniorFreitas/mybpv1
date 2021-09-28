<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\User;
use DB;
use Exception;
use Illuminate\Database\Seeder;
use MasterTag\DataHora;

class ClientesEmpresaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $clientes = Cliente::withoutGlobalScope('scopeCliente')->pluck('id');
       $user = User::find(104);
       foreach ($clientes as $cliente){
           $user->ClientesEmpresa()->attach($cliente);
       }
    }
}
