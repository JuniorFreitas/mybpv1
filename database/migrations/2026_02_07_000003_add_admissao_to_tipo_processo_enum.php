<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddAdmissaoToTipoProcessoEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // MySQL: Alterar o enum para incluir 'admissao', 'requisicao_vaga'
        DB::statement("ALTER TABLE aprovacao_extra_configs MODIFY COLUMN tipo_processo ENUM(
            'demissao',
            'ferias',
            'mudanca_cargo',
            'transferencia',
            'intermitente_fixo',
            'valor_extra',
            'requisicao_vaga',
            'admissao'
        ) NOT NULL COMMENT 'Tipo de processo que terá aprovação extra'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reverter para o enum original (sem admissao e requisicao_vaga)
        DB::statement("ALTER TABLE aprovacao_extra_configs MODIFY COLUMN tipo_processo ENUM(
            'demissao',
            'ferias',
            'mudanca_cargo',
            'transferencia',
            'intermitente_fixo',
            'valor_extra'
        ) NOT NULL COMMENT 'Tipo de processo que terá aprovação extra'");
    }
}
