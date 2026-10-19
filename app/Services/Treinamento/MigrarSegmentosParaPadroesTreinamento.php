<?php

namespace App\Services\Treinamento;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Migra segmentos_treinamento → carteiras/etiquetas globais + padrões por tenant.
 * Spec: docs/PADRAO_TREINAMENTO.md §8
 */
class MigrarSegmentosParaPadroesTreinamento
{
    /**
     * @return array{carteiras:int,etiquetas:int,padroes:int,lotacoes:int,vencimentos:int,admissoes:int,assinaturas:int}
     */
    public function executar(): array
    {
        $stats = [
            'carteiras' => 0,
            'etiquetas' => 0,
            'padroes' => 0,
            'lotacoes' => 0,
            'vencimentos' => 0,
            'admissoes' => 0,
            'assinaturas' => 0,
        ];

        if (!Schema::hasTable('segmentos_treinamento') || !Schema::hasTable('padroes_treinamento')) {
            return $stats;
        }

        $segmentoParaCarteira = [];
        $segmentoParaEtiqueta = [];

        $segmentos = DB::table('segmentos_treinamento')->orderBy('id')->get();

        foreach ($segmentos as $segmento) {
            $config = $this->decodeConfig($segmento->config_carteira ?? null);
            $slugBase = Str::slug((string) $segmento->slug) ?: 'padrao';

            $carteiraId = DB::table('carteiras_treinamento')->whereNull('empresa_id')->where('slug', $slugBase)->value('id');
            if (!$carteiraId) {
                $slug = $this->slugUnicoGlobal('carteiras_treinamento', $slugBase);
                $carteiraId = DB::table('carteiras_treinamento')->insertGetId([
                    'empresa_id' => null,
                    'nome' => $segmento->nome,
                    'slug' => $slug,
                    'ativo' => (bool) $segmento->ativo,
                    'cabecalho_img' => (string) ($config['cabecalho_img'] ?? ''),
                    'verso_img' => (string) ($config['verso_img'] ?? ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $stats['carteiras']++;
            }
            $segmentoParaCarteira[(int) $segmento->id] = (int) $carteiraId;

            $etiquetaId = DB::table('etiquetas_bloqueio')->whereNull('empresa_id')->where('slug', $slugBase)->value('id');
            if (!$etiquetaId) {
                $slugEtiqueta = $this->slugUnicoGlobal('etiquetas_bloqueio', $slugBase);
                $etiquetaId = DB::table('etiquetas_bloqueio')->insertGetId([
                    'empresa_id' => null,
                    'nome' => $segmento->nome,
                    'slug' => $slugEtiqueta,
                    'ativo' => (bool) $segmento->ativo,
                    'ramal_emergencia' => (string) ($config['ramal_emergencia'] ?? '1199'),
                    'bloqueio_texto_nao_use' => (string) ($config['bloqueio_texto_nao_use'] ?? 'NÃO USE, MOVA OU OPERE ENQUANTO ESTA ETIQUETA ESTIVER COLOCADA'),
                    'bloqueio_texto_demissao' => (string) ($config['bloqueio_texto_demissao'] ?? 'QUEM OPERAR O EQUIPAMENTO OU REMOVER A ETIQUETA ESTÁ SUJEITO A DEMISSÃO'),
                    'bloqueio_texto_cuidado' => (string) ($config['bloqueio_texto_cuidado'] ?? 'CUIDADO!'),
                    'bloqueio_texto_homens_trabalhando' => (string) ($config['bloqueio_texto_homens_trabalhando'] ?? 'HOMENS TRABALHANDO NÃO OPERE ESTE EQUIPAMENTO'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $stats['etiquetas']++;
            }
            $segmentoParaEtiqueta[(int) $segmento->id] = (int) $etiquetaId;
        }

        /** @var array<string,int> $mapaPadrao chave "{empresaId}:{segmentoId}" => padraoId */
        $mapaPadrao = [];

        $empresaIds = $this->resolverEmpresaIdsComSegmento();

        foreach ($empresaIds as $empresaId) {
            foreach ($segmentos as $segmento) {
                $segmentoId = (int) $segmento->id;
                if (!$this->empresaUsaSegmento($empresaId, $segmentoId)) {
                    continue;
                }

                $chave = $empresaId . ':' . $segmentoId;
                $slugBase = Str::slug((string) $segmento->slug) ?: 'padrao';
                $padraoId = DB::table('padroes_treinamento')
                    ->where('empresa_id', $empresaId)
                    ->where('slug', $slugBase)
                    ->value('id');

                $temEtiqueta = filter_var(
                    $this->decodeConfig($segmento->config_carteira ?? null)['exibir_etiqueta_bloqueio'] ?? true,
                    FILTER_VALIDATE_BOOLEAN
                );

                if (!$padraoId) {
                    $slugPadrao = $this->slugUnicoEmpresa('padroes_treinamento', $empresaId, $slugBase);
                    $padraoId = DB::table('padroes_treinamento')->insertGetId([
                        'empresa_id' => $empresaId,
                        'nome' => $segmento->nome,
                        'slug' => $slugPadrao,
                        'ativo' => (bool) $segmento->ativo,
                        'tem_carteira' => true,
                        'carteira_treinamento_id' => $segmentoParaCarteira[$segmentoId] ?? null,
                        'tem_etiqueta_bloqueio' => $temEtiqueta,
                        'etiqueta_bloqueio_id' => $temEtiqueta ? ($segmentoParaEtiqueta[$segmentoId] ?? null) : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $stats['padroes']++;
                }

                $mapaPadrao[$chave] = (int) $padraoId;

                $temMatriz = DB::table('padrao_treinamento_lotacao')
                    ->where('padrao_treinamento_id', $padraoId)
                    ->where('lotacao_key', 'matriz')
                    ->exists();

                if (!$temMatriz) {
                    DB::table('padrao_treinamento_lotacao')->insert([
                        'padrao_treinamento_id' => $padraoId,
                        'is_matriz' => true,
                        'cliente_filial_id' => null,
                        'lotacao_key' => 'matriz',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $stats['lotacoes']++;
                }
            }
        }

        // Vencimentos do segmento → pivot do padrão da mesma empresa
        $vencimentos = DB::table('vencimentos')
            ->whereNotNull('segmento_treinamento_id')
            ->whereNotNull('empresa_id')
            ->select(['id', 'empresa_id', 'segmento_treinamento_id'])
            ->get();

        foreach ($vencimentos as $vencimento) {
            $chave = (int) $vencimento->empresa_id . ':' . (int) $vencimento->segmento_treinamento_id;
            $padraoId = $mapaPadrao[$chave] ?? null;
            if (!$padraoId) {
                continue;
            }
            $exists = DB::table('padrao_treinamento_vencimento')
                ->where('padrao_treinamento_id', $padraoId)
                ->where('vencimento_id', $vencimento->id)
                ->exists();
            if (!$exists) {
                DB::table('padrao_treinamento_vencimento')->insert([
                    'padrao_treinamento_id' => $padraoId,
                    'vencimento_id' => $vencimento->id,
                ]);
                $stats['vencimentos']++;
            }
        }

        // Vencimentos genéricos (segmento null) → todos os padrões da empresa
        $genericos = DB::table('vencimentos')
            ->whereNull('segmento_treinamento_id')
            ->whereNotNull('empresa_id')
            ->select(['id', 'empresa_id'])
            ->get();

        foreach ($genericos as $vencimento) {
            $padroes = DB::table('padroes_treinamento')
                ->where('empresa_id', $vencimento->empresa_id)
                ->pluck('id');
            foreach ($padroes as $padraoId) {
                $exists = DB::table('padrao_treinamento_vencimento')
                    ->where('padrao_treinamento_id', $padraoId)
                    ->where('vencimento_id', $vencimento->id)
                    ->exists();
                if (!$exists) {
                    DB::table('padrao_treinamento_vencimento')->insert([
                        'padrao_treinamento_id' => $padraoId,
                        'vencimento_id' => $vencimento->id,
                    ]);
                    $stats['vencimentos']++;
                }
            }
        }

        // Default por empresa (slug alumar ou primeiro padrão criado)
        /** @var array<int,int> $padraoDefaultPorEmpresa */
        $padraoDefaultPorEmpresa = [];
        foreach ($mapaPadrao as $chave => $padraoId) {
            [$empresaIdMapa] = array_map('intval', explode(':', $chave, 2));
            if (!isset($padraoDefaultPorEmpresa[$empresaIdMapa])) {
                $padraoDefaultPorEmpresa[$empresaIdMapa] = $padraoId;
            }
        }
        foreach ($empresaIds as $empresaId) {
            $alumarPadrao = DB::table('padroes_treinamento')
                ->where('empresa_id', $empresaId)
                ->where('slug', 'alumar')
                ->value('id');
            if ($alumarPadrao) {
                $padraoDefaultPorEmpresa[$empresaId] = (int) $alumarPadrao;
                continue;
            }
            if (!isset($padraoDefaultPorEmpresa[$empresaId])) {
                $primeiro = DB::table('padroes_treinamento')
                    ->where('empresa_id', $empresaId)
                    ->orderBy('id')
                    ->value('id');
                if ($primeiro) {
                    $padraoDefaultPorEmpresa[$empresaId] = (int) $primeiro;
                }
            }
        }

        // Admissões: todas sem padrao (com ou sem segmento) → padrão correspondente
        $admissoes = DB::table('admissoes as a')
            ->join('feedback_curriculos as f', 'f.id', '=', 'a.feedback_id')
            ->whereNull('a.padrao_treinamento_id')
            ->whereNull('a.deleted_at')
            ->whereNull('f.deleted_at')
            ->select(['a.id', 'f.empresa_id', 'a.segmento_treinamento_id'])
            ->get();

        foreach ($admissoes as $admissao) {
            $empresaId = (int) $admissao->empresa_id;
            $padraoId = null;
            if (!empty($admissao->segmento_treinamento_id)) {
                $chave = $empresaId . ':' . (int) $admissao->segmento_treinamento_id;
                $padraoId = $mapaPadrao[$chave] ?? null;
            }
            if (!$padraoId) {
                $padraoId = $padraoDefaultPorEmpresa[$empresaId] ?? null;
            }
            if (!$padraoId) {
                continue;
            }
            DB::table('admissoes')->where('id', $admissao->id)->update([
                'padrao_treinamento_id' => $padraoId,
            ]);
            $stats['admissoes']++;
        }

        // Assinaturas por segmento → padrão da mesma empresa
        if (Schema::hasColumn('carteira_assinaturas', 'padrao_treinamento_id')) {
            $assinaturas = DB::table('carteira_assinaturas')
                ->whereNotNull('segmento_treinamento_id')
                ->whereNull('padrao_treinamento_id')
                ->select(['id', 'empresa_id', 'segmento_treinamento_id'])
                ->get();

            foreach ($assinaturas as $assinatura) {
                $chave = (int) $assinatura->empresa_id . ':' . (int) $assinatura->segmento_treinamento_id;
                $padraoId = $mapaPadrao[$chave] ?? null;
                if (!$padraoId) {
                    continue;
                }
                DB::table('carteira_assinaturas')->where('id', $assinatura->id)->update([
                    'padrao_treinamento_id' => $padraoId,
                ]);
                $stats['assinaturas']++;
            }
        }

        return $stats;
    }

    private function decodeConfig($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * @return list<int>
     */
    private function resolverEmpresaIdsComSegmento(): array
    {
        $ids = collect();

        if (Schema::hasTable('cliente_segmento_treinamento')) {
            $ids = $ids->merge(DB::table('cliente_segmento_treinamento')->pluck('cliente_id'));
        }

        $ids = $ids->merge(
            DB::table('vencimentos')->whereNotNull('segmento_treinamento_id')->whereNotNull('empresa_id')->pluck('empresa_id')
        );

        $ids = $ids->merge(
            DB::table('admissoes as a')
                ->join('feedback_curriculos as f', 'f.id', '=', 'a.feedback_id')
                ->whereNull('a.deleted_at')
                ->whereNull('f.deleted_at')
                ->pluck('f.empresa_id')
        );

        // Se ninguém tem pivot, ainda assim cria padrão ALUMAR para empresas com vencimentos
        if ($ids->isEmpty()) {
            $ids = $ids->merge(DB::table('vencimentos')->whereNotNull('empresa_id')->pluck('empresa_id'));
        }

        return $ids->filter()->unique()->map(fn ($id) => (int) $id)->values()->all();
    }

    private function empresaUsaSegmento(int $empresaId, int $segmentoId): bool
    {
        if (Schema::hasTable('cliente_segmento_treinamento')) {
            $temPivot = DB::table('cliente_segmento_treinamento')->where('cliente_id', $empresaId)->exists();
            if ($temPivot) {
                return DB::table('cliente_segmento_treinamento')
                    ->where('cliente_id', $empresaId)
                    ->where('segmento_treinamento_id', $segmentoId)
                    ->exists();
            }
        }

        $usaEmVencimento = DB::table('vencimentos')
            ->where('empresa_id', $empresaId)
            ->where('segmento_treinamento_id', $segmentoId)
            ->exists();

        $usaEmAdmissao = DB::table('admissoes as a')
            ->join('feedback_curriculos as f', 'f.id', '=', 'a.feedback_id')
            ->where('f.empresa_id', $empresaId)
            ->where('a.segmento_treinamento_id', $segmentoId)
            ->whereNull('a.deleted_at')
            ->whereNull('f.deleted_at')
            ->exists();

        if ($usaEmVencimento || $usaEmAdmissao) {
            return true;
        }

        // Sem pivot e sem uso explícito: só cria o padrão default (alumar) se a empresa tem qualquer vencimento
        $slug = DB::table('segmentos_treinamento')->where('id', $segmentoId)->value('slug');
        if ($slug === 'alumar') {
            return DB::table('vencimentos')->where('empresa_id', $empresaId)->exists()
                || DB::table('admissoes as a')
                    ->join('feedback_curriculos as f', 'f.id', '=', 'a.feedback_id')
                    ->where('f.empresa_id', $empresaId)
                    ->whereNull('a.deleted_at')
                    ->whereNull('f.deleted_at')
                    ->exists();
        }

        return false;
    }

    private function slugUnicoGlobal(string $table, string $slugBase): string
    {
        $slug = Str::slug($slugBase) ?: 'padrao';
        $candidate = $slug;
        $i = 2;
        while (DB::table($table)->whereNull('empresa_id')->where('slug', $candidate)->exists()) {
            $candidate = $slug . '-' . $i;
            $i++;
        }

        return Str::limit($candidate, 50, '');
    }

    private function slugUnicoEmpresa(string $table, int $empresaId, string $slugBase): string
    {
        $slug = Str::slug($slugBase) ?: 'padrao';
        $candidate = $slug;
        $i = 2;
        while (DB::table($table)->where('empresa_id', $empresaId)->where('slug', $candidate)->exists()) {
            $candidate = $slug . '-' . $i;
            $i++;
        }

        return Str::limit($candidate, 50, '');
    }
}
