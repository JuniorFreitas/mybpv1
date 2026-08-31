<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Dedup em massa do restante das colisões de users.login (majoritariamente
     * Candidato/Funcionario com placeholders tipo naotem@gmail.com,
     * dp@empresa.com.br compartilhado etc — ver auditoria da Fase 1).
     *
     * Sem revisão individual: mantém 1 "keeper" por grupo de login (ativo com
     * ultimo_acesso mais recente; sem isso, ativo mais recente por created_at;
     * sem nenhum ativo, o created_at mais recente) e renomeia os demais para
     * um login sintético único, preservando toda a linha/histórico.
     */
    public function up(): void
    {
        $duplicatedLogins = DB::table('users')
            ->select('login')
            ->whereNotNull('login')
            ->where('login', '<>', '')
            ->groupBy('login')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('login');

        foreach ($duplicatedLogins->chunk(200) as $loginChunk) {
            $rows = DB::table('users')
                ->select('id', 'login', 'ativo', 'ultimo_acesso', 'created_at')
                ->whereIn('login', $loginChunk->all())
                ->get()
                ->groupBy('login');

            foreach ($rows as $group) {
                $sorted = $group->sortByDesc(function ($row) {
                    return [
                        (int) $row->ativo,
                        $row->ultimo_acesso ?? '0000-00-00',
                        $row->created_at ?? '0000-00-00',
                    ];
                })->values();

                foreach ($sorted->slice(1) as $loser) {
                    DB::table('users')
                        ->where('id', $loser->id)
                        ->update([
                            'login' => self::suffixLogin($loser->login, $loser->id),
                        ]);
                }
            }
        }
    }

    /**
     * Insere o sufixo de desambiguação antes do '@' (quando o login tem
     * formato de e-mail), preservando um endereço sintaticamente válido —
     * ex: joao@gmail.com -> joao+dup12345@gmail.com. Sem isso o campo vira
     * "joao@gmail.com+dup12345", que não é um e-mail válido.
     */
    private static function suffixLogin(string $login, int $id): string
    {
        $posicaoArroba = strrpos($login, '@');

        if ($posicaoArroba === false) {
            return $login . '+dup' . $id;
        }

        return substr($login, 0, $posicaoArroba)
            . '+dup' . $id
            . substr($login, $posicaoArroba);
    }

    public function down(): void
    {
        // Irreversível de forma segura (não há como recuperar qual conta
        // "perdeu" o login original sem perder a garantia de unicidade).
    }
};
