<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmpresaIdIntermitenteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('intermitentes', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->unsignedBigInteger('cliente_id')->nullable()->change();
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
