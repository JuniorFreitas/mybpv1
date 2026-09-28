<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exame_tipos', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable()->after('id')->index();
            $table->unsignedBigInteger('formulario_encaminhamento_id')->nullable()->after('ativo')->index();
            $table->unsignedBigInteger('formulario_resultado_id')->nullable()->after('formulario_encaminhamento_id')->index();
            $table->integer('ordem')->default(0)->after('formulario_resultado_id');
        });

        Schema::table('alternativa_formularios', function (Blueprint $table) {
            if (!Schema::hasColumn('alternativa_formularios', 'ativo')) {
                $table->boolean('ativo')->default(true)->after('tipo');
            }
            if (!Schema::hasColumn('alternativa_formularios', 'chave_canonica')) {
                $table->string('chave_canonica', 64)->nullable()->after('ativo')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exame_tipos', function (Blueprint $table) {
            $table->dropColumn([
                'empresa_id',
                'formulario_encaminhamento_id',
                'formulario_resultado_id',
                'ordem',
            ]);
        });

        Schema::table('alternativa_formularios', function (Blueprint $table) {
            if (Schema::hasColumn('alternativa_formularios', 'chave_canonica')) {
                $table->dropColumn('chave_canonica');
            }
            if (Schema::hasColumn('alternativa_formularios', 'ativo')) {
                $table->dropColumn('ativo');
            }
        });
    }
};
