<?php

namespace App\Authorization;

/**
 * Parser compartilhado de metadados a partir do nome snake_case da habilidade.
 */
final class HabilidadeMetaParser
{
    /** Prefixos de módulo conhecidos (mais longos primeiro). */
    private const MODULO_PREFIXOS = [
        'controle_ponto' => 'Controle de Ponto',
        'weekly_report' => 'Weekly Report',
        'acesso_clinica' => 'Acesso Clínica',
        'preferencias' => 'Preferências',
        'configuracao' => 'Configurações',
        'administracao' => 'Administração',
        'planejamento' => 'Planejamento',
        'admissao' => 'Admissão',
        'entrevista' => 'Entrevistas',
        'financeiro' => 'Financeiro',
        'treinamento' => 'Treinamentos',
        'relatorio' => 'Relatórios',
        'curriculos' => 'Currículos',
        'feedback' => 'Feedback',
        'privilegio' => 'Privilégios',
        'ocorrencia' => 'Ocorrências',
        'avaliacoes' => 'Avaliações',
        'avaliacao' => 'Avaliação',
        'historico' => 'Histórico',
        'cadastro' => 'Cadastros',
        'usuario' => 'Usuários',
        'cloud' => 'Cloud',
        'site' => 'Site',
    ];

    /** Sufixos de ação conhecidos (mais longos primeiro). */
    private const ACAO_SUFIXOS = [
        'retirar_treinamento_realizado' => 'Retirar treinamento realizado',
        'aprova_reprova' => 'Aprovar / reprovar',
        'privilegio_adm' => 'Privilégio administrador',
        'ver_todas' => 'Ver todas',
        'funcionarios' => 'Associar funcionários',
        'vincular_avaliadores' => 'Vincular avaliadores',
        'alterar-senha' => 'Alterar senha',
        'realizar-lancamento' => 'Realizar lançamento',
        'processo_demitir' => 'Demitir no processo',
        'filtrar_demitido' => 'Filtrar demitidos',
        'active' => 'Ativar / desativar',
        'insert' => 'Inserir',
        'update' => 'Alterar',
        'delete' => 'Apagar',
        'deletar' => 'Apagar',
        'editar' => 'Editar',
        'export' => 'Exportar',
        'pdf' => 'PDF',
        'show' => 'Visualizar',
        'enviar' => 'Enviar',
        'aprovar' => 'Aprovar',
        'lancar' => 'Lançar',
        'avaliar' => 'Avaliar',
        'final' => 'Avaliação final',
        'listar' => 'Listar',
        'adm' => 'Administrar',
    ];

    /** Labels amigáveis para recursos frequentes. */
    private const RECURSO_LABELS = [
        'habilidades' => 'Habilidades',
        'papel' => 'Grupos de usuários',
        'usuarios' => 'Usuários',
        'clientes' => 'Clientes',
        'cih' => 'CIH',
        'documentos_legais' => 'Documentos legais',
        'documentos_legais_contrato' => 'Documentos legais — Contrato',
        'documentos_legais_documentos_empresa' => 'Documentos legais — Empresa',
        'documentos_legais_documentos_ssma' => 'Documentos legais — SSMA',
        'documentos_legais_tipos_documentos' => 'Documentos legais — Tipos documentos',
        'documentos_legais_tipos_servicos' => 'Documentos legais — Tipos serviços',
        'documentos_legais_formas_contratos' => 'Documentos legais — Formas contratos',
        'carteira-etiquetas' => 'Carteira de etiquetas',
        'folha-ponto' => 'Folha de ponto',
        'folha_ponto_manual' => 'Folha de ponto manual',
        'ponto-eletronico' => 'Ponto eletrônico',
        'ajustar-jornadas' => 'Ajuste de jornadas',
        'plano-conta' => 'Planos de conta',
        'classificacao-plano-conta' => 'Classificação planos de conta',
        'formas-pagamento' => 'Formas de pagamento',
        'fluxo-caixa' => 'Fluxo de caixa',
        'movimentacao' => 'Movimentação',
        'aprovacao' => 'Aprovação',
        'geral' => 'Geral',
    ];

    /**
     * @return array{
     *     nome: string,
     *     modulo: string,
     *     recurso: string,
     *     acao: string,
     *     descricao: string|null,
     *     aliases: list<string>,
     *     modulo_label: string,
     *     recurso_label: string,
     *     acao_label: string
     * }
     */
    public static function parse(string $nome, ?string $descricao = null): array
    {
        // Unifica pós-admissão canônica (admissao_pos_*) e legado (posadmissao_*) sob módulo Admissão.
        // Menu access admissao_pos_admissao* cai no parser padrão de módulo.
        if ($nome === 'posadmissao' || str_starts_with($nome, 'posadmissao_')
            || (str_starts_with($nome, 'admissao_pos_') && !str_starts_with($nome, 'admissao_pos_admissao'))) {
            if (str_starts_with($nome, 'admissao_pos_')) {
                $resto = substr($nome, strlen('admissao_pos_'));
            } else {
                $resto = $nome === 'posadmissao' ? '' : substr($nome, strlen('posadmissao_'));
            }
            [$recurso, $acao, $acaoLabel] = self::splitRecursoAcao($resto, $nome);
            if ($recurso === 'geral' && $resto === '') {
                $recurso = 'pos_admissao';
            } elseif ($recurso !== 'geral' && !str_starts_with($recurso, 'pos_')) {
                $recurso = 'pos_' . $recurso;
            } elseif ($recurso === 'geral') {
                $recurso = 'pos_admissao';
            }

            return [
                'nome' => $nome,
                'modulo' => 'admissao',
                'recurso' => $recurso,
                'acao' => $acao,
                'descricao' => $descricao,
                'aliases' => [],
                'modulo_label' => 'Admissão',
                'recurso_label' => self::RECURSO_LABELS[$recurso]
                    ?? ('Pós-admissão — ' . self::labelize(preg_replace('/^pos_/', '', $recurso) ?? $recurso)),
                'acao_label' => $acaoLabel,
            ];
        }

        // historico_dossie_* → módulo Admissão / recurso dossie (agrupa com histórico)
        if (str_starts_with($nome, 'historico_')) {
            $resto = substr($nome, strlen('historico_'));
            [$recurso, $acao, $acaoLabel] = self::splitRecursoAcao($resto, $nome);

            return [
                'nome' => $nome,
                'modulo' => 'admissao',
                'recurso' => 'historico_' . ($recurso === 'geral' ? 'geral' : $recurso),
                'acao' => $acao,
                'descricao' => $descricao,
                'aliases' => [],
                'modulo_label' => 'Admissão',
                'recurso_label' => 'Histórico — ' . self::labelize($recurso === 'geral' ? 'geral' : $recurso),
                'acao_label' => $acaoLabel,
            ];
        }

        if (str_starts_with($nome, 'avaliacao_noventa')) {
            $resto = substr($nome, strlen('avaliacao_'));
            [$recurso, $acao, $acaoLabel] = self::splitRecursoAcao($resto, $nome);

            return [
                'nome' => $nome,
                'modulo' => 'admissao',
                'recurso' => 'avaliacao_' . ($recurso === 'geral' ? 'noventa' : $recurso),
                'acao' => $acao,
                'descricao' => $descricao,
                'aliases' => [],
                'modulo_label' => 'Admissão',
                'recurso_label' => 'Avaliação 90 dias',
                'acao_label' => $acaoLabel,
            ];
        }

        [$modulo, $resto, $moduloLabel] = self::splitModulo($nome);

        [$recurso, $acao, $acaoLabel] = self::splitRecursoAcao($resto, $nome);

        return [
            'nome' => $nome,
            'modulo' => $modulo,
            'recurso' => $recurso,
            'acao' => $acao,
            'descricao' => $descricao,
            'aliases' => [],
            'modulo_label' => $moduloLabel,
            'recurso_label' => self::RECURSO_LABELS[$recurso] ?? self::labelize($recurso),
            'acao_label' => $acaoLabel,
        ];
    }

    public static function toDefinition(string $nome, string $descricao): HabilidadeDefinition
    {
        $meta = self::parse($nome, $descricao);

        return new HabilidadeDefinition(
            nome: $meta['nome'],
            modulo: $meta['modulo'],
            recurso: $meta['recurso'],
            acao: $meta['acao'],
            descricao: $descricao,
            aliases: [],
            moduloLabel: $meta['modulo_label'],
            recursoLabel: $meta['recurso_label'],
            acaoLabel: $meta['acao_label'],
        );
    }

    /**
     * @return array{0: string, 1: string, 2: string} modulo, resto, moduloLabel
     */
    private static function splitModulo(string $nome): array
    {
        foreach (self::MODULO_PREFIXOS as $prefixo => $label) {
            if ($nome === $prefixo) {
                return [$prefixo, '', $label];
            }
            $needle = $prefixo . '_';
            if (str_starts_with($nome, $needle)) {
                return [$prefixo, substr($nome, strlen($needle)), $label];
            }
        }

        $partes = explode('_', $nome);
        $modulo = $partes[0] ?? 'outros';
        $resto = implode('_', array_slice($partes, 1));

        return [$modulo, $resto, self::labelize($modulo)];
    }

    /**
     * @return array{0: string, 1: string, 2: string} recurso, acao, acaoLabel
     */
    private static function splitRecursoAcao(string $resto, string $nomeCompleto): array
    {
        if ($resto === '') {
            return ['geral', 'access', 'Acessar'];
        }

        // Abas do histórico / movimentação: recurso = prefixo estável, ação = resto.
        if (str_starts_with($resto, 'historico_aba_')) {
            return ['historico', substr($resto, strlen('historico_')), self::labelize(substr($resto, strlen('historico_')))];
        }
        if (str_starts_with($resto, 'movimentacao_exibir_aba_')) {
            return ['movimentacao', substr($resto, strlen('movimentacao_')), self::labelize(substr($resto, strlen('movimentacao_')))];
        }
        if (str_starts_with($resto, 'movimentacao_ferias_')) {
            $acao = substr($resto, strlen('movimentacao_ferias_'));

            return ['movimentacao_ferias', $acao, self::ACAO_SUFIXOS[$acao] ?? self::labelize($acao)];
        }
        if (str_starts_with($resto, 'quadro_lista_')) {
            $acao = substr($resto, strlen('quadro_lista_'));

            return ['quadro_lista', $acao, self::ACAO_SUFIXOS[$acao] ?? self::labelize($acao)];
        }
        if (str_starts_with($resto, 'quadro_tarefa_')) {
            $acao = substr($resto, strlen('quadro_tarefa_'));

            return ['quadro_tarefa', $acao, self::ACAO_SUFIXOS[$acao] ?? self::labelize($acao)];
        }
        if (str_starts_with($resto, 'quadro_')) {
            $acao = substr($resto, strlen('quadro_'));

            return ['quadro', $acao, self::ACAO_SUFIXOS[$acao] ?? self::labelize($acao)];
        }

        foreach (self::ACAO_SUFIXOS as $sufixo => $label) {
            if ($resto === $sufixo) {
                return ['geral', $sufixo, $label];
            }
            $needle = '_' . $sufixo;
            if (str_ends_with($resto, $needle)) {
                $recurso = substr($resto, 0, -strlen($needle));

                return [$recurso !== '' ? $recurso : 'geral', $sufixo, $label];
            }
        }

        // Sem sufixo de ação conhecido → recurso completo, ação access.
        return [$resto, 'access', 'Acessar'];
    }

    private static function labelize(string $slug): string
    {
        $slug = str_replace(['-', '_'], ' ', $slug);

        return mb_convert_case($slug, MB_CASE_TITLE, 'UTF-8');
    }
}
