<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Como a carteira/etiqueta é montada: identificador (chapa ou matrícula) e blade opcional.
 */
class AddIdentificadorEBladeToCarteirasEEtiquetas extends Migration
{
    public function up()
    {
        Schema::table('carteiras_treinamento', function (Blueprint $table) {
            $table->string('identificador', 20)->default('chapa')->after('verso_img');
            $table->string('blade', 80)->nullable()->after('identificador');
        });

        Schema::table('etiquetas_bloqueio', function (Blueprint $table) {
            $table->string('identificador', 20)->default('chapa')->after('bloqueio_texto_homens_trabalhando');
            $table->string('blade', 80)->nullable()->after('identificador');
        });
    }

    public function down()
    {
        Schema::table('carteiras_treinamento', function (Blueprint $table) {
            $table->dropColumn(['identificador', 'blade']);
        });

        Schema::table('etiquetas_bloqueio', function (Blueprint $table) {
            $table->dropColumn(['identificador', 'blade']);
        });
    }
}
