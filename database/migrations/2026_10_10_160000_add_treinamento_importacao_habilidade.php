<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $habId = DB::table('habilidades')->where('nome', 'treinamento_importacao')->value('id');

        if (!$habId) {
            $habId = DB::table('habilidades')->insertGetId([
                'nome' => 'treinamento_importacao',
                'descricao' => 'Acessar rota/menu Importação de Treinamentos',
            ]);
        }

        // Libera para papéis que já têm acesso à carteira de treinamentos
        $habCarteiraId = DB::table('habilidades')
            ->where('nome', 'treinamento_carteira-etiquetas')
            ->value('id');

        if (!$habCarteiraId) {
            return;
        }

        $papelIds = DB::table('papeis_habilidades')
            ->where('habilidade_id', $habCarteiraId)
            ->pluck('papel_id');

        foreach ($papelIds as $papelId) {
            $jaTem = DB::table('papeis_habilidades')
                ->where('papel_id', $papelId)
                ->where('habilidade_id', $habId)
                ->exists();

            if (!$jaTem) {
                DB::table('papeis_habilidades')->insert([
                    'papel_id' => $papelId,
                    'habilidade_id' => $habId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $habId = DB::table('habilidades')->where('nome', 'treinamento_importacao')->value('id');
        if ($habId) {
            DB::table('papeis_habilidades')->where('habilidade_id', $habId)->delete();
            DB::table('habilidades')->where('id', $habId)->delete();
        }
    }
};
