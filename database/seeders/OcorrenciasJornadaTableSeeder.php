<?php

namespace Database\Seeders;

use App\Models\OcorrenciaJornada;
use DB;
use Illuminate\Database\Seeder;

class OcorrenciasJornadaTableSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $lista[] = ['descricao' => 'Abono', 'trabalhado' => false, 'conta_horas' => false, 'ativo' => true];
        $lista[] = ['descricao' => 'Afastado', 'trabalhado' => false, 'conta_horas' => false, 'ativo' => true];
        $lista[] = ['descricao' => 'Atestado', 'trabalhado' => false, 'conta_horas' => false, 'ativo' => true];
        $lista[] = ['descricao' => 'Dia trabalhado', 'trabalhado' => true, 'conta_horas' => true, 'ativo' => true];
        $lista[] = ['descricao' => 'Falta', 'trabalhado' => false, 'conta_horas' => true, 'ativo' => true];
        $lista[] = ['descricao' => 'Falta BH', 'trabalhado' => false, 'conta_horas' => false, 'ativo' => true];
        $lista[] = ['descricao' => 'Feriado', 'trabalhado' => false, 'conta_horas' => false, 'ativo' => true];
        $lista[] = ['descricao' => 'Folga', 'trabalhado' => false, 'conta_horas' => false, 'ativo' => true];
        $lista[] = ['descricao' => 'Férias', 'trabalhado' => false, 'conta_horas' => false, 'ativo' => true];
        $lista[] = ['descricao' => 'Jornada externa', 'trabalhado' => true, 'conta_horas' => true, 'ativo' => true];
        $lista[] = ['descricao' => 'Suspensão', 'trabalhado' => false, 'conta_horas' => false, 'ativo' => true];
        $lista[] = ['descricao' => 'Suspensão de contrato', 'trabalhado' => false, 'conta_horas' => false, 'ativo' => true];

        try {
            DB::beginTransaction();

            foreach ($lista as $ocorrenca) {
                if (OcorrenciaJornada::withoutGlobalScopes()->whereDescricao($ocorrenca['descricao'])->count() == 0) {

                    OcorrenciaJornada::withoutGlobalScopes()->create($ocorrenca);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage(),$e->getCode(),$e->getLine(),$e->getTraceAsString());
        }
    }

}
