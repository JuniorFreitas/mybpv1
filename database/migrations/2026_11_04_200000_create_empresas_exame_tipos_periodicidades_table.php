<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas_exame_tipos_periodicidades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('exame_tipo_id');
            $table->string('unidade', 10); // anos|dias
            $table->unsignedInteger('valor');
            $table->timestamps();

            $table->unique(['empresa_id', 'exame_tipo_id'], 'emp_exame_tipo_period_unique');
            $table->index('empresa_id');
            $table->index('exame_tipo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas_exame_tipos_periodicidades');
    }
};
