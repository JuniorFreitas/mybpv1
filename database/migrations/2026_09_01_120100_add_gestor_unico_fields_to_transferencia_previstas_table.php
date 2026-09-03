<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transferencia_previstas', function (Blueprint $table) {
            $table->string('modo_aprovacao', 20)->default('padrao')->after('fluxo_gestores_automatico');
            $table->unsignedBigInteger('gestor_aprovacao_id')->nullable()->after('modo_aprovacao');
            $table->string('status_aprovacao_gestor_unico')->nullable()->after('gestor_aprovacao_id');
            $table->unsignedBigInteger('user_aprovacao_gestor_unico_id')->nullable()->after('status_aprovacao_gestor_unico');
            $table->dateTime('data_aprovacao_gestor_unico')->nullable()->after('user_aprovacao_gestor_unico_id');
            $table->text('obs_aprovacao_gestor_unico')->nullable()->after('data_aprovacao_gestor_unico');

            $table->foreign('gestor_aprovacao_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('user_aprovacao_gestor_unico_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transferencia_previstas', function (Blueprint $table) {
            $table->dropForeign(['gestor_aprovacao_id']);
            $table->dropForeign(['user_aprovacao_gestor_unico_id']);
            $table->dropColumn([
                'modo_aprovacao',
                'gestor_aprovacao_id',
                'status_aprovacao_gestor_unico',
                'user_aprovacao_gestor_unico_id',
                'data_aprovacao_gestor_unico',
                'obs_aprovacao_gestor_unico',
            ]);
        });
    }
};
