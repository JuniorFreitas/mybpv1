<?php

namespace App\Services\CartaOferta;

use App\Models\CartaOferta;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\VagasAbertas;
use MasterTag\DataHora;

class CartaOfertaContextoBuilder
{
    public const NAO_INFORMADO = 'Não informado';

    public function fake(?Cliente $empresa = null): array
    {
        return [
            'colaborador' => [
                'nome' => 'João da Silva',
                'cpf' => '000.000.000-00',
                'email' => 'joao.silva@exemplo.com',
            ],
            'cargo' => 'Analista de RH',
            'setor' => 'Recursos Humanos',
            'projeto' => 'Projeto Exemplo',
            'salario' => 'R$ 3.500,00',
            'data_inicio' => '10/03/2026',
            'empresa' => [
                'nome_fantasia' => $empresa->nome_fantasia ?? 'Empresa Exemplo',
                'razao_social' => $empresa->razao_social ?? 'Empresa Exemplo LTDA',
                'cnpj' => $empresa->cnpj ?? '00.000.000/0001-00',
            ],
            'data_emissao' => now()->format('d/m/Y'),
            'bloco_salario' => '<p style="line-height: 22pt;">Salário: <strong>R$ 3.500,00</strong></p>',
        ];
    }

    /**
     * Monta contexto para preview sem persistir carta (currículo/vaga + campos opcionais).
     *
     * @param  array{setor?: ?string, salario?: ?string, data_inicio?: ?string}  $extras
     */
    public function paraPreview(Curriculo $curriculo, ?VagasAbertas $vagaAberta, ?Cliente $empresa, array $extras = []): array
    {
        if ($vagaAberta) {
            $vagaAberta->loadMissing(['Vaga:id,nome']);
        }

        $cargo = null;
        if ($vagaAberta && $vagaAberta->Vaga) {
            $cargo = $vagaAberta->Vaga->nome;
        } elseif ($vagaAberta && $vagaAberta->titulo) {
            $cargo = $vagaAberta->titulo;
        }

        $dataInicio = $extras['data_inicio'] ?? null;
        if ($dataInicio) {
            try {
                $dataInicio = (new DataHora($dataInicio))->dataCompleta() ?: $dataInicio;
            } catch (\Throwable $e) {
                // mantém valor informado
            }
        }

        $salario = $this->formatarSalario($extras['salario'] ?? null);
        $projeto = $this->resolverProjetoNomeDaVagaAberta($vagaAberta);

        return [
            'colaborador' => [
                'nome' => $this->valor($curriculo->nome ?? null),
                'cpf' => $this->valor($curriculo->cpf ?? null),
                'email' => $this->valor($curriculo->email ?? null),
            ],
            'cargo' => $this->valor($cargo),
            'setor' => $this->valor($extras['setor'] ?? null),
            'projeto' => $this->valor($projeto),
            'salario' => $salario,
            'data_inicio' => $this->valor($dataInicio),
            'empresa' => [
                'nome_fantasia' => $this->valor($empresa?->nome_fantasia ?? $empresa?->razao_social ?? null),
                'razao_social' => $this->valor($empresa?->razao_social ?? null),
                'cnpj' => $this->valor($empresa?->cnpj ?? null),
            ],
            'data_emissao' => (new DataHora())->dataCompletaExt() ?: now()->format('d/m/Y'),
            'bloco_salario' => $this->blocoSalarioHtml($salario),
        ];
    }

    public function paraCarta(CartaOferta $carta): array
    {
        if (!$carta->relationLoaded('Curriculo') || !$carta->Curriculo) {
            $carta->setRelation(
                'Curriculo',
                Curriculo::withoutGlobalScopes()
                    ->select(['id', 'nome', 'email', 'cpf'])
                    ->find($carta->curriculo_id)
            );
        }

        if ($carta->vagas_abertas_id && (!$carta->relationLoaded('vagaAberta') || !$carta->vagaAberta)) {
            $carta->setRelation(
                'vagaAberta',
                VagasAbertas::withoutGlobalScopes()
                    ->with(['Vaga' => fn ($q) => $q->withoutGlobalScopes()->select(['id', 'nome'])])
                    ->select(['id', 'vaga_id', 'titulo'])
                    ->find($carta->vagas_abertas_id)
            );
        }

        if ($carta->vaga_projeto_id) {
            $carta->loadMissing([
                'vagaProjeto:id,vaga_aberta_id,projeto_id',
                'vagaProjeto.Projeto:id,nome',
                'vagaProjeto.VagaAberta:id,vaga_id,titulo',
                'vagaProjeto.VagaAberta.Vaga:id,nome',
            ]);
        }

        if ($carta->vagas_abertas_id) {
            $carta->loadMissing([
                'vagaAberta.Projetos:id,vaga_aberta_id,projeto_id',
                'vagaAberta.Projetos.Projeto:id,nome',
            ]);
        }

        $cargo = $this->resolverCargo($carta);
        $empresa = $carta->relationLoaded('empresa') && $carta->empresa
            ? $carta->empresa
            : Cliente::withoutGlobalScopes()
                ->select(['id', 'razao_social', 'nome_fantasia', 'cnpj'])
                ->find($carta->empresa_id);

        $salario = $this->formatarSalario($carta->salario ?? null);
        $projeto = $this->resolverProjetoNome($carta);

        return [
            'colaborador' => [
                'nome' => $this->valor($carta->Curriculo->nome ?? null),
                'cpf' => $this->valor($carta->Curriculo->cpf ?? null),
                'email' => $this->valor($carta->Curriculo->email ?? null),
            ],
            'cargo' => $this->valor($cargo),
            'setor' => $this->valor($carta->setor ?? null),
            'projeto' => $this->valor($projeto),
            'salario' => $salario,
            'data_inicio' => $this->valor($carta->data_inicio_br ?? null),
            'empresa' => [
                'nome_fantasia' => $this->valor($empresa->nome_fantasia ?? $empresa->razao_social ?? null),
                'razao_social' => $this->valor($empresa->razao_social ?? null),
                'cnpj' => $this->valor($empresa->cnpj ?? null),
            ],
            'data_emissao' => (new DataHora())->dataCompletaExt() ?: now()->format('d/m/Y'),
            'bloco_salario' => $this->blocoSalarioHtml($salario),
        ];
    }

    protected function resolverCargo(CartaOferta $carta): ?string
    {
        if ($carta->vagaAberta && $carta->vagaAberta->Vaga) {
            return $carta->vagaAberta->Vaga->nome;
        }
        if (
            $carta->vagaProjeto
            && $carta->vagaProjeto->VagaAberta
            && $carta->vagaProjeto->VagaAberta->Vaga
        ) {
            return $carta->vagaProjeto->VagaAberta->Vaga->nome;
        }
        if ($carta->vagaAberta && $carta->vagaAberta->titulo) {
            return $carta->vagaAberta->titulo;
        }

        return null;
    }

    protected function resolverProjetoNome(CartaOferta $carta): ?string
    {
        $nome = trim((string) ($carta->vagaProjeto?->Projeto?->nome ?? ''));
        if ($nome !== '') {
            return $nome;
        }

        return $this->resolverProjetoNomeDaVagaAberta($carta->vagaAberta);
    }

    protected function resolverProjetoNomeDaVagaAberta(?VagasAbertas $vagaAberta): ?string
    {
        if (!$vagaAberta) {
            return null;
        }

        if (!$vagaAberta->relationLoaded('Projetos')) {
            $vagaAberta->loadMissing([
                'Projetos:id,vaga_aberta_id,projeto_id',
                'Projetos.Projeto:id,nome',
            ]);
        }

        $projetos = $vagaAberta->Projetos;
        if (!$projetos || $projetos->isEmpty()) {
            return null;
        }

        $nomes = $projetos
            ->map(fn ($vp) => trim((string) ($vp->Projeto?->nome ?? '')))
            ->filter()
            ->unique()
            ->values();

        return $nomes->isEmpty() ? null : $nomes->implode(', ');
    }

    /**
     * Persiste null quando vazio ou zero (não deve aparecer no PDF).
     */
    public function normalizarSalarioParaPersistencia(?string $salario): ?string
    {
        $trim = trim((string) $salario);
        if ($trim === '' || $this->salarioEhZero($trim)) {
            return null;
        }

        return $trim;
    }

    /**
     * Retorna salário formatado (R$ X.XXX,XX) ou string vazia quando ausente/zero.
     */
    public function formatarSalario(?string $salario): string
    {
        $trim = trim((string) $salario);
        if ($trim === '' || $this->salarioEhZero($trim)) {
            return '';
        }

        if (stripos($trim, 'R$') === false) {
            return 'R$ ' . $trim;
        }

        return $trim;
    }

    public function salarioEhZero(string $salario): bool
    {
        $digitos = preg_replace('/\D+/', '', $salario) ?? '';

        return $digitos === '' || (int) $digitos === 0;
    }

    protected function blocoSalarioHtml(string $salarioFormatado): string
    {
        if ($salarioFormatado === '') {
            return '';
        }

        return '<p style="line-height: 22pt;">Salário: <strong>' . e($salarioFormatado) . '</strong></p>';
    }

    protected function valor(?string $value): string
    {
        $trim = trim((string) $value);

        return $trim !== '' ? $trim : self::NAO_INFORMADO;
    }
}
