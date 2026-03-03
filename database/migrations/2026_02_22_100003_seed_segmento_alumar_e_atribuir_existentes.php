<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedSegmentoAlumarEAtribuirExistentes extends Migration
{
    /**
     * Run the migrations.
     * Cria segmento ALUMAR (default do sistema) e atribui vencimentos/admissões existentes.
     *
     * @return void
     */
    public function up()
    {
        $configCarteira = json_encode([
            'cabecalho_img' => 'images/carteira/cabecalho_carteira_alumar.webp',
            'verso_img' => 'images/carteira/verso_carteira_alumar.webp',
            'exibir_etiqueta_bloqueio' => true,
        ]);

        if (DB::table('segmentos_treinamento')->where('slug', 'alumar')->doesntExist()) {
            DB::table('segmentos_treinamento')->insert([
                'nome' => 'ALUMAR',
                'slug' => 'alumar',
                'ativo' => true,
                'config_carteira' => $configCarteira,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $alumarId = DB::table('segmentos_treinamento')->where('slug', 'alumar')->value('id');
        if ($alumarId) {
            DB::table('vencimentos')->whereNull('segmento_treinamento_id')->update(['segmento_treinamento_id' => $alumarId]);
            DB::table('admissoes')->whereNull('segmento_treinamento_id')->update(['segmento_treinamento_id' => $alumarId]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $alumarId = DB::table('segmentos_treinamento')->where('slug', 'alumar')->value('id');
        if ($alumarId) {
            DB::table('vencimentos')->where('segmento_treinamento_id', $alumarId)->update(['segmento_treinamento_id' => null]);
            DB::table('admissoes')->where('segmento_treinamento_id', $alumarId)->update(['segmento_treinamento_id' => null]);
        }
        DB::table('segmentos_treinamento')->where('slug', 'alumar')->delete();
    }
}
