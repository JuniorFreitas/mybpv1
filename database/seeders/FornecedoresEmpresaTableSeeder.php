<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Fornecedor;
use App\Models\User;
use DB;
use Exception;
use Illuminate\Database\Seeder;
use MasterTag\DataHora;

class FornecedoresEmpresaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $fornecedores = Fornecedor::withoutGlobalScope('scopeFornecedor')->pluck('id');
       $user = User::find(97);
       foreach ($fornecedores as $fornecedor){
           $user->FornecedoresEmpresa()->attach($fornecedor);
       }
    }
}
