<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItensCloudTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('itens_cloud', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cloud_id')->index('itens_cloud_cloud_id_foreign');
            $table->unsignedBigInteger('arquivo_id')->nullable()->index('itens_cloud_arquivo_id_foreign');
            $table->string('label', 255);
            $table->string('tipo');
            $table->unsignedBigInteger('pertence')->nullable()->index('itens_cloud_pertence_foreign');
            $table->unsignedBigInteger('quem_criou')->index('itens_cloud_quem_criou_foreign');
            $table->boolean('aprovado')->default(0);
            $table->unsignedBigInteger('quem_aprovou')->nullable()->index('itens_cloud_quem_aprovou_foreign');
            $table->dateTime('data_aprovacao')->nullable();
            $table->boolean('revisado')->default(0);
            $table->unsignedBigInteger('quem_revisou')->nullable()->index('itens_cloud_quem_revisou_foreign');
            $table->dateTime('data_revisao')->nullable();
            $table->unsignedBigInteger('quem_editou')->nullable()->index('itens_cloud_quem_editou_foreign');
            $table->unsignedBigInteger('quem_excluiu')->nullable()->index('itens_cloud_quem_excluiu_foreign');
            $table->boolean('movido')->default(0);
            $table->unsignedBigInteger('quem_moveu')->nullable()->index('itens_cloud_quem_moveu_foreign');
            $table->dateTime('data_movido')->nullable();
            $table->unsignedBigInteger('pertence_anterior')->nullable()->index('itens_cloud_pertence_anterior_foreign');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('itens_cloud');
    }
}
