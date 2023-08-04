<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeedbackIdToExamesesmtsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('examesesmts', function (Blueprint $table) {
            $table->boolean('atual')->after('vencido')->default(false);
            $table->unsignedBigInteger('feedback_id')->after('id')->nullable();
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('examesesmts', function (Blueprint $table) {
            $table->dropForeign('feedback_id');
            $table->dropColumn('feedback_id');
            $table->dropColumn('atual');
        });
    }
}
