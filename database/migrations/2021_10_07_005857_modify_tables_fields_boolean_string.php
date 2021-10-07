<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyTablesFieldsBooleanString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parecer_entrevista_tecnica', function (Blueprint $table) {
            $table->string('opera_plat_movel',100)->nullable()->change();
            $table->string('opera_plat_ponte',100)->nullable()->change();
            $table->string('experiencia_cargas_rigger',100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
