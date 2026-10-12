<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aceite/recusa eletrônico da carta oferta (clientes sem assinatura digital), com as mesmas
     * evidências da assinatura: IP, navegador, local, consentimento, hash e código de verificação.
     */
    public function up(): void
    {
        Schema::table('curriculo_carta_oferta', function (Blueprint $table) {
            // PDF exatamente como foi exibido ao candidato (o template pode mudar depois).
            if (!Schema::hasColumn('curriculo_carta_oferta', 'proposta_arquivo_id')) {
                $table->unsignedBigInteger('proposta_arquivo_id')->nullable()->after('arquivo_id');
            }
            if (!Schema::hasColumn('curriculo_carta_oferta', 'proposta_hash')) {
                $table->string('proposta_hash', 64)->nullable()->after('proposta_arquivo_id');
            }
        });

        if (Schema::hasTable('curriculo_carta_oferta_aceites')) {
            return;
        }

        Schema::create('curriculo_carta_oferta_aceites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('carta_oferta_id')->unique();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('curriculo_id');
            $table->string('decisao', 20);
            $table->text('motivo')->nullable();
            $table->string('nome', 255);
            $table->string('email', 255)->nullable();
            $table->string('cpf', 20)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->json('geolocalizacao')->nullable();
            $table->boolean('consentimento')->default(false);
            $table->text('consentimento_texto')->nullable();
            $table->dateTime('data_decisao_utc');
            $table->unsignedBigInteger('proposta_arquivo_id')->nullable();
            $table->string('proposta_hash', 64)->nullable();
            $table->string('hash_evidencia', 64);
            $table->uuid('codigo_verificacao')->unique();
            $table->unsignedBigInteger('comprovante_arquivo_id')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'curriculo_id'], 'ccoa_empresa_curriculo_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculo_carta_oferta_aceites');

        Schema::table('curriculo_carta_oferta', function (Blueprint $table) {
            foreach (['proposta_hash', 'proposta_arquivo_id'] as $col) {
                if (Schema::hasColumn('curriculo_carta_oferta', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
