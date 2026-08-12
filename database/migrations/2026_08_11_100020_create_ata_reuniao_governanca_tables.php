<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ata_reuniao_acessos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->unsignedBigInteger('user_id');
            $table->string('papel', 40);
            $table->string('origem', 40)->default('manual');
            $table->timestamp('expira_em')->nullable();
            $table->timestamp('revogado_em')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'ata_reuniao_id', 'user_id', 'papel'], 'ata_acesso_unique');
            $table->index(['empresa_id', 'user_id', 'ata_reuniao_id'], 'ata_acesso_emp_user_ata_idx');
            $table->index(['empresa_id', 'ata_reuniao_id', 'papel'], 'ata_acesso_emp_ata_papel_idx');
        });

        Schema::create('ata_reuniao_aprovacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->unsignedBigInteger('aprovador_id');
            $table->string('versao', 20)->default('0.1');
            $table->string('status', 30)->default('pendente');
            $table->string('decisao', 40)->nullable();
            $table->text('comentario')->nullable();
            $table->timestamp('respondido_em')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'ata_reuniao_id', 'versao', 'aprovador_id'], 'ata_aprov_unique');
            $table->index(['empresa_id', 'aprovador_id', 'status'], 'ata_aprov_emp_user_status_idx');
            $table->index(['empresa_id', 'ata_reuniao_id', 'status'], 'ata_aprov_emp_ata_status_idx');
        });

        Schema::create('ata_reuniao_versoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->string('numero', 20);
            $table->unsignedBigInteger('autor_id')->nullable();
            $table->text('descricao')->nullable();
            $table->json('campos_alterados')->nullable();
            $table->json('snapshot')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'ata_reuniao_id', 'numero'], 'ata_versao_unique');
            $table->index(['empresa_id', 'ata_reuniao_id', 'created_at'], 'ata_versao_emp_ata_data_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ata_reuniao_versoes');
        Schema::dropIfExists('ata_reuniao_aprovacoes');
        Schema::dropIfExists('ata_reuniao_acessos');
    }
};
