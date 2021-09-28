<?php

namespace Database\Seeders;

use DB;
use Exception;
use Illuminate\Database\Seeder;
use MasterTag\DataHora;

class ArquivoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            DB::beginTransaction();
            $table = DB::connection('mysql_antes')
                ->table('arquivos')->get();
            foreach ($table as $tabela) {
                \App\Models\Arquivo::create(
                    [
                        'id' => $tabela->id,
                        'quem_enviou' => $tabela->quem_enviou,
                        'nome' => $tabela->nome,
                        //'url' => $tabela->/,
                        'local' => $tabela->local,
                        'imagem' => $tabela->imagem,
                        'layout' => $tabela->layout,
                        'extensao' => $tabela->extensao,
                        'file' => $tabela->file,
                        'thumb' => $tabela->thumb,
                        'bytes' => $tabela->bytes,
                        'temporario' => $tabela->temporario,
                        'chave' => $tabela->chave,
                        'created_at' => (new DataHora($tabela->created_at))->dataHoraInsert(),
                        'updated_at' => (new DataHora($tabela->updated_at))->dataHoraInsert(),
                    ]
                );
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return $e->getTrace() . ' - ' . $e->getCode() . ' - ' . $e->getCode() . ' - ' . $e->getLine();
        }
    }
}
