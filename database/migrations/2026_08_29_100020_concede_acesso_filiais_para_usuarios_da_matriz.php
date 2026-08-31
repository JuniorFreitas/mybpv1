<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Usuários Administrador/Gestor com acesso ativo à empresa-mãe ganham
     * acesso também à filial-empresa nova, com o mesmo papel de hoje
     * (papel_id nulo cai no fallback de sempre — sem mudança de permissão
     * percebida, só passa a poder trocar pra lá). Restrito a
     * Administrador/Gestor de propósito: são os únicos tipos que usam o
     * painel interno (g/) onde o seletor de empresa aparece — conceder pra
     * Candidato/Funcionario (que na MONTISOL sozinha somam ~25 mil contas)
     * não faz sentido nenhum e estourou o limite de placeholders do MySQL
     * na primeira tentativa. Ajuste fino de papel por filial fica pra
     * depois, sob demanda.
     */
    public function up(): void
    {
        $filiais = DB::table('cliente_filials')
            ->whereNotNull('empresa_nova_id')
            ->select('empresa_id', 'empresa_nova_id')
            ->get();

        foreach ($filiais as $filial) {
            $vinculos = DB::table('user_empresas')
                ->join('users', 'users.id', '=', 'user_empresas.user_id')
                ->where('user_empresas.empresa_id', $filial->empresa_id)
                ->where('user_empresas.ativo', true)
                ->whereIn('users.tipo', ['Administrador', 'Gestor'])
                ->pluck('user_empresas.user_id');

            if ($vinculos->isEmpty()) {
                continue;
            }

            $agora = now();
            foreach ($vinculos->chunk(500) as $chunk) {
                $registros = $chunk->map(fn ($userId) => [
                    'user_id' => $userId,
                    'empresa_id' => $filial->empresa_nova_id,
                    'papel_id' => null,
                    'ativo' => true,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ])->all();

                DB::table('user_empresas')->insertOrIgnore($registros);
            }
        }
    }

    public function down(): void
    {
        $novosIds = DB::table('cliente_filials')->whereNotNull('empresa_nova_id')->pluck('empresa_nova_id');
        DB::table('user_empresas')->whereIn('empresa_id', $novosIds)->delete();
    }
};
