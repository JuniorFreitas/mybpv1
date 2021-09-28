<?php

namespace Database\Seeders;

use DB;
use Exception;
use Illuminate\Database\Seeder;

class AreaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try{
            DB::beginTransaction();
            $areas = DB::connection('mysql_antes')->table('areas')->get();
            foreach ($areas as $area){
                \App\Models\Area::create(
                    [
                        'id' => $area->id,
                        'label' => $area->label,
                        'ativo' => $area->ativo
                    ]
                );
            }
            DB::commit();
        }catch (Exception $e){
            DB::rollBack();
            return $e->getTrace().' - '.$e->getCode().' - '.$e->getCode().' - '.$e->getLine();
        }
    }
}
