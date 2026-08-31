<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Correções de login duplicado revisadas manualmente durante a auditoria
     * da Fase 1 (contas Administrador/Gestor), categorias A, C e D — ver
     * scratchpad/auditoria_login_duplicado_admin_gestor.csv.
     *
     * Roda ANTES da migration de dedup em massa (100003) de propósito: para
     * alguns grupos (ex: valdenias.franca@cmpcindustrial.com.br, onde as duas
     * contas estavam ativas e sem ultimo_acesso) o critério automático do
     * dedup em massa escolheria um "keeper" diferente do decidido aqui à mão.
     * Rodar esta primeiro garante que a decisão revisada prevaleça.
     *
     * Idempotente: cada UPDATE casa também pelo login original exato, então
     * rodar de novo depois de já aplicado não faz nada (o WHERE não bate mais).
     */
    public function up(): void
    {
        $renames = [
            // Categoria A — duplicata acidental, mesma pessoa (keeper = conta ativa)
            ['id' => 39817, 'login' => 'analicevcn@gmail.com'],
            ['id' => 79417, 'login' => 'benjamin@safemed.com.br'],
            ['id' => 68770, 'login' => 'carolina.oliveira@montisol.com.br'],
            ['id' => 65676, 'login' => 'edson.graca@montisol.com.br'],
            ['id' => 79641, 'login' => 'felipe.fiche@montisol.com.br'],
            ['id' => 79640, 'login' => 'fernando.lima@montisol.com.br'],
            ['id' => 69814, 'login' => 'francoises.bpse@montisol.com.br'],
            ['id' => 60049, 'login' => 'nara@iluminar-ma.com.br'],
            ['id' => 73894, 'login' => 'patricia.bpse@cmpcindustrial.com.br'],
            // ambas ativas, sem ultimo_acesso — keeper = criada 1º (109694)
            ['id' => 109736, 'login' => 'valdenias.franca@cmpcindustrial.com.br'],

            // Categoria C — pessoa diferente colidindo com o dono legítimo do login
            ['id' => 60026, 'login' => 'karleane@iluminar-ma.com.br'],
            ['id' => 112131, 'login' => 'karleane@iluminar-ma.com.br'],
            ['id' => 60958, 'login' => 'gestao.gente@bpse.com.br'],
            ['id' => 72416, 'login' => 'dayna.andrade@montisol.com.br'],

            // Categoria D — email de departamento reaproveitado (keeper = Gestor dono real)
            ['id' => 95398, 'login' => 'bp.rh2@maxtecservicos.com.br'],
            ['id' => 95592, 'login' => 'bp.rh2@maxtecservicos.com.br'],
            ['id' => 95923, 'login' => 'bp.rh2@maxtecservicos.com.br'],
            ['id' => 95927, 'login' => 'bp.rh2@maxtecservicos.com.br'],
            ['id' => 100927, 'login' => 'bp.rh2@maxtecservicos.com.br'],
            ['id' => 100928, 'login' => 'bp.rh2@maxtecservicos.com.br'],
            ['id' => 100932, 'login' => 'bp.rh2@maxtecservicos.com.br'],
        ];

        foreach ($renames as $rename) {
            DB::table('users')
                ->where('id', $rename['id'])
                ->where('login', $rename['login'])
                ->update([
                    'login' => self::suffixLogin($rename['login'], $rename['id']),
                ]);
        }
    }

    /**
     * Insere o sufixo de desambiguação antes do '@' (quando o login tem
     * formato de e-mail), preservando um endereço sintaticamente válido —
     * ex: analicevcn@gmail.com -> analicevcn+dup39817@gmail.com.
     * Sem isso o campo vira algo como "email@dominio.com+dup123", que não é
     * um e-mail válido e pode falhar validação/envio em qualquer fluxo que
     * trate users.login como endereço de e-mail.
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
        // Irreversível de forma segura (voltar reintroduziria a colisão de login).
    }
};
