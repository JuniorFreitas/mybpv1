<?php

namespace App\Services\ApiExterna;

use App\Models\Admissao;
use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pós-admissão: colaboradores admitidos (ativos/demitidos) + entrevista de desligamento.
 */
class PosAdmissaoApiService
{
    public function __construct(
        private readonly AdmissaoApiAssembler $assembler
    ) {
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function listar(int $empresaId, int $page, int $perPage, array $filtros = []): LengthAwarePaginator
    {
        $query = DB::table('feedback_curriculos as fc')
            ->join('admissoes as a', function ($join) {
                $join->on('a.feedback_id', '=', 'fc.id')->whereNull('a.deleted_at');
            })
            ->leftJoin('curriculos as c', function ($join) {
                $join->on('c.id', '=', 'fc.curriculo_id')->whereNull('c.deleted_at');
            })
            ->leftJoin('demissaos as d', 'd.feedback_id', '=', 'fc.id')
            ->where('fc.empresa_id', $empresaId)
            ->whereNull('fc.deleted_at')
            ->where('a.status', Admissao::STATUS_ADMISSAO_ADMITIDO)
            ->select([
                'fc.id',
                'c.nome',
                'c.cpf',
                'a.matricula',
                'a.cargo',
                'a.data_admissao',
                'd.id as demissao_id',
                'd.data_desmobilizacao',
            ])
            ->orderByDesc('fc.id');

        if (Schema::hasTable('entrevista_desligamentos')) {
            $query->leftJoin('entrevista_desligamentos as ed', 'ed.feedback_id', '=', 'fc.id')
                ->addSelect('ed.id as entrevista_id');
        }

        $this->aplicarFiltros($query, $filtros);

        return $query->paginate($perPage, ['*'], 'page', $page)
            ->through(function ($row) {
                $pid = ApiExternaId::encode((int) $row->id);

                return [
                    'public_id' => $pid,
                    'nome' => $row->nome,
                    'cpf' => ApiExternaResponse::mascararCpf($row->cpf),
                    'matricula' => $row->matricula,
                    'cargo' => $row->cargo,
                    'data_admissao' => $row->data_admissao,
                    'demitido' => (bool) ($row->demissao_id ?? null),
                    'data_desmobilizacao' => $row->data_desmobilizacao ?? null,
                    'tem_entrevista_desligamento' => (bool) ($row->entrevista_id ?? null),
                    'links' => [
                        'self' => url('/api/v3/pos-admissao/' . $pid),
                        'processo' => url('/api/v3/processos/' . $pid),
                        'entrevista_desligamento' => url('/api/v3/pos-admissao/' . $pid . '/entrevista-desligamento'),
                    ],
                ];
            });
    }

    public function detalhe(int $empresaId, int $feedbackId): ?array
    {
        $existe = DB::table('feedback_curriculos as fc')
            ->join('admissoes as a', function ($join) {
                $join->on('a.feedback_id', '=', 'fc.id')->whereNull('a.deleted_at');
            })
            ->where('fc.empresa_id', $empresaId)
            ->where('fc.id', $feedbackId)
            ->whereNull('fc.deleted_at')
            ->where('a.status', Admissao::STATUS_ADMISSAO_ADMITIDO)
            ->exists();

        if (! $existe) {
            return null;
        }

        $payload = $this->assembler->montarUm($empresaId, $feedbackId, completo: true);
        if (! $payload) {
            return null;
        }

        $pid = ApiExternaId::encode($feedbackId);
        $entrevista = $this->entrevistaDesligamento($empresaId, $feedbackId);
        $payload['entrevista_desligamento'] = $entrevista['entrevista'] ?? null;
        $payload['links'] = array_merge($payload['links'] ?? [], [
            'pos_admissao' => url('/api/v3/pos-admissao/' . $pid),
            'entrevista_desligamento' => url('/api/v3/pos-admissao/' . $pid . '/entrevista-desligamento'),
        ]);

        return $payload;
    }

    public function entrevistaDesligamento(int $empresaId, int $feedbackId): ?array
    {
        $existe = DB::table('feedback_curriculos')
            ->where('empresa_id', $empresaId)
            ->where('id', $feedbackId)
            ->whereNull('deleted_at')
            ->exists();

        if (! $existe) {
            return null;
        }

        $pid = ApiExternaId::encode($feedbackId);
        $entrevista = null;

        if (Schema::hasTable('entrevista_desligamentos')) {
            $row = DB::table('entrevista_desligamentos')
                ->where('feedback_id', $feedbackId)
                ->first();

            if ($row) {
                // Subconjunto sanitizado (LGPD): sem textos livres sensíveis completos.
                $entrevista = [
                    'public_id' => ApiExternaId::encode((int) $row->id),
                    'data_entrevista' => $row->data_entrevista ?? null,
                    'motivo' => $row->motivo ?? null,
                    'trabalharia_novamente' => $row->trabalharia_novamente ?? null,
                    'pode_voltar' => isset($row->pode_voltar) ? (bool) $row->pode_voltar : null,
                    'quem_entrevistou' => $row->quem_entrevistou ?? null,
                    'preenchido_por' => $row->preenchido_por ?? null,
                    'tem_parecer' => ! empty($row->parecer_entrevistador),
                    'tem_comentarios' => ! empty($row->comentarios),
                ];
            }
        }

        return [
            'public_id' => $pid,
            'entrevista' => $entrevista,
            'links' => [
                'self' => url('/api/v3/pos-admissao/' . $pid . '/entrevista-desligamento'),
                'pos_admissao' => url('/api/v3/pos-admissao/' . $pid),
                'processo' => url('/api/v3/processos/' . $pid),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    private function aplicarFiltros($query, array $filtros): void
    {
        if (! empty($filtros['q'])) {
            $q = trim((string) $filtros['q']);
            $digits = preg_replace('/\D+/', '', $q) ?: '';
            $query->where(function ($inner) use ($q, $digits) {
                $inner->where('c.nome', 'like', '%' . $q . '%')
                    ->orWhere('a.matricula', 'like', '%' . $q . '%');
                if ($digits !== '') {
                    $inner->orWhere('c.cpf', 'like', '%' . $digits . '%');
                }
            });
        }

        if (array_key_exists('demitido', $filtros) && $filtros['demitido'] !== null) {
            if ($filtros['demitido']) {
                $query->whereNotNull('d.id');
            } else {
                $query->whereNull('d.id');
            }
        }

        if (array_key_exists('com_entrevista', $filtros) && $filtros['com_entrevista'] !== null
            && Schema::hasTable('entrevista_desligamentos')) {
            if ($filtros['com_entrevista']) {
                $query->whereNotNull('ed.id');
            } else {
                $query->whereNull('ed.id');
            }
        }

        if (! empty($filtros['vaga_id'])) {
            $query->where('fc.vaga_id', (int) $filtros['vaga_id']);
        }
    }
}
