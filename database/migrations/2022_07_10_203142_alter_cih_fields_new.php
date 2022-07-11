<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCihFieldsNew extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cihs', function (Blueprint $table) {
            $table->boolean('varios_colaboradores')->after('outra_tag')->default(false);
            $table->longText('colaboradores_avulso')->after('varios_colaboradores')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cihs', function (Blueprint $table) {
            //
        });
    }
}
