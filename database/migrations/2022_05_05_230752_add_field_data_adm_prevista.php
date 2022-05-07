<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldDataAdmPrevista extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admissoes', function (Blueprint $table) {
            $table->date('data_adm_prevista')->after('data_admissao')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admissoes', function (Blueprint $table) {
            $table->removeColumn('data_adm_prevista');
        });
    }
}
