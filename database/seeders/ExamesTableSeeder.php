<?php

namespace Database\Seeders;

use App\Models\ExameRiscoTipo;
use App\Models\ExameTipo;
use App\Models\OcorrenciaJornada;
use App\Scopes\ScopeEmpresa;
use DB;
use Illuminate\Database\Seeder;

class ExamesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $listaTipoExame[] = ['label' => 'Admissional', 'ativo' => true];
        $listaTipoExame[] = ['label' => 'Periódico', 'ativo' => true];
        $listaTipoExame[] = ['label' => 'Pericial', 'ativo' => true];
        $listaTipoExame[] = ['label' => 'Mudança de Função', 'ativo' => true];
        $listaTipoExame[] = ['label' => 'Retorno Trabalho', 'ativo' => true];
        $listaTipoExame[] = ['label' => 'Demissional', 'ativo' => true];

        $listaRiscoTipo[] = ['label' => 'Físico', 'ativo' => true];
        $listaRiscoTipo[] = ['label' => 'Químico', 'ativo' => true];
        $listaRiscoTipo[] = ['label' => 'Biológico', 'ativo' => true];
        $listaRiscoTipo[] = ['label' => 'Ergonômico', 'ativo' => true];


        try {
            DB::beginTransaction();
            foreach ($listaTipoExame as $tipoexame) {
                ExameTipo::create($tipoexame);
            }
            foreach ($listaRiscoTipo as $riscotipo) {
                ExameRiscoTipo::create($riscotipo);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage(), $e->getCode(), $e->getLine(), $e->getTraceAsString());
        }
    }

}
