<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notificacao_tipos')) {
            Schema::create('notificacao_tipos', function (Blueprint $table) {
                $table->string('codigo', 64)->primary();
                $table->string('nome', 120);
                $table->string('canal', 32)->default('email');
                $table->string('regra_elegibilidade', 64)->nullable();
                $table->boolean('ativo')->default(true);
                $table->boolean('editavel')->default(false);
                $table->unsignedSmallInteger('ordem')->default(100);
            });
        }

        $tipos = [
            ['codigo' => 'aprovacao_rh', 'nome' => 'Aprovação RH (Movimentações)', 'canal' => 'email', 'regra_elegibilidade' => 'privilegios_rh', 'ativo' => 1, 'editavel' => 1, 'ordem' => 10],
            ['codigo' => 'vencimento_aso', 'nome' => 'Vencimento ASO', 'canal' => 'email', 'regra_elegibilidade' => null, 'ativo' => 1, 'editavel' => 0, 'ordem' => 20],
            ['codigo' => 'vencimento_ferias', 'nome' => 'Vencimento Férias', 'canal' => 'email', 'regra_elegibilidade' => null, 'ativo' => 1, 'editavel' => 0, 'ordem' => 30],
            ['codigo' => 'vencimento_treinamento', 'nome' => 'Vencimento Treinamento', 'canal' => 'email', 'regra_elegibilidade' => null, 'ativo' => 1, 'editavel' => 0, 'ordem' => 40],
            ['codigo' => 'avaliacao_90_dias', 'nome' => 'Avaliação 90 Dias', 'canal' => 'email', 'regra_elegibilidade' => null, 'ativo' => 1, 'editavel' => 0, 'ordem' => 50],
        ];

        foreach ($tipos as $tipo) {
            $exists = DB::table('notificacao_tipos')->where('codigo', $tipo['codigo'])->exists();
            if (!$exists) {
                DB::table('notificacao_tipos')->insert($tipo);
            }
        }

        if (!Schema::hasTable('notificacao_grupos')) {
            Schema::create('notificacao_grupos', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('empresa_id');
                $table->string('tipo_codigo', 64);
                $table->string('nome', 160);
                $table->boolean('ativo')->default(true);
                $table->string('escopo_cc', 32)->default('todos');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['empresa_id', 'tipo_codigo', 'ativo'], 'notificacao_grupos_empresa_tipo_ativo_idx');
                $table->index(['empresa_id', 'tipo_codigo', 'deleted_at'], 'notificacao_grupos_empresa_tipo_deleted_idx');
                $table->foreign('tipo_codigo')->references('codigo')->on('notificacao_tipos');
            });
        }

        if (!Schema::hasTable('notificacao_grupo_destinatarios')) {
            Schema::create('notificacao_grupo_destinatarios', function (Blueprint $table) {
                $table->unsignedInteger('grupo_id');
                $table->unsignedInteger('user_id');
                $table->primary(['grupo_id', 'user_id'], 'notificacao_grupo_destinatarios_pk');
                $table->foreign('grupo_id')->references('id')->on('notificacao_grupos')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('notificacao_grupo_centros_custo')) {
            Schema::create('notificacao_grupo_centros_custo', function (Blueprint $table) {
                $table->unsignedInteger('grupo_id');
                $table->unsignedInteger('centro_custo_id');
                $table->primary(['grupo_id', 'centro_custo_id'], 'notificacao_grupo_centros_custo_pk');
                $table->foreign('grupo_id')->references('id')->on('notificacao_grupos')->onDelete('cascade');
            });
        }

        $habExists = DB::table('habilidades')->where('nome', 'configuracao_notificacoes')->exists();
        if (!$habExists) {
            DB::table('habilidades')->insert([
                'nome' => 'configuracao_notificacoes',
                'descricao' => 'Configurar grupos de destinatários de notificações (menu Configurações)',
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacao_grupo_centros_custo');
        Schema::dropIfExists('notificacao_grupo_destinatarios');
        Schema::dropIfExists('notificacao_grupos');
        Schema::dropIfExists('notificacao_tipos');
        DB::table('habilidades')->where('nome', 'configuracao_notificacoes')->delete();
    }
};
