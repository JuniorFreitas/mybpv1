<?php

namespace App\Domain\Exames\Services;

use App\Models\AlternativaFormulario;
use App\Models\Formulario;
use App\Models\RespostaAlternativas;
use App\Models\SetoresFormulario;
use DomainException;
use Illuminate\Support\Facades\DB;

class ExameFormularioBuilderService
{
    public const TIPOS_CAMPO = ['checkbox', 'select', 'text', 'textarea', 'number', 'float'];

    public function __construct(private readonly ExameFormularioResolver $resolver)
    {
    }

    public function listarFormulariosEmpresa(int $empresaId)
    {
        return Formulario::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->orderBy('titulo')
            ->get();
    }

    public function criarFormulario(int $empresaId, string $titulo, ?string $descricao = null): Formulario
    {
        return DB::transaction(function () use ($empresaId, $titulo, $descricao) {
            $form = Formulario::withoutGlobalScopes()->create([
                'empresa_id' => $empresaId,
                'titulo' => trim($titulo),
                'descricao' => $descricao,
            ]);

            $setor = SetoresFormulario::create([
                'empresa_id' => $empresaId,
                'nome' => 'Geral',
            ]);

            $form->Setores()->attach($setor->id, ['ordem' => 1]);

            $this->resolver->invalidarCache(null, $empresaId);

            return $this->carregar($form->id, $empresaId);
        });
    }

    public function carregar(int $formularioId, int $empresaId): Formulario
    {
        $form = Formulario::withoutGlobalScopes()
            ->where('id', $formularioId)
            ->where('empresa_id', $empresaId)
            ->first();

        if (!$form) {
            throw new DomainException('Formulário não encontrado.');
        }

        return $form->load(['Setores.Alternativas.Opcoes']);
    }

    public function atualizarFormulario(int $formularioId, int $empresaId, array $dados): Formulario
    {
        $form = $this->carregar($formularioId, $empresaId);
        $form->update([
            'titulo' => trim($dados['titulo'] ?? $form->titulo),
            'descricao' => $dados['descricao'] ?? $form->descricao,
        ]);
        $this->resolver->invalidarCache(null, $empresaId);

        return $form->fresh()->load(['Setores.Alternativas.Opcoes']);
    }

    public function adicionarSetor(int $formularioId, int $empresaId, string $nome): SetoresFormulario
    {
        return DB::transaction(function () use ($formularioId, $empresaId, $nome) {
            $form = $this->carregar($formularioId, $empresaId);
            $maxOrdem = (int) DB::table('formulario_setores')
                ->where('formulario_id', $form->id)
                ->max('ordem');

            $setor = SetoresFormulario::create([
                'empresa_id' => $empresaId,
                'nome' => trim($nome),
            ]);

            $form->Setores()->attach($setor->id, ['ordem' => $maxOrdem + 1]);
            $this->resolver->invalidarCache(null, $empresaId);

            return $setor;
        });
    }

    public function atualizarSetor(int $setorId, int $empresaId, string $nome): SetoresFormulario
    {
        $setor = SetoresFormulario::query()->where('id', $setorId)->where('empresa_id', $empresaId)->first();
        if (!$setor) {
            throw new DomainException('Seção não encontrada.');
        }
        $setor->update(['nome' => trim($nome)]);
        $this->resolver->invalidarCache(null, $empresaId);

        return $setor;
    }

    public function reordenarSetores(int $formularioId, int $empresaId, array $setorIds): void
    {
        $this->carregar($formularioId, $empresaId);
        DB::transaction(function () use ($formularioId, $setorIds) {
            foreach (array_values($setorIds) as $index => $setorId) {
                DB::table('formulario_setores')
                    ->where('formulario_id', $formularioId)
                    ->where('setores_id', $setorId)
                    ->update(['ordem' => $index + 1]);
            }
        });
        $this->resolver->invalidarCache(null, $empresaId);
    }

    public function adicionarCampo(int $setorId, int $empresaId, array $dados): AlternativaFormulario
    {
        $this->assertTipoCampo($dados['tipo'] ?? null);

        return DB::transaction(function () use ($setorId, $empresaId, $dados) {
            $setor = SetoresFormulario::query()->where('id', $setorId)->where('empresa_id', $empresaId)->first();
            if (!$setor) {
                throw new DomainException('Seção não encontrada.');
            }

            $alternativa = AlternativaFormulario::create([
                'empresa_id' => $empresaId,
                'nome' => trim($dados['nome']),
                'tipo' => $dados['tipo'],
                'ativo' => true,
                'chave_canonica' => $dados['chave_canonica'] ?? null,
            ]);

            $maxOrdem = (int) DB::table('setor_alternativas')->where('setor_id', $setorId)->max('ordem');

            $setor->Alternativas()->attach($alternativa->id, [
                'obrigatorio' => (bool) ($dados['obrigatorio'] ?? false),
                'min' => $dados['min'] ?? null,
                'max' => $dados['max'] ?? null,
                'ordem' => $maxOrdem + 1,
                'class_especial' => $dados['class_especial'] ?? null,
            ]);

            if (($dados['tipo'] ?? '') === 'select' && !empty($dados['opcoes']) && is_array($dados['opcoes'])) {
                $this->sincronizarOpcoes($alternativa, $dados['opcoes']);
            }

            $this->resolver->invalidarCache(null, $empresaId);

            return $alternativa->load('Opcoes');
        });
    }

    public function atualizarCampo(int $alternativaId, int $empresaId, array $dados): AlternativaFormulario
    {
        return DB::transaction(function () use ($alternativaId, $empresaId, $dados) {
            $alternativa = AlternativaFormulario::query()
                ->where('id', $alternativaId)
                ->where('empresa_id', $empresaId)
                ->first();

            if (!$alternativa) {
                throw new DomainException('Campo não encontrado.');
            }

            if (isset($dados['tipo'])) {
                $this->assertTipoCampo($dados['tipo']);
            }

            $alternativa->update([
                'nome' => trim($dados['nome'] ?? $alternativa->nome),
                'tipo' => $dados['tipo'] ?? $alternativa->tipo,
                'chave_canonica' => array_key_exists('chave_canonica', $dados)
                    ? $dados['chave_canonica']
                    : $alternativa->chave_canonica,
                'ativo' => array_key_exists('ativo', $dados) ? (bool) $dados['ativo'] : $alternativa->ativo,
            ]);

            if (isset($dados['setor_id'])) {
                $pivot = [
                    'obrigatorio' => (bool) ($dados['obrigatorio'] ?? false),
                    'min' => $dados['min'] ?? null,
                    'max' => $dados['max'] ?? null,
                ];
                if (isset($dados['class_especial'])) {
                    $pivot['class_especial'] = $dados['class_especial'];
                }
                if (isset($dados['ordem'])) {
                    $pivot['ordem'] = (int) $dados['ordem'];
                }
                DB::table('setor_alternativas')
                    ->where('setor_id', $dados['setor_id'])
                    ->where('alternativa_id', $alternativaId)
                    ->update($pivot);
            }

            if (($alternativa->tipo === 'select' || ($dados['tipo'] ?? null) === 'select')
                && array_key_exists('opcoes', $dados)
                && is_array($dados['opcoes'])) {
                $this->sincronizarOpcoes($alternativa, $dados['opcoes'], false);
            }

            $this->resolver->invalidarCache(null, $empresaId);

            return $alternativa->fresh()->load('Opcoes');
        });
    }

    public function reordenarCampos(int $setorId, int $empresaId, array $alternativaIds): void
    {
        $setor = SetoresFormulario::query()->where('id', $setorId)->where('empresa_id', $empresaId)->first();
        if (!$setor) {
            throw new DomainException('Seção não encontrada.');
        }

        DB::transaction(function () use ($setorId, $alternativaIds) {
            foreach (array_values($alternativaIds) as $index => $alternativaId) {
                DB::table('setor_alternativas')
                    ->where('setor_id', $setorId)
                    ->where('alternativa_id', $alternativaId)
                    ->update(['ordem' => $index + 1]);
            }
        });
        $this->resolver->invalidarCache(null, $empresaId);
    }

    /**
     * Soft remove: desvincula do setor e marca alternativa como inativa (preserva IDs históricos).
     */
    public function desativarCampo(int $setorId, int $alternativaId, int $empresaId): void
    {
        DB::transaction(function () use ($setorId, $alternativaId, $empresaId) {
            $alternativa = AlternativaFormulario::query()
                ->where('id', $alternativaId)
                ->where('empresa_id', $empresaId)
                ->first();

            if (!$alternativa) {
                throw new DomainException('Campo não encontrado.');
            }

            DB::table('setor_alternativas')
                ->where('setor_id', $setorId)
                ->where('alternativa_id', $alternativaId)
                ->delete();

            $alternativa->update(['ativo' => false]);
            $this->resolver->invalidarCache(null, $empresaId);
        });
    }

    /**
     * @param  array<int, string|array{id?:int,label:string,value?:int}>  $opcoes
     */
    private function sincronizarOpcoes(AlternativaFormulario $alternativa, array $opcoes, bool $somenteCriar = true): void
    {
        $ordem = 1;
        $idsMantidos = [];

        foreach ($opcoes as $opcao) {
            $label = is_array($opcao) ? trim((string) ($opcao['label'] ?? '')) : trim((string) $opcao);
            if ($label === '') {
                continue;
            }

            $id = is_array($opcao) ? ($opcao['id'] ?? null) : null;
            $value = is_array($opcao) ? ($opcao['value'] ?? null) : null;

            if ($id && !$somenteCriar) {
                $existente = RespostaAlternativas::query()
                    ->where('id', $id)
                    ->where('alternativa_id', $alternativa->id)
                    ->first();
                if ($existente) {
                    $existente->update([
                        'label' => $label,
                        'ordem' => $ordem,
                        'value' => $value ?? $existente->value ?? $existente->id,
                    ]);
                    $idsMantidos[] = $existente->id;
                    $ordem++;
                    continue;
                }
            }

            $nova = RespostaAlternativas::create([
                'alternativa_id' => $alternativa->id,
                'label' => $label,
                'selecionado' => false,
                'value' => $value,
            ]);
            // value padrão = próprio id (padrão do motor legado)
            if ($nova->value === null) {
                $nova->update(['value' => $nova->id, 'ordem' => $ordem]);
            } else {
                DB::table('resposta_alternativas')->where('id', $nova->id)->update(['ordem' => $ordem]);
            }
            $idsMantidos[] = $nova->id;
            $ordem++;
        }

        if (!$somenteCriar && !empty($idsMantidos)) {
            // Não apaga opções antigas — apenas deixa de listar no sync de novas;
            // opções removidas da lista permanecem no banco para histórico.
        }
    }

    private function assertTipoCampo(?string $tipo): void
    {
        if (!$tipo || !in_array($tipo, self::TIPOS_CAMPO, true)) {
            throw new DomainException('Tipo de campo inválido.');
        }
    }
}
