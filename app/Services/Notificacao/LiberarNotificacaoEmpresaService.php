<?php

namespace App\Services\Notificacao;

use App\Helpers\RHHelper;
use App\Models\NotificacaoGrupo;
use App\Models\NotificacaoTipo;
use Illuminate\Support\Facades\DB;

/**
 * Libera a tela Configurações › Notificações e, se necessário,
 * cria o grupo inicial "RH Geral" (aprovacao_rh / escopo todos).
 */
class LiberarNotificacaoEmpresaService
{
    public const HABILIDADE = 'configuracao_notificacoes';

    public const GRUPO_PADRAO = 'RH Geral';

    /** @var list<string> */
    private const PAPEIS_ANCORA = [
        'configuracao_papel',
        'configuracao_whatsapp',
        'privilegio_gestao_rh',
        'privilegio_aprovar_por_rh',
        'privilegio_aprovar_rh',
    ];

    public function __construct(
        private readonly NotificacaoEngine $engine
    ) {
    }

    /**
     * @param  list<int>|null  $papelIds  Se null, detecta papéis da empresa com âncoras de config/RH.
     * @return array{
     *   habilidade_id: int,
     *   papeis_liberados: list<int>,
     *   grupo_id: int|null,
     *   grupo_criado: bool,
     *   destinatarios: int
     * }
     */
    public function liberar(int $empresaId, ?array $papelIds = null, bool $criarGrupoPadrao = true): array
    {
        $habilidadeId = $this->garantirHabilidade();
        $papeis = $papelIds !== null && $papelIds !== []
            ? array_values(array_unique(array_map('intval', $papelIds)))
            : $this->detectarPapeisAlvo($empresaId);

        $liberados = [];
        foreach ($papeis as $papelId) {
            if ($papelId <= 0) {
                continue;
            }
            $exists = DB::table('papeis_habilidades')
                ->where('papel_id', $papelId)
                ->where('habilidade_id', $habilidadeId)
                ->exists();
            if (!$exists) {
                DB::table('papeis_habilidades')->insert([
                    'papel_id' => $papelId,
                    'habilidade_id' => $habilidadeId,
                ]);
                $liberados[] = $papelId;
            }
        }

        $grupoId = null;
        $grupoCriado = false;
        $destinatarios = 0;

        if ($criarGrupoPadrao) {
            [$grupoId, $grupoCriado, $destinatarios] = $this->garantirGrupoRhGeral($empresaId);
        }

        $this->engine->invalidarEmpresa($empresaId);
        RHHelper::invalidarCache($empresaId);

        return [
            'habilidade_id' => $habilidadeId,
            'papeis_liberados' => $liberados,
            'grupo_id' => $grupoId,
            'grupo_criado' => $grupoCriado,
            'destinatarios' => $destinatarios,
        ];
    }

    private function garantirHabilidade(): int
    {
        $id = DB::table('habilidades')->where('nome', self::HABILIDADE)->value('id');
        if ($id) {
            return (int) $id;
        }

        return (int) DB::table('habilidades')->insertGetId([
            'nome' => self::HABILIDADE,
            'descricao' => 'Configurar grupos de destinatários de notificações (menu Configurações)',
        ]);
    }

    /**
     * @return list<int>
     */
    private function detectarPapeisAlvo(int $empresaId): array
    {
        $usados = DB::table('users')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->whereNotNull('grupo_id')
            ->distinct()
            ->pluck('grupo_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($usados === []) {
            return DB::table('papeis')
                ->where('empresa_id', $empresaId)
                ->where('ativo', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return DB::table('papeis_habilidades as ph')
            ->join('habilidades as h', 'h.id', '=', 'ph.habilidade_id')
            ->whereIn('ph.papel_id', $usados)
            ->whereIn('h.nome', self::PAPEIS_ANCORA)
            ->distinct()
            ->pluck('ph.papel_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return array{0: int|null, 1: bool, 2: int}
     */
    private function garantirGrupoRhGeral(int $empresaId): array
    {
        $tipoOk = DB::table('notificacao_tipos')
            ->where('codigo', NotificacaoTipo::APROVACAO_RH)
            ->where('ativo', true)
            ->exists();
        if (!$tipoOk) {
            return [null, false, 0];
        }

        // withoutGlobalScopes remove SoftDeletes: prioriza ativo e restaura soft-deleted se existir.
        $grupo = NotificacaoGrupo::query()
            ->withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('tipo_codigo', NotificacaoTipo::APROVACAO_RH)
            ->where('nome', self::GRUPO_PADRAO)
            ->orderByRaw('CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END')
            ->first();

        $criado = false;
        if (!$grupo) {
            $grupo = NotificacaoGrupo::query()->withoutGlobalScopes()->create([
                'empresa_id' => $empresaId,
                'tipo_codigo' => NotificacaoTipo::APROVACAO_RH,
                'nome' => self::GRUPO_PADRAO,
                'ativo' => true,
                'escopo_cc' => NotificacaoTipo::ESCOPO_TODOS,
            ]);
            $criado = true;
        } elseif ($grupo->trashed()) {
            $grupo->restore();
            $grupo->forceFill([
                'ativo' => true,
                'escopo_cc' => NotificacaoTipo::ESCOPO_TODOS,
            ])->save();
        }

        $rhIds = RHHelper::idsUsuariosRhEmpresa($empresaId);
        if ($rhIds !== []) {
            $atuais = DB::table('notificacao_grupo_destinatarios')
                ->where('grupo_id', $grupo->id)
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->all();
            $novos = array_values(array_diff($rhIds, $atuais));
            foreach ($novos as $userId) {
                DB::table('notificacao_grupo_destinatarios')->insert([
                    'grupo_id' => $grupo->id,
                    'user_id' => $userId,
                ]);
            }
        }

        $total = (int) DB::table('notificacao_grupo_destinatarios')
            ->where('grupo_id', $grupo->id)
            ->count();

        return [(int) $grupo->id, $criado, $total];
    }
}
