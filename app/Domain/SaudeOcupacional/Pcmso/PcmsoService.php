<?php

namespace App\Domain\SaudeOcupacional\Pcmso;

use App\Domain\SaudeOcupacional\Colaboradores\ColaboradoresAtivosQuery;
use App\Domain\SaudeOcupacional\Painel\PainelCache;
use App\Domain\SaudeOcupacional\Pgr\PgrVigenteRepositorio;
use App\Domain\SaudeOcupacional\Pgr\RiscosCatalogoService;
use App\Domain\SaudeOcupacional\Regras\Periodicidade;
use App\Domain\SaudeOcupacional\Regras\TravaLegalNr7;
use App\Models\ExameTipo;
use App\Models\PcmsoVersao;
use App\Models\PcmsoVersaoExame;
use App\Models\SaudeMedicoVinculo;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use MasterTag\DataHora;

/**
 * PCMSO (NR-7) por programa: versões (rascunho → vigente → arquivado), médico coordenador,
 * exames complementares por cargo e tipo de ASO, sugeridos a partir dos riscos do PGR vigente.
 *
 * `pcmsos` (já existente) é o programa — em empresas de terceirização, um por contrato/cliente.
 */
class PcmsoService
{
    public function __construct(
        private readonly ExamesCatalogoService $exames,
        private readonly PgrVigenteRepositorio $pgr,
        private readonly RiscosCatalogoService $riscos,
        private readonly TravaLegalNr7 $trava
    ) {
    }

    /**
     * Programas da empresa com a versão vigente, rascunho e uso em encaminhamentos.
     *
     * @return list<array<string, mixed>>
     */
    public function listarProgramas(int $empresaId, ?CarbonImmutable $hoje = null): array
    {
        $hoje ??= CarbonImmutable::today();

        $versoes = PcmsoVersao::query()
            ->where('empresa_id', $empresaId)
            ->whereIn('status', [PcmsoVersao::STATUS_VIGENTE, PcmsoVersao::STATUS_RASCUNHO])
            ->get()
            ->groupBy('pcmso_id');

        $uso = DB::table('exame_funcionarios')
            ->where('empresa_id', $empresaId)
            ->where('pcmso', true)
            ->groupBy('pcmso_id')
            ->selectRaw('pcmso_id, COUNT(*) as total')
            ->pluck('total', 'pcmso_id');

        return DB::table('pcmsos')
            ->where('empresa_id', $empresaId)
            ->orderByDesc('ativo')
            ->orderBy('label')
            ->get(['id', 'label', 'ativo'])
            ->map(function ($p) use ($versoes, $uso, $hoje) {
                $doPrograma = $versoes->get($p->id, collect());
                $vigente = $doPrograma->firstWhere('status', PcmsoVersao::STATUS_VIGENTE);
                $rascunho = $doPrograma->firstWhere('status', PcmsoVersao::STATUS_RASCUNHO);

                return [
                    'id' => (int) $p->id,
                    'label' => (string) $p->label,
                    'ativo' => (bool) $p->ativo,
                    'encaminhamentos' => (int) ($uso[$p->id] ?? 0),
                    'vigente' => $vigente ? [
                        'id' => $vigente->id,
                        'versao' => $vigente->versao,
                        'vigencia_inicio_br' => $vigente->vigencia_inicio_br,
                        'vigencia_fim_br' => $vigente->vigencia_fim_br,
                        'vencida' => $vigente->vigencia_fim && $vigente->vigencia_fim->lessThan($hoje),
                    ] : null,
                    'rascunho' => $rascunho ? ['id' => $rascunho->id, 'versao' => $rascunho->versao] : null,
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listarVersoes(int $empresaId, int $pcmsoId): array
    {
        $this->programa($empresaId, $pcmsoId);

        $totais = DB::table('pcmsos_versoes_exames')
            ->join('pcmsos_versoes as v', 'v.id', '=', 'pcmsos_versoes_exames.pcmso_versao_id')
            ->where('v.pcmso_id', $pcmsoId)
            ->groupBy('pcmso_versao_id')
            ->selectRaw('pcmso_versao_id, COUNT(DISTINCT vaga_id) as cargos, COUNT(*) as exames')
            ->get()
            ->keyBy('pcmso_versao_id');

        return PcmsoVersao::query()
            ->where('empresa_id', $empresaId)
            ->where('pcmso_id', $pcmsoId)
            ->with('Coordenador:id,nome,crm,crm_uf')
            ->orderByDesc('versao')
            ->get()
            ->map(fn (PcmsoVersao $v) => [
                'id' => $v->id,
                'versao' => $v->versao,
                'status' => $v->status,
                'vigencia_inicio_br' => $v->vigencia_inicio_br,
                'vigencia_fim_br' => $v->vigencia_fim_br,
                'publicado_em_br' => $v->publicado_em_br,
                'saude_medico_id' => $v->saude_medico_id,
                'coordenador' => $v->Coordenador ? $v->Coordenador->nome . ' (' . $v->Coordenador->crm_completo . ')' : null,
                'observacao' => $v->observacao,
                'total_cargos' => (int) ($totais[$v->id]->cargos ?? 0),
                'total_exames' => (int) ($totais[$v->id]->exames ?? 0),
            ])
            ->all();
    }

    /**
     * Cargos com exames da versão, riscos do PGR vigente e colaboradores ativos.
     *
     * @return array<string, mixed>
     */
    public function detalhe(int $empresaId, int $versaoId): array
    {
        $versao = $this->versao($empresaId, $versaoId);
        $exames = collect($this->exames->listar($empresaId))->keyBy('id');
        $tipos = ExameTipo::query()->pluck('label', 'id');
        $riscosNomes = collect($this->riscos->listar($empresaId))->pluck('label', 'id');
        $riscosPorCargo = $this->pgr->riscosPorCargo($empresaId);

        $linhas = PcmsoVersaoExame::query()->where('pcmso_versao_id', $versao->id)->get()->groupBy('vaga_id');

        $colaboradores = (new ColaboradoresAtivosQuery())->daEmpresa($empresaId)
            ->leftJoin('vagas_abertas as va', 'va.id', '=', 'fc.vagas_abertas_id')
            ->selectRaw('COALESCE(va.vaga_id, fc.vaga_id) as vaga, COUNT(DISTINCT fc.id) as total')
            ->groupByRaw('COALESCE(va.vaga_id, fc.vaga_id)')
            ->pluck('total', 'vaga');

        $cargos = DB::table('vagas')
            ->where('empresa_id', $empresaId)
            ->where(fn ($q) => $q->where('ativo', true)->orWhereIn('id', $linhas->keys()->all()))
            ->orderBy('nome')
            ->get(['id', 'nome'])
            ->map(fn ($vaga) => [
                'vaga_id' => (int) $vaga->id,
                'nome' => (string) $vaga->nome,
                'colaboradores' => (int) ($colaboradores[$vaga->id] ?? 0),
                'riscos' => collect($riscosPorCargo[$vaga->id] ?? [])->map(fn ($id) => $riscosNomes[$id] ?? ('#' . $id))->values()->all(),
                'exames' => $linhas->get($vaga->id, collect())->map(fn (PcmsoVersaoExame $e) => [
                    'exame_id' => $e->exame_id,
                    'label' => $exames[$e->exame_id]['label'] ?? ('#' . $e->exame_id),
                    'exame_tipo_ids' => array_map('intval', $e->exame_tipo_ids),
                    'tipos' => collect($e->exame_tipo_ids)->map(fn ($id) => $tipos[$id] ?? ('#' . $id))->values()->all(),
                    'obrigatorio' => $e->obrigatorio,
                    'unidade' => $e->unidade,
                    'valor' => $e->valor,
                    'periodicidade' => $e->unidade && $e->valor ? (new Periodicidade($e->unidade, $e->valor))->descricao() : null,
                    'origem' => $e->origem,
                    'observacao' => $e->observacao,
                ])->values()->all(),
            ])
            ->all();

        return [
            'versao' => collect($this->listarVersoes($empresaId, $versao->pcmso_id))->firstWhere('id', $versao->id),
            'editavel' => $versao->status === PcmsoVersao::STATUS_RASCUNHO,
            'pgr_vigente' => $this->pgr->vigente($empresaId),
            'cargos' => $cargos,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function opcoes(int $empresaId): array
    {
        $hoje = CarbonImmutable::today()->toDateString();

        return [
            'exames' => $this->exames->listar($empresaId),
            'tipos_exame' => ExameTipo::query()->where('ativo', true)->orderBy('id')->get(['id', 'label'])
                ->map(fn ($t) => ['id' => (int) $t->id, 'label' => (string) $t->label])->all(),
            'riscos' => $this->riscos->listar($empresaId),
            'exames_por_risco' => $this->exames->examesPorRisco($empresaId),
            'coordenadores' => DB::table('saude_medicos as m')
                ->join('saude_medicos_vinculos as v', 'v.saude_medico_id', '=', 'm.id')
                ->where('m.empresa_id', $empresaId)
                ->where('v.papel', SaudeMedicoVinculo::PAPEL_COORDENADOR)
                ->whereNull('m.deleted_at')
                ->whereNull('v.deleted_at')
                ->where('m.ativo', true)
                ->where(fn ($q) => $q->whereNull('v.vigencia_fim')->orWhere('v.vigencia_fim', '>=', $hoje))
                ->distinct()
                ->orderBy('m.nome')
                ->get(['m.id', 'm.nome', 'm.crm', 'm.crm_uf'])
                ->map(fn ($m) => ['value' => (int) $m->id, 'label' => $m->nome . ' (CRM ' . $m->crm . '/' . $m->crm_uf . ')'])
                ->all(),
        ];
    }

    public function criarPrograma(int $empresaId, string $label): int
    {
        $label = trim($label);
        if (mb_strlen($label) < 2 || mb_strlen($label) > 120) {
            throw ValidationException::withMessages(['label' => 'Informe o nome do programa (2 a 120 caracteres).']);
        }
        if (DB::table('pcmsos')->where('empresa_id', $empresaId)->whereRaw('LOWER(label) = ?', [mb_strtolower($label)])->exists()) {
            throw ValidationException::withMessages(['label' => 'Já existe um PCMSO com esse nome.']);
        }

        return (int) DB::table('pcmsos')->insertGetId(['empresa_id' => $empresaId, 'label' => $label, 'ativo' => true]);
    }

    /**
     * Nova versão em rascunho, copiando a vigente. Um rascunho por programa.
     */
    public function criarRascunho(int $empresaId, int $pcmsoId, User $autor): PcmsoVersao
    {
        $this->programa($empresaId, $pcmsoId);

        if (PcmsoVersao::query()->where('pcmso_id', $pcmsoId)->where('status', PcmsoVersao::STATUS_RASCUNHO)->exists()) {
            throw ValidationException::withMessages(['pcmso' => 'Já existe uma versão em rascunho deste PCMSO.']);
        }

        return DB::transaction(function () use ($empresaId, $pcmsoId, $autor) {
            $base = PcmsoVersao::query()
                ->where('pcmso_id', $pcmsoId)
                ->where('status', PcmsoVersao::STATUS_VIGENTE)
                ->with('Exames')
                ->first();

            $rascunho = PcmsoVersao::query()->create([
                'pcmso_id' => $pcmsoId,
                'empresa_id' => $empresaId,
                'versao' => (int) PcmsoVersao::withTrashed()->where('pcmso_id', $pcmsoId)->max('versao') + 1,
                'status' => PcmsoVersao::STATUS_RASCUNHO,
                'saude_medico_id' => $base?->saude_medico_id,
                'observacao' => $base?->observacao,
                'user_id' => $autor->id,
            ]);

            foreach ($base?->Exames ?? [] as $linha) {
                PcmsoVersaoExame::query()->create(
                    $linha->only(['vaga_id', 'exame_id', 'exame_tipo_ids', 'obrigatorio', 'unidade', 'valor', 'origem', 'observacao'])
                    + ['pcmso_versao_id' => $rascunho->id]
                );
            }

            $this->auditar($autor, $rascunho, sprintf('criou rascunho do PCMSO versão %d%s', $rascunho->versao, $base ? ' (cópia da versão ' . $base->versao . ')' : ''));

            return $rascunho;
        });
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function salvarCabecalho(int $empresaId, int $versaoId, array $dados): void
    {
        $versao = $this->rascunho($empresaId, $versaoId);
        $medicoId = !empty($dados['saude_medico_id']) ? (int) $dados['saude_medico_id'] : null;

        if ($medicoId && !DB::table('saude_medicos')->where('empresa_id', $empresaId)->where('id', $medicoId)->whereNull('deleted_at')->exists()) {
            throw ValidationException::withMessages(['saude_medico_id' => 'Médico não pertence a esta empresa.']);
        }

        $observacao = trim((string) ($dados['observacao'] ?? ''));
        $versao->saude_medico_id = $medicoId;
        $versao->observacao = $observacao === '' ? null : mb_substr($observacao, 0, 1000);
        $versao->save();
    }

    /**
     * Exames de um cargo no rascunho (substitui a lista).
     *
     * @param  list<array<string, mixed>>  $itens
     */
    public function salvarCargo(int $empresaId, int $versaoId, int $vagaId, array $itens): void
    {
        $versao = $this->rascunho($empresaId, $versaoId);
        if (!DB::table('vagas')->where('empresa_id', $empresaId)->where('id', $vagaId)->exists()) {
            throw ValidationException::withMessages(['vaga_id' => 'Cargo não pertence a esta empresa.']);
        }

        $tiposValidos = ExameTipo::query()->where('ativo', true)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $linhas = collect($itens)
            ->map(function ($i) use ($tiposValidos) {
                $tipos = array_values(array_unique(array_intersect(array_map('intval', (array) ($i['exame_tipo_ids'] ?? [])), $tiposValidos)));
                $unidade = $i['unidade'] ?? null;
                $valor = isset($i['valor']) && $i['valor'] !== '' ? (int) $i['valor'] : null;

                if ($unidade || $valor) {
                    try {
                        $periodicidade = new Periodicidade((string) $unidade, (int) $valor);
                    } catch (\InvalidArgumentException) {
                        throw ValidationException::withMessages(['exames' => 'Periodicidade do exame inválida.']);
                    }
                    $this->trava->validar($periodicidade, 'exames');
                }

                return [
                    'exame_id' => (int) ($i['exame_id'] ?? 0),
                    'exame_tipo_ids' => $tipos,
                    'obrigatorio' => filter_var($i['obrigatorio'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'unidade' => $unidade ?: null,
                    'valor' => $unidade ? $valor : null,
                    'origem' => ($i['origem'] ?? null) === PcmsoVersaoExame::ORIGEM_SUGERIDO ? PcmsoVersaoExame::ORIGEM_SUGERIDO : PcmsoVersaoExame::ORIGEM_MANUAL,
                    'observacao' => ($obs = trim((string) ($i['observacao'] ?? ''))) === '' ? null : mb_substr($obs, 0, 500),
                ];
            })
            ->filter(fn ($i) => $i['exame_id'] > 0)
            ->unique('exame_id')
            ->values();

        if ($linhas->contains(fn ($i) => $i['exame_tipo_ids'] === [])) {
            throw ValidationException::withMessages(['exames' => 'Marque em quais tipos de ASO cada exame é feito.']);
        }
        if ($linhas->count() > 60) {
            throw ValidationException::withMessages(['exames' => 'No máximo 60 exames por cargo.']);
        }
        $this->exames->validarDaEmpresa($empresaId, $linhas->pluck('exame_id')->all());

        DB::transaction(function () use ($versao, $vagaId, $linhas) {
            PcmsoVersaoExame::query()->where('pcmso_versao_id', $versao->id)->where('vaga_id', $vagaId)->delete();
            foreach ($linhas as $linha) {
                PcmsoVersaoExame::query()->create($linha + ['pcmso_versao_id' => $versao->id, 'vaga_id' => $vagaId]);
            }
        });
    }

    /**
     * Preenche o rascunho com os exames sugeridos pelos riscos do PGR vigente.
     * Não remove nada; só acrescenta exames e tipos que faltam.
     *
     * @return array{cargos: int, exames_adicionados: int}
     */
    public function sugerirPeloPgr(int $empresaId, int $versaoId): array
    {
        $versao = $this->rascunho($empresaId, $versaoId);
        if (!$this->pgr->vigente($empresaId)) {
            throw ValidationException::withMessages(['pgr' => 'Publique o PGR (riscos por cargo) antes de sugerir exames.']);
        }

        $examesPorRisco = $this->exames->examesPorRisco($empresaId);
        if ($examesPorRisco === []) {
            throw ValidationException::withMessages(['pgr' => 'Defina os exames de cada risco em "Exames por risco" antes de sugerir.']);
        }

        $cargos = 0;
        $adicionados = 0;

        DB::transaction(function () use ($versao, $empresaId, $examesPorRisco, &$cargos, &$adicionados) {
            foreach ($this->pgr->riscosPorCargo($empresaId) as $vagaId => $riscoIds) {
                $sugestoes = [];
                foreach ($riscoIds as $riscoId) {
                    foreach ($examesPorRisco[$riscoId] ?? [] as $item) {
                        $sugestoes[$item['exame_id']] = array_values(array_unique(array_merge($sugestoes[$item['exame_id']] ?? [], $item['exame_tipo_ids'])));
                    }
                }
                if ($sugestoes === []) {
                    continue;
                }

                $existentes = PcmsoVersaoExame::query()
                    ->where('pcmso_versao_id', $versao->id)
                    ->where('vaga_id', $vagaId)
                    ->get()
                    ->keyBy('exame_id');

                $mudou = false;
                foreach ($sugestoes as $exameId => $tipos) {
                    $linha = $existentes->get($exameId);
                    if (!$linha) {
                        PcmsoVersaoExame::query()->create([
                            'pcmso_versao_id' => $versao->id,
                            'vaga_id' => $vagaId,
                            'exame_id' => $exameId,
                            'exame_tipo_ids' => $tipos,
                            'obrigatorio' => true,
                            'origem' => PcmsoVersaoExame::ORIGEM_SUGERIDO,
                        ]);
                        $adicionados++;
                        $mudou = true;
                        continue;
                    }

                    $unidos = array_values(array_unique(array_merge(array_map('intval', $linha->exame_tipo_ids), $tipos)));
                    if (count($unidos) !== count($linha->exame_tipo_ids)) {
                        $linha->exame_tipo_ids = $unidos;
                        $linha->save();
                        $mudou = true;
                    }
                }

                $cargos += $mudou ? 1 : 0;
            }
        });

        return ['cargos' => $cargos, 'exames_adicionados' => $adicionados];
    }

    /**
     * Publica: exige médico coordenador com vínculo vigente no início e ao menos um cargo com exames.
     */
    public function publicar(int $empresaId, int $versaoId, ?string $inicio, ?string $fim, User $autor): PcmsoVersao
    {
        $versao = $this->rascunho($empresaId, $versaoId);
        $dataInicio = $this->data($inicio, 'vigencia_inicio', 'Informe o início da vigência.');
        $dataFim = $fim ? $this->data($fim, 'vigencia_fim', 'Fim da vigência inválido.') : null;

        if ($dataFim && $dataFim->lessThanOrEqualTo($dataInicio)) {
            throw ValidationException::withMessages(['vigencia_fim' => 'O fim precisa ser depois do início.']);
        }
        if (!$versao->saude_medico_id || !$this->coordenadorVigenteEm($empresaId, $versao->saude_medico_id, $dataInicio)) {
            throw ValidationException::withMessages(['saude_medico_id' => 'Escolha um médico com vínculo de coordenador do PCMSO vigente no início (NR-7).']);
        }
        if (!PcmsoVersaoExame::query()->where('pcmso_versao_id', $versao->id)->exists()) {
            throw ValidationException::withMessages(['pcmso' => 'Defina os exames de ao menos um cargo antes de publicar.']);
        }

        DB::transaction(function () use ($versao, $dataInicio, $dataFim, $autor) {
            $anterior = PcmsoVersao::query()
                ->where('pcmso_id', $versao->pcmso_id)
                ->where('status', PcmsoVersao::STATUS_VIGENTE)
                ->lockForUpdate()
                ->first();

            if ($anterior) {
                if ($anterior->vigencia_inicio && $dataInicio->lessThanOrEqualTo(CarbonImmutable::parse($anterior->vigencia_inicio))) {
                    throw ValidationException::withMessages(['vigencia_inicio' => 'O início precisa ser depois do início da versão vigente (' . $anterior->vigencia_inicio_br . ').']);
                }
                $anterior->status = PcmsoVersao::STATUS_ARQUIVADO;
                $anterior->vigencia_fim = $dataInicio->subDay()->toDateString();
                $anterior->save();
            }

            $versao->status = PcmsoVersao::STATUS_VIGENTE;
            $versao->vigencia_inicio = $dataInicio->toDateString();
            $versao->vigencia_fim = $dataFim?->toDateString();
            $versao->publicado_em = now();
            $versao->user_id = $autor->id;
            $versao->save();

            $this->auditar($autor, $versao, sprintf(
                'publicou o PCMSO versão %d (vigência a partir de %s)%s',
                $versao->versao,
                $dataInicio->format('d/m/Y'),
                $anterior ? '; versão ' . $anterior->versao . ' arquivada' : ''
            ));
        });

        PainelCache::invalidar($empresaId);

        return $versao->refresh();
    }

    public function excluirRascunho(int $empresaId, int $versaoId, User $autor): void
    {
        $versao = $this->rascunho($empresaId, $versaoId);
        $versao->delete();
        $this->auditar($autor, $versao, sprintf('excluiu o rascunho do PCMSO versão %d', $versao->versao));
    }

    /**
     * Para o Painel: programas ativos sem versão vigente e vigentes vencendo em até 60 dias.
     *
     * @return array{programas_ativos: int, sem_vigente: int, vencendo: int, vencidos: int}
     */
    public function alertas(int $empresaId, ?CarbonImmutable $hoje = null): array
    {
        $hoje ??= CarbonImmutable::today();
        $programas = collect($this->listarProgramas($empresaId, $hoje))->where('ativo', true);

        $vigentes = DB::table('pcmsos_versoes')
            ->where('empresa_id', $empresaId)
            ->where('status', PcmsoVersao::STATUS_VIGENTE)
            ->whereNull('deleted_at')
            ->whereNotNull('vigencia_fim');

        return [
            'programas_ativos' => $programas->count(),
            'sem_vigente' => $programas->whereNull('vigente')->count(),
            'vencendo' => (clone $vigentes)->whereBetween('vigencia_fim', [$hoje->toDateString(), $hoje->addDays(60)->toDateString()])->count(),
            'vencidos' => (clone $vigentes)->where('vigencia_fim', '<', $hoje->toDateString())->count(),
        ];
    }

    private function coordenadorVigenteEm(int $empresaId, int $medicoId, CarbonImmutable $data): bool
    {
        $dia = $data->toDateString();

        return DB::table('saude_medicos_vinculos as v')
            ->join('saude_medicos as m', 'm.id', '=', 'v.saude_medico_id')
            ->where('v.empresa_id', $empresaId)
            ->where('m.id', $medicoId)
            ->where('m.ativo', true)
            ->whereNull('m.deleted_at')
            ->whereNull('v.deleted_at')
            ->where('v.papel', SaudeMedicoVinculo::PAPEL_COORDENADOR)
            ->whereDate('v.vigencia_inicio', '<=', $dia)
            ->where(fn ($q) => $q->whereNull('v.vigencia_fim')->orWhere('v.vigencia_fim', '>=', $dia))
            ->exists();
    }

    private function programa(int $empresaId, int $pcmsoId): object
    {
        $programa = DB::table('pcmsos')->where('empresa_id', $empresaId)->where('id', $pcmsoId)->first();
        if (!$programa) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())->setModel('Pcmso', [$pcmsoId]);
        }

        return $programa;
    }

    public function versao(int $empresaId, int $versaoId): PcmsoVersao
    {
        return PcmsoVersao::query()->where('empresa_id', $empresaId)->findOrFail($versaoId);
    }

    private function rascunho(int $empresaId, int $versaoId): PcmsoVersao
    {
        $versao = $this->versao($empresaId, $versaoId);
        if ($versao->status !== PcmsoVersao::STATUS_RASCUNHO) {
            throw ValidationException::withMessages(['pcmso' => 'Só a versão em rascunho pode ser alterada. Crie uma nova versão.']);
        }

        return $versao;
    }

    private function data(?string $valor, string $campo, string $mensagem): CarbonImmutable
    {
        try {
            if ($valor) {
                return CarbonImmutable::parse((new DataHora($valor))->dataInsert())->startOfDay();
            }
        } catch (\Throwable) {
            // cai na mensagem abaixo
        }

        throw ValidationException::withMessages([$campo => $mensagem]);
    }

    private function auditar(User $autor, PcmsoVersao $versao, string $descricao): void
    {
        // activity_log do MyBP exige a coluna custom `descricao` (NOT NULL).
        activity('saude_pcmso')
            ->causedBy($autor)
            ->withProperties(['empresa_id' => $versao->empresa_id, 'pcmso_id' => $versao->pcmso_id, 'pcmso_versao_id' => $versao->id, 'versao' => $versao->versao])
            ->tap(function ($activity) use ($descricao) {
                $activity->descricao = $descricao;
            })
            ->log($descricao);
    }
}
