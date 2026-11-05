<?php

namespace App\Domain\SaudeOcupacional\Medicos;

use App\Domain\SaudeOcupacional\Painel\PainelCache;
use App\Models\SaudeMedico;
use App\Models\SaudeMedicoVinculo;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use MasterTag\DataHora;

/**
 * Médicos responsáveis: coordenador do PCMSO (um vigente por vez) e examinadores (opcionalmente por clínica).
 * Dados profissionais (nome, CRM, contato). Nada de dado de saúde.
 */
class MedicosResponsaveisService
{
    public const UFS = ['AC', 'AL', 'AM', 'AP', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MG', 'MS', 'MT', 'PA', 'PB', 'PE', 'PI', 'PR', 'RJ', 'RN', 'RO', 'RR', 'RS', 'SC', 'SE', 'SP', 'TO'];

    public const DIAS_ALERTA_VINCULO = 30;

    /**
     * @return list<array<string, mixed>>
     */
    public function listar(int $empresaId, ?CarbonImmutable $hoje = null): array
    {
        $hoje ??= CarbonImmutable::today();
        $clinicas = $this->clinicas($empresaId)->pluck('nome', 'id');

        return SaudeMedico::query()
            ->where('empresa_id', $empresaId)
            ->with(['Vinculos' => fn ($q) => $q->orderByDesc('vigencia_inicio')])
            ->orderByDesc('ativo')
            ->orderBy('nome')
            ->get()
            ->map(fn (SaudeMedico $m) => [
                'id' => $m->id,
                'nome' => $m->nome,
                'crm' => $m->crm,
                'crm_uf' => $m->crm_uf,
                'crm_completo' => $m->crm_completo,
                'rqe' => $m->rqe,
                'especialidade' => $m->especialidade,
                'email' => $m->email,
                'telefone' => $m->telefone,
                'ativo' => $m->ativo,
                'updated_at_br' => $m->updated_at_br,
                'vinculos' => $m->Vinculos->map(fn (SaudeMedicoVinculo $v) => [
                    'id' => $v->id,
                    'papel' => $v->papel,
                    'empresa_exame_id' => $v->empresa_exame_id,
                    'clinica' => $v->empresa_exame_id ? ($clinicas[$v->empresa_exame_id] ?? null) : null,
                    'vigencia_inicio_br' => $v->vigencia_inicio_br,
                    'vigencia_fim_br' => $v->vigencia_fim_br,
                    'vigente' => $this->vigenteEm($v, $hoje),
                ])->values()->all(),
            ])
            ->all();
    }

    /**
     * @return array{ufs: list<string>, clinicas: list<array{value: int, label: string}>, papeis: list<array{value: string, label: string}>}
     */
    public function opcoes(int $empresaId): array
    {
        return [
            'ufs' => self::UFS,
            'clinicas' => $this->clinicas($empresaId)->map(fn ($c) => ['value' => (int) $c->id, 'label' => $c->nome])->values()->all(),
            'papeis' => [
                ['value' => SaudeMedicoVinculo::PAPEL_COORDENADOR, 'label' => 'Coordenador do PCMSO'],
                ['value' => SaudeMedicoVinculo::PAPEL_EXAMINADOR, 'label' => 'Examinador'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function salvar(int $empresaId, ?int $medicoId, array $dados, User $autor): SaudeMedico
    {
        $medico = $medicoId ? $this->buscar($empresaId, $medicoId) : null;
        $normalizado = $this->normalizarMedico($empresaId, $dados, $medicoId);
        $vinculos = $this->normalizarVinculos($empresaId, (array) ($dados['vinculos'] ?? []), $medico);

        $medico = DB::transaction(function () use ($medico, $empresaId, $normalizado, $vinculos, $autor) {
            $medico ??= new SaudeMedico(['empresa_id' => $empresaId, 'ativo' => true]);
            $novo = !$medico->exists;
            $medico->fill($normalizado + ['user_id' => $autor->id]);
            $medico->empresa_id = $empresaId;
            $medico->save();

            $manter = [];
            foreach ($vinculos as $v) {
                $registro = $v['id']
                    ? SaudeMedicoVinculo::query()->where('saude_medico_id', $medico->id)->findOrFail($v['id'])
                    : new SaudeMedicoVinculo(['saude_medico_id' => $medico->id, 'empresa_id' => $empresaId]);
                $registro->fill([
                    'papel' => $v['papel'],
                    'empresa_exame_id' => $v['empresa_exame_id'],
                    'vigencia_inicio' => $v['inicio']->toDateString(),
                    'vigencia_fim' => $v['fim']?->toDateString(),
                ]);
                $registro->saude_medico_id = $medico->id;
                $registro->empresa_id = $empresaId;
                $registro->save();
                $manter[] = $registro->id;
            }

            SaudeMedicoVinculo::query()
                ->where('saude_medico_id', $medico->id)
                ->whereNotIn('id', $manter ?: [0])
                ->delete();

            $this->auditar($autor, $medico, $novo ? 'criou' : 'alterou');

            return $medico;
        });

        PainelCache::invalidar($empresaId);

        return $medico;
    }

    public function alterarAtivo(int $empresaId, int $medicoId, bool $ativo, User $autor): void
    {
        $medico = $this->buscar($empresaId, $medicoId);
        $medico->ativo = $ativo;
        $medico->save();
        $this->auditar($autor, $medico, $ativo ? 'ativou' : 'desativou');
        PainelCache::invalidar($empresaId);
    }

    public function excluir(int $empresaId, int $medicoId, User $autor): void
    {
        $medico = $this->buscar($empresaId, $medicoId);

        DB::transaction(function () use ($medico, $autor) {
            SaudeMedicoVinculo::query()->where('saude_medico_id', $medico->id)->delete();
            $medico->delete();
            $this->auditar($autor, $medico, 'excluiu');
        });

        PainelCache::invalidar($empresaId);
    }

    /**
     * Coordenador do PCMSO vigente na data (médico ativo, vínculo dentro da vigência).
     *
     * @return array{medico_id: int, nome: string, crm_completo: string, vigencia_fim_br: string|null}|null
     */
    public function coordenadorVigente(int $empresaId, ?CarbonImmutable $data = null): ?array
    {
        $data = ($data ?? CarbonImmutable::today())->toDateString();

        $linha = DB::table('saude_medicos_vinculos as v')
            ->join('saude_medicos as m', 'm.id', '=', 'v.saude_medico_id')
            ->where('v.empresa_id', $empresaId)
            ->where('v.papel', SaudeMedicoVinculo::PAPEL_COORDENADOR)
            ->whereNull('v.deleted_at')
            ->whereNull('m.deleted_at')
            ->where('m.ativo', true)
            ->whereDate('v.vigencia_inicio', '<=', $data)
            ->where(fn ($q) => $q->whereNull('v.vigencia_fim')->orWhere('v.vigencia_fim', '>=', $data))
            ->orderByDesc('v.vigencia_inicio')
            ->first(['m.id', 'm.nome', 'm.crm', 'm.crm_uf', 'v.vigencia_fim']);

        if (!$linha) {
            return null;
        }

        return [
            'medico_id' => (int) $linha->id,
            'nome' => $linha->nome,
            'crm_completo' => 'CRM ' . $linha->crm . '/' . $linha->crm_uf,
            'vigencia_fim_br' => $linha->vigencia_fim ? CarbonImmutable::parse($linha->vigencia_fim)->format('d/m/Y') : null,
        ];
    }

    /**
     * Para o Painel: coordenador vigente e vínculos que terminam em até 30 dias.
     *
     * @return array{coordenador: array<string, mixed>|null, vinculos_vencendo: int}
     */
    public function alertas(int $empresaId, ?CarbonImmutable $hoje = null): array
    {
        $hoje ??= CarbonImmutable::today();

        $vencendo = DB::table('saude_medicos_vinculos as v')
            ->join('saude_medicos as m', 'm.id', '=', 'v.saude_medico_id')
            ->where('v.empresa_id', $empresaId)
            ->whereNull('v.deleted_at')
            ->whereNull('m.deleted_at')
            ->where('m.ativo', true)
            ->whereBetween('v.vigencia_fim', [$hoje->toDateString(), $hoje->addDays(self::DIAS_ALERTA_VINCULO)->toDateString()])
            ->count();

        return [
            'coordenador' => $this->coordenadorVigente($empresaId, $hoje),
            'vinculos_vencendo' => $vencendo,
        ];
    }

    /**
     * @param  array<string, mixed>  $dados
     * @return array<string, mixed>
     */
    private function normalizarMedico(int $empresaId, array $dados, ?int $medicoId): array
    {
        $nome = trim((string) ($dados['nome'] ?? ''));
        if (mb_strlen($nome) < 3 || mb_strlen($nome) > 150) {
            throw ValidationException::withMessages(['nome' => 'Informe o nome do médico (3 a 150 caracteres).']);
        }

        $crm = preg_replace('/\D/', '', (string) ($dados['crm'] ?? ''));
        if (strlen($crm) < 4 || strlen($crm) > 10) {
            throw ValidationException::withMessages(['crm' => 'Informe o número do CRM (só números, 4 a 10 dígitos).']);
        }

        $uf = mb_strtoupper(trim((string) ($dados['crm_uf'] ?? '')));
        if (!in_array($uf, self::UFS, true)) {
            throw ValidationException::withMessages(['crm_uf' => 'Escolha a UF do CRM.']);
        }

        $duplicado = SaudeMedico::query()
            ->where('empresa_id', $empresaId)
            ->where('crm', $crm)
            ->where('crm_uf', $uf)
            ->when($medicoId, fn ($q) => $q->where('id', '!=', $medicoId))
            ->exists();
        if ($duplicado) {
            throw ValidationException::withMessages(['crm' => "Já existe um médico com CRM {$crm}/{$uf} nesta empresa."]);
        }

        $email = trim((string) ($dados['email'] ?? ''));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages(['email' => 'E-mail inválido.']);
        }

        return [
            'nome' => $nome,
            'crm' => $crm,
            'crm_uf' => $uf,
            'rqe' => $this->textoOpcional($dados['rqe'] ?? null, 20),
            'especialidade' => $this->textoOpcional($dados['especialidade'] ?? null, 100),
            'email' => $email !== '' ? mb_substr($email, 0, 150) : null,
            'telefone' => $this->textoOpcional($dados['telefone'] ?? null, 20),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $vinculos
     * @return list<array{id: int|null, papel: string, empresa_exame_id: int|null, inicio: CarbonImmutable, fim: CarbonImmutable|null}>
     */
    private function normalizarVinculos(int $empresaId, array $vinculos, ?SaudeMedico $medico): array
    {
        $clinicasValidas = $this->clinicas($empresaId)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $normalizados = [];

        foreach ($vinculos as $v) {
            $papel = (string) ($v['papel'] ?? '');
            if (!in_array($papel, SaudeMedicoVinculo::PAPEIS, true)) {
                throw ValidationException::withMessages(['vinculos' => 'Papel do vínculo inválido.']);
            }

            $inicio = $this->data($v['vigencia_inicio'] ?? null);
            if (!$inicio) {
                throw ValidationException::withMessages(['vinculos' => 'Informe o início da vigência do vínculo.']);
            }
            $fim = $this->data($v['vigencia_fim'] ?? null);
            if ($fim && $fim->lessThan($inicio)) {
                throw ValidationException::withMessages(['vinculos' => 'O fim da vigência não pode ser antes do início.']);
            }

            $clinica = !empty($v['empresa_exame_id']) ? (int) $v['empresa_exame_id'] : null;
            if ($clinica && ($papel !== SaudeMedicoVinculo::PAPEL_EXAMINADOR || !in_array($clinica, $clinicasValidas, true))) {
                throw ValidationException::withMessages(['vinculos' => 'Clínica inválida (só para examinador e da própria empresa).']);
            }

            $normalizados[] = [
                'id' => !empty($v['id']) ? (int) $v['id'] : null,
                'papel' => $papel,
                'empresa_exame_id' => $clinica,
                'inicio' => $inicio,
                'fim' => $fim,
            ];
        }

        $this->validarCoordenadorSemSobreposicao($empresaId, $normalizados, $medico);

        return $normalizados;
    }

    /**
     * Só um coordenador do PCMSO por vez na empresa.
     *
     * @param  list<array{papel: string, inicio: CarbonImmutable, fim: CarbonImmutable|null}>  $vinculos
     */
    private function validarCoordenadorSemSobreposicao(int $empresaId, array $vinculos, ?SaudeMedico $medico): void
    {
        $novos = array_values(array_filter($vinculos, fn ($v) => $v['papel'] === SaudeMedicoVinculo::PAPEL_COORDENADOR));
        if ($novos === []) {
            return;
        }

        $outros = DB::table('saude_medicos_vinculos as v')
            ->join('saude_medicos as m', 'm.id', '=', 'v.saude_medico_id')
            ->where('v.empresa_id', $empresaId)
            ->where('v.papel', SaudeMedicoVinculo::PAPEL_COORDENADOR)
            ->whereNull('v.deleted_at')
            ->whereNull('m.deleted_at')
            ->when($medico, fn ($q) => $q->where('v.saude_medico_id', '!=', $medico->id))
            ->get(['m.nome', 'v.vigencia_inicio', 'v.vigencia_fim'])
            ->map(fn ($l) => [
                'nome' => $l->nome,
                'inicio' => CarbonImmutable::parse($l->vigencia_inicio),
                'fim' => $l->vigencia_fim ? CarbonImmutable::parse($l->vigencia_fim) : null,
            ])
            ->all();

        $todos = array_merge(array_map(fn ($v) => $v + ['nome' => 'este médico'], $novos), $outros);

        foreach ($novos as $i => $a) {
            foreach ($todos as $j => $b) {
                if ($i === $j) {
                    continue;
                }
                if ($this->sobrepoe($a['inicio'], $a['fim'], $b['inicio'], $b['fim'])) {
                    throw ValidationException::withMessages([
                        'vinculos' => sprintf(
                            'Já existe coordenador do PCMSO no período (%s, desde %s). Encerre a vigência anterior antes.',
                            $b['nome'],
                            $b['inicio']->format('d/m/Y')
                        ),
                    ]);
                }
            }
        }
    }

    private function sobrepoe(CarbonImmutable $iniA, ?CarbonImmutable $fimA, CarbonImmutable $iniB, ?CarbonImmutable $fimB): bool
    {
        return (!$fimB || $iniA->lessThanOrEqualTo($fimB)) && (!$fimA || $iniB->lessThanOrEqualTo($fimA));
    }

    private function vigenteEm(SaudeMedicoVinculo $v, CarbonImmutable $data): bool
    {
        return $v->vigencia_inicio && $v->vigencia_inicio->lessThanOrEqualTo($data)
            && (!$v->vigencia_fim || $v->vigencia_fim->greaterThanOrEqualTo($data));
    }

    private function buscar(int $empresaId, int $medicoId): SaudeMedico
    {
        return SaudeMedico::query()->where('empresa_id', $empresaId)->findOrFail($medicoId);
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function clinicas(int $empresaId)
    {
        return DB::table('empresa_exames')
            ->where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get(['id', 'nome']);
    }

    private function data(mixed $valor): ?CarbonImmutable
    {
        if (!$valor) {
            return null;
        }

        try {
            return CarbonImmutable::parse((new DataHora((string) $valor))->dataInsert())->startOfDay();
        } catch (\Throwable) {
            throw ValidationException::withMessages(['vinculos' => 'Data de vigência inválida.']);
        }
    }

    private function textoOpcional(mixed $valor, int $max): ?string
    {
        $texto = trim((string) $valor);

        return $texto === '' ? null : mb_substr($texto, 0, $max);
    }

    private function auditar(User $autor, SaudeMedico $medico, string $verbo): void
    {
        $descricao = sprintf('%s médico responsável %s (%s)', $verbo, $medico->nome, $medico->crm_completo);

        // activity_log do MyBP exige a coluna custom `descricao` (NOT NULL).
        activity('saude_medicos')
            ->causedBy($autor)
            ->withProperties(['empresa_id' => $medico->empresa_id, 'saude_medico_id' => $medico->id])
            ->tap(function ($activity) use ($descricao) {
                $activity->descricao = $descricao;
            })
            ->log($descricao);
    }
}
