import { APIRequestContext, Page, expect, test } from '@playwright/test';

/** Empresa com WhatsApp configurado em homologação/local (override: PLAYWRIGHT_EMPRESA_ID). */
export const EMPRESA_WHATSAPP_E2E = Number(process.env.PLAYWRIGHT_EMPRESA_ID ?? 100);

export type WhatsappStatus = {
    empresa_id: number;
    whatsapp_liberado: boolean;
    modulos: Record<string, boolean>;
    tipos: Record<string, boolean>;
};

export async function fetchWhatsappStatus(request: APIRequestContext): Promise<WhatsappStatus> {
    const response = await request.get('/g/configuracoes/whatsapp/status');
    expect(response.ok()).toBeTruthy();
    return response.json();
}

export function pularSeEmpresaWhatsappInvalida(status: WhatsappStatus): void {
    if (status.empresa_id !== EMPRESA_WHATSAPP_E2E) {
        test.skip(
            true,
            `Config WhatsApp E2E requer login com empresa_id=${EMPRESA_WHATSAPP_E2E} (sessão atual: ${status.empresa_id})`,
        );
    }
}

export async function expectAutenticado(page: Page): Promise<void> {
    await expect(page).not.toHaveURL(/\/g\/login\/?$/);
}

export const ROTAS = {
    login: '/g/login',
    dashboard: '/g/dashboard',
    configWhatsapp: '/g/configuracoes/whatsapp',
    adminClientes: '/g/administracao/clientes',
    recrutamento: '/g/recrutamentos',
    controleExames: '/g/controle-exames',
    preadmissao: '/g/preadmissao',
    resultadoIntegrado: '/g/resultado-integrado',
    parecerRota: '/g/parecer-rota',
    intermitente: '/g/apontamento/intermitente',
    usuarios: '/g/usuarios',
} as const;
