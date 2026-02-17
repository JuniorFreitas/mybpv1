<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mudanca_cargo', function (Blueprint $table) {
            $table->unsignedBigInteger('aprovacao_extra_id')->nullable()->after('status_aprovacao_gestor');
            $table->string('status_aprovacao_extra')->nullable()->after('aprovacao_extra_id');
            $table->text('obs_aprovacao_extra')->nullable()->after('status_aprovacao_extra');
            $table->string('data_aprovacao_extra')->nullable()->after('obs_aprovacao_extra');

            $table->foreign('aprovacao_extra_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mudanca_cargo', function (Blueprint $table) {
            $table->dropForeign(['aprovacao_extra_id']);
            $table->dropColumn(['aprovacao_extra_id', 'status_aprovacao_extra', 'obs_aprovacao_extra', 'data_aprovacao_extra']);
        });
    }
};
