<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * As migrations 100002 e 100003 originalmente gravaram o sufixo de
     * desambiguação DEPOIS do domínio (ex: "joao@gmail.com+dup123"), o que
     * não é um e-mail válido. Os dois arquivos já foram corrigidos para
     * inserir o sufixo antes do '@' — esta migration só repara as linhas que
     * já tinham sido gravadas com o formato antigo antes da correção.
     *
     * Ambiente que roda as migrations pela primeira vez (produção) já usa a
     * versão corrigida de 100002/100003 e nunca produz o formato errado —
     * esta migration é essencialmente um no-op nesse caso.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('login', 'like', '%@%+dup%')
            ->orderBy('id')
            ->select('id', 'login')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $corrigido = preg_replace(
                        '/^(.*)@(.*)(\+dup\d+)$/',
                        '$1$3@$2',
                        $row->login
                    );

                    if ($corrigido !== null && $corrigido !== $row->login) {
                        DB::table('users')
                            ->where('id', $row->id)
                            ->update(['login' => $corrigido]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Não reversível de forma útil.
    }
};
