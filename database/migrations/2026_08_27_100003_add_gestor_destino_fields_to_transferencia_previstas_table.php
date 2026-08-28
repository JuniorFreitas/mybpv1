<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transferencia_previstas', function (Blueprint $table) {
            $table->boolean('exige_aprovacao_gestor_destino')->default(false)->after('gestor_id');
            $table->boolean('fluxo_gestores_automatico')->default(false)->after('exige_aprovacao_gestor_destino');
            $table->unsignedBigInteger('gestor_destino_id')->nullable()->after('exige_aprovacao_gestor_destino');
            $table->unsignedBigInteger('user_aprovacao_gestor_destino_id')->nullable()->after('gestor_destino_id');
            $table->string('status_aprovacao_gestor_destino')->nullable()->after('user_aprovacao_gestor_destino_id');
            $table->text('obs_aprovacao_gestor_destino')->nullable()->after('status_aprovacao_gestor_destino');
            $table->dateTime('data_aprovacao_gestor_destino')->nullable()->after('obs_aprovacao_gestor_destino');

            $table->foreign('gestor_destino_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('user_aprovacao_gestor_destino_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transferencia_previstas', function (Blueprint $table) {
            $table->dropForeign(['gestor_destino_id']);
            $table->dropForeign(['user_aprovacao_gestor_destino_id']);
            $table->dropColumn([
                'exige_aprovacao_gestor_destino',
                'fluxo_gestores_automatico',
                'gestor_destino_id',
                'user_aprovacao_gestor_destino_id',
                'status_aprovacao_gestor_destino',
                'obs_aprovacao_gestor_destino',
                'data_aprovacao_gestor_destino',
            ]);
        });
    }
};
