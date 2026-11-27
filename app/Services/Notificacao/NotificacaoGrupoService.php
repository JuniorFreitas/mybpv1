<?php

namespace App\Services\Notificacao;

use App\Helpers\RHHelper;
use App\Models\CentroCusto;
use App\Models\NotificacaoGrupo;
use App\Models\NotificacaoTipo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NotificacaoGrupoService
{
    public function __construct(
        private readonly NotificacaoEngine $engine
    ) {
    }

    /**
     * @param  array{nome: string, ativo?: bool, escopo_cc: string, user_ids?: list<int>, centro_custo_ids?: list<int>}  $dados
     */
    public function criar(int $empresaId, string $tipoCodigo, array $dados): NotificacaoGrupo
    {
        $this->assertTipoEditavel($tipoCodigo);

        return DB::transaction(function () use ($empresaId, $tipoCodigo, $dados) {
            $grupo = NotificacaoGrupo::query()->create([
                'empresa_id' => $empresaId,
                'tipo_codigo' => $tipoCodigo,
                'nome' => $dados['nome'],
                'ativo' => (bool) ($dados['ativo'] ?? true),
                'escopo_cc' => $dados['escopo_cc'],
            ]);

            $this->syncDestinatariosECentros($grupo, $empresaId, $tipoCodigo, $dados);
            $this->engine->invalidarEmpresa($empresaId);

            return $grupo->fresh(['destinatarios:id,nome,login', 'centrosCusto:id,label']);
        });
    }

    /**
     * @param  array{nome: string, ativo?: bool, escopo_cc: string, user_ids?: list<int>, centro_custo_ids?: list<int>}  $dados
     */
    public function atualizar(NotificacaoGrupo $grupo, array $dados): NotificacaoGrupo
    {
        $this->assertTipoEditavel($grupo->tipo_codigo);

        return DB::transaction(function () use ($grupo, $dados) {
            $grupo->update([
                'nome' => $dados['nome'],
                'ativo' => (bool) ($dados['ativo'] ?? $grupo->ativo),
                'escopo_cc' => $dados['escopo_cc'],
            ]);

            $this->syncDestinatariosECentros($grupo, (int) $grupo->empresa_id, $grupo->tipo_codigo, $dados);
            $this->engine->invalidarEmpresa((int) $grupo->empresa_id);

            return $grupo->fresh(['destinatarios:id,nome,login', 'centrosCusto:id,label']);
        });
    }

    public function excluir(NotificacaoGrupo $grupo): void
    {
        $this->assertTipoEditavel($grupo->tipo_codigo);
        $empresaId = (int) $grupo->empresa_id;

        // Soft delete: mantém pivôs para eventual restauração/auditoria.
        DB::transaction(function () use ($grupo) {
            $grupo->delete();
        });

        $this->engine->invalidarEmpresa($empresaId);
    }

    public function alterarAtivo(NotificacaoGrupo $grupo, ?bool $ativo = null): NotificacaoGrupo
    {
        $this->assertTipoEditavel($grupo->tipo_codigo);

        $novo = $ativo === null ? !$grupo->ativo : $ativo;
        $grupo->update(['ativo' => $novo]);
        $this->engine->invalidarEmpresa((int) $grupo->empresa_id);

        return $grupo->fresh();
    }

    /**
     * @param  array{user_ids?: list<int>, centro_custo_ids?: list<int>, escopo_cc: string}  $dados
     */
    private function syncDestinatariosECentros(NotificacaoGrupo $grupo, int $empresaId, string $tipoCodigo, array $dados): void
    {
        $userIds = array_values(array_unique(array_map('intval', $dados['user_ids'] ?? [])));
        $this->assertUsuariosElegiveis($empresaId, $tipoCodigo, $userIds);
        $grupo->destinatarios()->sync($userIds);

        $escopo = $dados['escopo_cc'];
        if ($escopo === NotificacaoTipo::ESCOPO_ESPECIFICOS) {
            $ccIds = array_values(array_unique(array_map('intval', $dados['centro_custo_ids'] ?? [])));
            if ($ccIds === []) {
                throw ValidationException::withMessages([
                    'centro_custo_ids' => ['Selecione ao menos um centro de custo para o escopo específicos.'],
                ]);
            }
            $this->assertCentrosDaEmpresa($empresaId, $ccIds);
            $grupo->centrosCusto()->sync($ccIds);
        } else {
            $grupo->centrosCusto()->detach();
        }
    }

    private function assertTipoEditavel(string $tipoCodigo): void
    {
        $tipo = NotificacaoTipo::query()->where('codigo', $tipoCodigo)->first();
        if (!$tipo || !$tipo->ativo || !$tipo->editavel) {
            throw ValidationException::withMessages([
                'tipo_codigo' => ['Este tipo de notificação não pode ser editado nesta versão.'],
            ]);
        }
    }

    /**
     * @param  list<int>  $userIds
     */
    private function assertUsuariosElegiveis(int $empresaId, string $tipoCodigo, array $userIds): void
    {
        if ($userIds === []) {
            return;
        }

        $tipo = NotificacaoTipo::query()->where('codigo', $tipoCodigo)->first();
        if ($tipo && $tipo->regra_elegibilidade === NotificacaoTipo::REGRA_PRIVILEGIOS_RH) {
            $permitidos = RHHelper::idsUsuariosRhEmpresa($empresaId);
            $invalidos = array_values(array_diff($userIds, $permitidos));
            if ($invalidos !== []) {
                throw ValidationException::withMessages([
                    'user_ids' => ['Um ou mais usuários não possuem privilégio RH ou não pertencem à empresa.'],
                ]);
            }
        }
    }

    /**
     * @param  list<int>  $ccIds
     */
    private function assertCentrosDaEmpresa(int $empresaId, array $ccIds): void
    {
        $count = CentroCusto::query()
            ->withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->whereIn('id', $ccIds)
            ->count();

        if ($count !== count($ccIds)) {
            throw ValidationException::withMessages([
                'centro_custo_ids' => ['Um ou mais centros de custo são inválidos para esta empresa.'],
            ]);
        }
    }
}
