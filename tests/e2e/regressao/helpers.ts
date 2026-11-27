import { Page, expect, test } from '@playwright/test';

/** Empresa padrão do smoke (override: PLAYWRIGHT_EMPRESA_ID). */
export const EMPRESA_E2E = Number(process.env.PLAYWRIGHT_EMPRESA_ID ?? 104);

export async function expectAutenticado(page: Page): Promise<void> {
    await expect(page).not.toHaveURL(/\/g\/login\/?$/);
}

/**
 * Navega e valida que a página não redirecionou para login nem exibiu erro fatal.
 * Telas sem permissão podem 403/redirect — use `permitirNegado` nesse caso.
 */
export async function expectPaginaOperacional(
    page: Page,
    path: string,
    options: { permitirNegado?: boolean; titulo?: RegExp } = {},
): Promise<'ok' | 'negado'> {
    const response = await page.goto(path, { waitUntil: 'domcontentloaded' });
    const status = response?.status() ?? 0;

    if (page.url().includes('/g/login')) {
        throw new Error(`Sessão inválida ao abrir ${path}`);
    }

    // Redirect 3xx (ex.: index → hub movimentação) é aceitável
    if (status >= 500) {
        throw new Error(`HTTP ${status} em ${path}`);
    }

    const corpo = page.locator('body');
    await expect(corpo).toBeVisible();

    const texto = (await corpo.innerText().catch(() => '')).toLowerCase();

    if (/não encontramos esta página|pagina não encontrada|página não encontrada/.test(texto) || status === 404) {
        throw new Error(`404 em ${path}`);
    }

    const negado =
        status === 403 ||
        /não autorizado|nao autorizado|forbidden|acesso negado|sem permissão|sem permissao/.test(texto);

    if (negado) {
        if (options.permitirNegado) {
            return 'negado';
        }
        throw new Error(`Acesso negado em ${path}`);
    }

    if (options.titulo) {
        await expect(page.getByText(options.titulo).first()).toBeVisible({ timeout: 20_000 });
    }

    return 'ok';
}

export type RotaSmoke = {
    id: string;
    path: string;
    modulo: string;
    prioridade: 'P0' | 'P1' | 'P2';
    /** Se true, 403/sem permissão não falha o smoke. */
    permitirNegado?: boolean;
};

/** Inventário canônico de rotas para regressão (páginas index/listagem). */
export const ROTAS_REGRESSAO: RotaSmoke[] = [
    // Sessão / home
    { id: 'AUTH-01', path: '/g/dashboard', modulo: 'Auth', prioridade: 'P0' },

    // Admissão
    { id: 'ADM-01', path: '/g/admissao/admissao', modulo: 'Admissão', prioridade: 'P0' },
    { id: 'ADM-02', path: '/g/preadmissao', modulo: 'Pré-admissão', prioridade: 'P0' },
    { id: 'ADM-03', path: '/g/posadmissao', modulo: 'Pós-admissão', prioridade: 'P0' },
    { id: 'ADM-04', path: '/g/historico', modulo: 'Histórico', prioridade: 'P0' },
    { id: 'ADM-05', path: '/g/controle-exames', modulo: 'Controle exames', prioridade: 'P0' },
    { id: 'ADM-06', path: '/g/admissao/import', modulo: 'Importação admissão', prioridade: 'P1', permitirNegado: true },
    { id: 'ADM-07', path: '/g/apontamento/cih', modulo: 'CIH', prioridade: 'P1', permitirNegado: true },
    { id: 'ADM-08', path: '/g/apontamento/intermitente', modulo: 'Intermitente', prioridade: 'P1', permitirNegado: true },

    // Planejamento / movimentação
    { id: 'PLA-01', path: '/g/planejamento/movimentacao', modulo: 'Movimentação', prioridade: 'P0' },
    { id: 'PLA-02', path: '/g/planejamento/requisicao-vaga', modulo: 'Requisição de vaga', prioridade: 'P0' },
    { id: 'PLA-03', path: '/g/planejamento/mobilizacao', modulo: 'Mobilização', prioridade: 'P1', permitirNegado: true },
    { id: 'PLA-04', path: '/g/planejamento/movimentacao/ferias-prevista', modulo: 'Férias prevista', prioridade: 'P0' },
    { id: 'PLA-05', path: '/g/planejamento/movimentacao/demissao-prevista', modulo: 'Demissão prevista', prioridade: 'P0' },
    { id: 'PLA-06', path: '/g/planejamento/movimentacao/transferencia-prevista', modulo: 'Transferência', prioridade: 'P0' },
    { id: 'PLA-07', path: '/g/planejamento/movimentacao/valor-extra-prevista', modulo: 'Valor extra', prioridade: 'P0' },
    { id: 'PLA-08', path: '/g/planejamento/movimentacao/mudanca-cargo', modulo: 'Mudança cargo', prioridade: 'P1' },
    { id: 'PLA-09', path: '/g/planejamento/movimentacao/admissoes-prevista', modulo: 'Admissão prevista', prioridade: 'P1' },
    { id: 'PLA-10', path: '/g/planejamento/movimentacao/intermitente-fixo-prevista', modulo: 'Intermitente fixo', prioridade: 'P1', permitirNegado: true },

    // Recrutamento / entrevistas
    { id: 'REC-01', path: '/g/curriculos/recrutamentos', modulo: 'Recrutamento', prioridade: 'P0' },
    { id: 'REC-02', path: '/g/curriculos/curriculos-selecionados', modulo: 'Currículos selecionados', prioridade: 'P1', permitirNegado: true },
    { id: 'ENT-01', path: '/g/entrevistas/parecer_rh', modulo: 'Parecer RH', prioridade: 'P1', permitirNegado: true },
    { id: 'ENT-02', path: '/g/entrevistas/parecer-rota', modulo: 'Parecer rota', prioridade: 'P1', permitirNegado: true },
    { id: 'ENT-03', path: '/g/entrevistas/resultado-integrado', modulo: 'Resultado integrado', prioridade: 'P1', permitirNegado: true },

    // Customizações / config
    { id: 'CFG-01', path: '/g/administracao/aprovacao-extra-config', modulo: 'Aprovação Extra', prioridade: 'P0', permitirNegado: true },
    { id: 'CFG-02', path: '/g/configuracoes/whatsapp', modulo: 'WhatsApp config', prioridade: 'P1', permitirNegado: true },
    { id: 'CFG-03', path: '/g/configuracoes/notificacoes', modulo: 'Notificações', prioridade: 'P0', permitirNegado: true },

    // Cadastros
    { id: 'CAD-01', path: '/g/cadastro/centrocusto', modulo: 'Centro de custo', prioridade: 'P0' },
    { id: 'CAD-02', path: '/g/cadastro/vagas', modulo: 'Vagas', prioridade: 'P0' },
    { id: 'CAD-03', path: '/g/cadastro/vagas-abertas', modulo: 'Vagas abertas', prioridade: 'P1', permitirNegado: true },
    { id: 'CAD-04', path: '/g/cadastro/documentos-preadmissao', modulo: 'Docs pré-admissão', prioridade: 'P1', permitirNegado: true },

    // Operacional / financeiro
    { id: 'OPS-01', path: '/g/ocorrencia', modulo: 'Ocorrências', prioridade: 'P1', permitirNegado: true },
    { id: 'OPS-02', path: '/g/cadastro/avaliacoes/avaliar', modulo: 'Avaliar', prioridade: 'P2', permitirNegado: true },
    { id: 'FIN-01', path: '/g/plano-conta', modulo: 'Plano de contas', prioridade: 'P2', permitirNegado: true },
];

export function rotasPorPrioridade(...niveis: Array<RotaSmoke['prioridade']>): RotaSmoke[] {
    return ROTAS_REGRESSAO.filter((r) => niveis.includes(r.prioridade));
}

export function anotarSkipPermissao(id: string, path: string): void {
    test.info().annotations.push({
        type: 'note',
        description: `${id} ${path}: sem permissão na sessão atual (aceitável no smoke)`,
    });
}
