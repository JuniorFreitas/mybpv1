<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Habilidades do módulo Saúde Ocupacional.
 *
 * - saude_ocupacional: menu + Painel (perfil RH). Liberada para quem já cuida de exames/ASO.
 * - saude_ocupacional_configuracao: Configurações do módulo. Liberada para quem já cadastra exames.
 * - saude_ocupacional_dados_sensiveis: CID, laudos, atestados (perfil Saúde/SESMT).
 *   NÃO é liberada automaticamente: o cliente decide quem recebe (LGPD art. 11).
 */
return new class extends Migration
{
    private const HABILIDADES = [
        'saude_ocupacional' => 'Acessar menu Saúde Ocupacional e Painel',
        'saude_ocupacional_configuracao' => 'Acessar Configurações da Saúde Ocupacional',
        'saude_ocupacional_dados_sensiveis' => 'Ver dados sensíveis de saúde (CID, laudos, atestados)',
    ];

    /** habilidade nova => habilidades atuais que dão direito a ela */
    private const LIBERAR_PARA = [
        'saude_ocupacional' => ['admissao_controle_exames', 'relatorio_asos', 'cadastro_empresa_exame'],
        'saude_ocupacional_configuracao' => ['cadastro_empresa_exame'],
    ];

    public function up(): void
    {
        $ids = [];
        foreach (self::HABILIDADES as $nome => $descricao) {
            $ids[$nome] = DB::table('habilidades')->where('nome', $nome)->value('id')
                ?? DB::table('habilidades')->insertGetId(['nome' => $nome, 'descricao' => $descricao]);
        }

        foreach (self::LIBERAR_PARA as $nova => $origens) {
            $origemIds = DB::table('habilidades')->whereIn('nome', $origens)->pluck('id');
            if ($origemIds->isEmpty()) {
                continue;
            }

            $papelIds = DB::table('papeis_habilidades')
                ->whereIn('habilidade_id', $origemIds)
                ->distinct()
                ->pluck('papel_id');

            $jaTem = DB::table('papeis_habilidades')
                ->where('habilidade_id', $ids[$nova])
                ->pluck('papel_id')
                ->all();

            $inserir = $papelIds->diff($jaTem)
                ->map(fn ($papelId) => ['papel_id' => $papelId, 'habilidade_id' => $ids[$nova]])
                ->values()
                ->all();

            foreach (array_chunk($inserir, 500) as $lote) {
                DB::table('papeis_habilidades')->insert($lote);
            }
        }
    }

    public function down(): void
    {
        $ids = DB::table('habilidades')->whereIn('nome', array_keys(self::HABILIDADES))->pluck('id');
        if ($ids->isEmpty()) {
            return;
        }

        DB::table('papeis_habilidades')->whereIn('habilidade_id', $ids)->delete();
        DB::table('habilidades')->whereIn('id', $ids)->delete();
    }
};
