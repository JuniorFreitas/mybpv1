<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa_whatsapp_configs', function (Blueprint $table) {
            $table->json('modulos_habilitados')->nullable()->after('texto_assinatura');
        });

        Schema::create('usuario_whatsapp_preferencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('modulo', 80);
            $table->boolean('receber')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'modulo']);
            $table->index(['user_id', 'receber']);
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_whatsapp_preferencias');

        Schema::table('empresa_whatsapp_configs', function (Blueprint $table) {
            $table->dropColumn('modulos_habilitados');
        });
    }
};
