<?php

namespace Database\Seeders;

use DB;
use Exception;
use Illuminate\Database\Seeder;

class PapeisTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lista[] = ['nome' => 'Suporte', 'descricao' => 'Tipo de usuário usado pelos desenvolvedores do sistema', 'empresa_id' => 1, 'email' => 'dev@mybp.com.br', 'ativo' => 's'];
        $lista[] = ['nome' => 'Administrador', 'descricao' => 'Tipo de usuário administrador do sistema', 'empresa_id' => 1, 'email' => 'diretoria@bpse.com.br', 'ativo' => 's'];

        foreach ($lista as $papel) {
            \App\Models\Papel::create($papel);
        }

//        try {
//            DB::beginTransaction();
//            $table = DB::connection('mysql_antes')
//                ->table('papeis')->get();
//            foreach ($table as $tabela) {
//                \App\Models\Papel::create(
//                    [
//                        'id' => $tabela->id,
//                        'nome' => $tabela->nome,
//                        'descricao' => $tabela->descricao,
//                        'email' => $tabela->email,
//                        'ativo' => $tabela->ativo,
//                    ]
//                );
//            }
//            DB::commit();
//        } catch (Exception $e) {
//            DB::rollBack();
//            return $e->getTrace() . ' - ' . $e->getCode() . ' - ' . $e->getCode() . ' - ' . $e->getLine();
//        }
    }
}
