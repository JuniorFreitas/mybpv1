<?php

namespace App\Domain\Ajuda\Services;

/**
 * Sinônimos do vocabulário de RH: liga a palavra que o usuário costuma usar
 * ao termo que o manual usa ("demitir" → "desligamento", "carteirinha" → "carteira").
 *
 * Para ensinar um termo novo, inclua-o no grupo do termo do manual (ou crie um
 * grupo). Cada grupo precisa ter ao menos uma palavra que exista no manual.
 */
final class GlossarioRh
{
    /**
     * Palavras que significam exatamente a mesma coisa: são tratadas como uma só, na pergunta
     * e no manual, com peso integral ("funcionário" e "colaborador" dão o mesmo resultado).
     */
    private const EQUIVALENTES = [
        'colaborador' => ['funcionario', 'empregado', 'trabalhador'],
    ];

    private const GRUPOS = [
        ['demissao', 'demitir', 'desligamento', 'desligar', 'rescisao', 'dispensar', 'dispensa'],
        ['admissao', 'admitir', 'contratar', 'contratacao'],
        ['promover', 'promocao', 'mudanca'],
        ['transferencia', 'transferir', 'remanejar'],
        ['carteira', 'carteirinha', 'cracha'],
        ['exportar', 'exportacao', 'baixar', 'download', 'excel', 'planilha', 'csv'],
        ['importacao', 'importar', 'subir', 'upload', 'carregar'],
        ['enviar', 'mandar', 'encaminhar', 'envio'],
        ['alterar', 'editar', 'trocar', 'mudar', 'modificar', 'atualizar'],
        ['excluir', 'remover', 'apagar', 'deletar'],
        ['requisicao', 'abertura', 'solicitacao', 'solicitar', 'pedir', 'pedido'],
        ['oferta', 'proposta'],
        ['ferias', 'descanso'],
        ['programar', 'marcar', 'agendar', 'agendamento', 'solicitar'],
        ['ponto', 'batida', 'jornada'],
        ['ajustar', 'ajuste', 'corrigir', 'correcao', 'acertar'],
        ['folha', 'espelho'],
        ['afastamento', 'atestado', 'licenca'],
        ['chamado', 'suporte', 'atendimento', 'ajuda'],
        ['medida', 'advertencia', 'suspensao', 'punicao'],
        ['exame', 'aso', 'admissional', 'periodico'],
        ['treinamento', 'curso', 'capacitacao'],
        ['gestor', 'lider', 'lideranca', 'chefe', 'supervisor'],
        ['salario', 'remuneracao', 'pagamento'],
        ['senha', 'esqueci', 'esqueceu', 'recuperar', 'redefinir'],
        ['imprimir', 'gerar', 'emitir', 'pdf'],
        ['candidatura', 'candidatar', 'inscricao', 'inscrever'],
        ['curriculo', 'cv'],
        ['permissao', 'acesso', 'grupo', 'perfil'],
        ['vencimento', 'vencer', 'vencendo', 'vencido', 'prazo', 'validade'],
        ['portal', 'site', 'publicar', 'publicada', 'divulgar'],
        ['desempenho', 'avaliacao', 'competencia'],
        ['aniversariante', 'aniversario', 'parabens'],
        ['fornecedor', 'terceiro', 'terceirizada', 'prestador'],
        ['convocar', 'convocacao', 'chamar', 'intermitente'],
        ['listagem', 'lista', 'relacao'],
    ];

    /**
     * Expansões de mão única: a palavra do usuário leva ao termo do manual, mas não o contrário
     * ("promover" busca "mudança de cargo"; "cargo" não busca promoção).
     */
    private const EXPANSOES = [
        'promover' => ['mudanca', 'cargo'],
        'promocao' => ['mudanca', 'cargo'],
        'espelho' => ['folha', 'ponto'],
        'carteirinha' => ['etiqueta'],
        'direto' => ['avulsa'],
        'direta' => ['avulsa'],
    ];

    /** @var array<string, string>|null radical → radical da palavra principal */
    private ?array $equivalentes = null;

    /** @var array<string, array<int, string>>|null radical → radicais do(s) grupo(s) */
    private ?array $mapa = null;

    public function __construct(
        private readonly TokenizadorManual $tokenizador,
    ) {
    }

    /**
     * @param  string  $termo  termo já reduzido ao radical pelo TokenizadorManual
     * @return array<int, string>
     */
    public function sinonimos(string $termo): array
    {
        return $this->mapa()[$termo] ?? [];
    }

    /**
     * Troca cada termo pela palavra principal do seu grupo de equivalentes.
     *
     * @param  array<int, string>  $termos  termos já reduzidos ao radical
     * @return array<int, string>
     */
    public function canonizar(array $termos): array
    {
        if ($this->equivalentes === null) {
            $this->equivalentes = [];
            foreach (self::EQUIVALENTES as $principal => $outros) {
                $radical = $this->tokenizador->tokenizar($principal)[0];
                foreach ($this->tokenizador->tokenizar(implode(' ', $outros)) as $outro) {
                    $this->equivalentes[$outro] = $radical;
                }
            }
        }

        return array_map(fn (string $termo) => $this->equivalentes[$termo] ?? $termo, $termos);
    }

    private function mapa(): array
    {
        if ($this->mapa !== null) {
            return $this->mapa;
        }

        $mapa = [];
        foreach (self::GRUPOS as $grupo) {
            $radicais = array_values(array_unique($this->tokenizador->tokenizar(implode(' ', $grupo))));
            foreach ($radicais as $radical) {
                $outros = array_values(array_diff($radicais, [$radical]));
                $mapa[$radical] = array_values(array_unique(array_merge($mapa[$radical] ?? [], $outros)));
            }
        }

        foreach (self::EXPANSOES as $palavra => $destinos) {
            foreach ($this->tokenizador->tokenizar($palavra) as $radical) {
                $mapa[$radical] = array_values(array_unique(array_merge(
                    $mapa[$radical] ?? [],
                    $this->tokenizador->tokenizar(implode(' ', $destinos)),
                )));
            }
        }

        return $this->mapa = $mapa;
    }
}
