<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddTreinamentoFieldsToMudancaCargoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mudanca_cargo', function (Blueprint $table) {
            $table->boolean('treinamento_funcao')->default(false)->after('nova_funcao');
            $table->date('treinamento_data_inicio')->nullable()->after('treinamento_funcao');
            $table->date('treinamento_data_fim')->nullable()->after('treinamento_data_inicio');
        });

        Schema::table('mudanca_cargo_anexos', function (Blueprint $table) {
            $table->string('tipo_anexo')->default('anexo_default')->after('arquivo_id');
        });

        // Atualiza registros existentes para ter o valor padrão
        DB::table('mudanca_cargo_anexos')->whereNull('tipo_anexo')->update(['tipo_anexo' => 'anexo_default']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mudanca_cargo_anexos', function (Blueprint $table) {
            $table->dropColumn('tipo_anexo');
        });
        
        Schema::table('mudanca_cargo', function (Blueprint $table) {
            $table->dropColumn('treinamento_data_fim');
            $table->dropColumn('treinamento_data_inicio');
            $table->dropColumn('treinamento_funcao');
        });
    }
}

