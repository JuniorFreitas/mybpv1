<?php

namespace App\Services\ApiExterna;

use App\Models\Admissao;
use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Histórico de admissão (tela Histórico): listagem + nested medidas/afastamentos.
 * Reutiliza AdmissaoApiAssembler no detalhe.
 */
class HistoricoApiService
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
                'a.funcao',
                'a.tipo_admissao',
                'a.data_admissao',
                'a.centro_custo_id',
                'd.id as demissao_id',
                'd.data_desmobilizacao',
            ])
            ->orderByDesc('a.data_admissao');

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
                    'funcao' => $row->funcao,
                    'tipo_admissao' => $row->tipo_admissao,
                    'data_admissao' => $row->data_admissao,
                    'centro_custo_public_id' => ApiExternaId::encode($row->centro_custo_id),
                    'demitido' => (bool) $row->demissao_id,
                    'data_desmobilizacao' => $row->data_desmobilizacao,
                    'links' => [
                        'self' => url('/api/v3/historico/' . $pid),
                        'processo' => url('/api/v3/processos/' . $pid),
                        'medidas' => url('/api/v3/historico/' . $pid . '/medidas'),
                        'afastamentos' => url('/api/v3/historico/' . $pid . '/afastamentos'),
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
            ->exists();

        if (! $existe) {
            return null;
        }

        $payload = $this->assembler->montarUm($empresaId, $feedbackId, completo: true);
        if (! $payload) {
            return null;
        }

        $pid = ApiExternaId::encode($feedbackId);
        $payload['links'] = array_merge($payload['links'] ?? [], [
            'historico' => url('/api/v3/historico/' . $pid),
            'medidas' => url('/api/v3/historico/' . $pid . '/medidas'),
            'afastamentos' => url('/api/v3/historico/' . $pid . '/afastamentos'),
        ]);

        return $payload;
    }

    public function medidas(int $empresaId, int $feedbackId): ?array
    {
        if (! $this->pertence($empresaId, $feedbackId)) {
            return null;
        }

        $pid = ApiExternaId::encode($feedbackId);
        $itens = [];

        if (Schema::hasTable('medida_administrativas')) {
            $itens = DB::table('medida_administrativas')
                ->where('feedback_id', $feedbackId)
                ->whereNull('deleted_at')
                ->select(['id', 'tipo', 'definicao', 'causa', 'data_solicitacao', 'data_retorno', 'solicitante'])
                ->orderByDesc('id')
                ->get()
                ->map(fn ($m) => [
                    'public_id' => ApiExternaId::encode((int) $m->id),
                    'tipo' => $m->tipo,
                    'definicao' => $m->definicao,
                    'causa' => $m->causa,
                    'data_solicitacao' => $m->data_solicitacao,
                    'data_retorno' => $m->data_retorno,
                    'solicitante' => $m->solicitante,
                    'links' => [
                        'self' => url('/api/v3/medidas-administrativas/' . ApiExternaId::encode((int) $m->id)),
                    ],
                ])
                ->all();
        }

        return [
            'public_id' => $pid,
            'medidas' => $itens,
            'links' => [
                'self' => url('/api/v3/historico/' . $pid . '/medidas'),
                'historico' => url('/api/v3/historico/' . $pid),
            ],
        ];
    }

    public function afastamentos(int $empresaId, int $feedbackId): ?array
    {
        if (! $this->pertence($empresaId, $feedbackId)) {
            return null;
        }

        $pid = ApiExternaId::encode($feedbackId);
        $itens = [];
        $tabela = Schema::hasTable('afastamentos')
            ? 'afastamentos'
            : (Schema::hasTable('afastamento_feedbacks') ? 'afastamento_feedbacks' : null);

        if ($tabela) {
            $itens = DB::table($tabela)
                ->where('feedback_id', $feedbackId)
                ->select(['id', 'motivo', 'data_inicio', 'data_fim', 'observacao'])
                ->orderByDesc('id')
                ->get()
                ->map(fn ($a) => [
                    'public_id' => ApiExternaId::encode((int) $a->id),
                    'motivo' => $a->motivo,
                    'data_inicio' => $a->data_inicio,
                    'data_fim' => $a->data_fim,
                    'observacao' => $a->observacao,
                ])
                ->all();
        }

        return [
            'public_id' => $pid,
            'afastamentos' => $itens,
            'links' => [
                'self' => url('/api/v3/historico/' . $pid . '/afastamentos'),
                'historico' => url('/api/v3/historico/' . $pid),
            ],
        ];
    }

    private function pertence(int $empresaId, int $feedbackId): bool
    {
        return DB::table('feedback_curriculos')
            ->where('empresa_id', $empresaId)
            ->where('id', $feedbackId)
            ->whereNull('deleted_at')
            ->exists();
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
                    ->orWhere('a.matricula', 'like', '%' . $q . '%')
                    ->orWhere('a.cargo', 'like', '%' . $q . '%');
                if ($digits !== '') {
                    $inner->orWhere('c.cpf', 'like', '%' . $digits . '%');
                }
            });
        }

        if (! empty($filtros['cpf'])) {
            $cpf = preg_replace('/\D+/', '', (string) $filtros['cpf']) ?: '';
            if ($cpf !== '') {
                $query->where('c.cpf', $cpf);
            }
        }

        if (! empty($filtros['matricula'])) {
            $query->where('a.matricula', 'like', '%' . $filtros['matricula'] . '%');
        }

        if (! empty($filtros['cargo'])) {
            $query->where('a.cargo', 'like', '%' . $filtros['cargo'] . '%');
        }

        if (! empty($filtros['tipo_admissao'])) {
            $query->where('a.tipo_admissao', $filtros['tipo_admissao']);
        }

        if (! empty($filtros['centro_custo_id'])) {
            $query->where('a.centro_custo_id', (int) $filtros['centro_custo_id']);
        }

        if (array_key_exists('demitido', $filtros) && $filtros['demitido'] !== null) {
            if ($filtros['demitido']) {
                $query->whereNotNull('d.id');
            } else {
                $query->whereNull('d.id');
            }
        }
    }
}
