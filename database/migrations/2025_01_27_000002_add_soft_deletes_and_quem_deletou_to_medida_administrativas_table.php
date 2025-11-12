<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesAndQuemDeletouToMedidaAdministrativasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('medida_administrativas', function (Blueprint $table) {
            $table->unsignedBigInteger('quem_deletou_id')->nullable()->after('data_retorno');
            $table->softDeletes();
            
            $table->foreign('quem_deletou_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('medida_administrativas', function (Blueprint $table) {
            $table->dropForeign(['quem_deletou_id']);
            $table->dropColumn(['quem_deletou_id', 'deleted_at']);
        });
    }
}

