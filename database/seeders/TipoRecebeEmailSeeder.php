<?php

namespace Database\Seeders;

use App\Models\TipoRecebeEmail;
use Illuminate\Database\Seeder;
use DB;

class TipoRecebeEmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $listaTipo[] = ['nome' => 'Avaliação 90 Dias'];
        $listaTipo[] = ['nome' => 'Vencimento Férias'];

        try {
            DB::beginTransaction();
            foreach ($listaTipo as $lista) {
                TipoRecebeEmail::create($lista);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage(), $e->getCode(), $e->getLine(), $e->getTraceAsString());
        }
    }
}
