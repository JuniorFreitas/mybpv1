<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nome', 255);
            $table->string('logradouro', 255)->nullable();
            $table->string('complemento', 255)->nullable();
            $table->string('bairro', 255)->nullable();
            $table->string('municipio', 255)->nullable();
            $table->string('uf', 255)->nullable();
            $table->string('cep')->nullable();
            $table->string('login')->nullable();
            $table->string('password')->nullable();
            $table->string('tipo');
            $table->unsignedBigInteger('grupo_id')->nullable();
//            $table->unsignedBigInteger('grupo_cloud_id')->nullable()->index('users_grupo_cloud_id_foreign');
            $table->string('cadastrou')->nullable();
            $table->boolean('ativo');
            $table->boolean('temp')->default(0);
            $table->dateTime('ultimo_acesso')->nullable();
            $table->rememberToken();
            $table->boolean('termos')->nullable()->default(0);
            $table->string('device_token')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable()->index('users_empresa_id_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
