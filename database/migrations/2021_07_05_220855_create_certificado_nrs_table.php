<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificadoNrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificado_nrs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable()->index('certificado_nrs_feedback_id_foreign');
            $table->string('nr_dez_instituicao')->nullable();
            $table->date('nr_dez_emissao')->nullable();
            $table->date('nr_dez_validade')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('certificado_nrs');
    }
}
