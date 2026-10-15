<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('empresa_api_credenciais')) {
            return;
        }

        Schema::create('empresa_api_credenciais', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('user_id');
            $table->string('client_id', 64);
            $table->string('client_secret_hash', 255);
            $table->json('scopes');
            $table->boolean('ativo')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('client_id', 'empresa_api_credenciais_client_id_uq');
            $table->index(['empresa_id', 'ativo'], 'empresa_api_credenciais_empresa_ativo_idx');
            $table->index('user_id', 'empresa_api_credenciais_user_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_api_credenciais');
    }
};
