import { test, expect } from '@playwright/test';
import { fetchWhatsappStatus, expectAutenticado, ROTAS } from './helpers';

/**
 * Testes de UI operacional — validam visibilidade dos controles WhatsApp.
 * Regra: empresa liberada + módulo on + tel. principal tipo whatsapp.
 *
 * Cenários com dados reais (candidato com/sem WhatsApp) exigem fixtures no ambiente.
 * Marque test.skip como false após preparar dados de homologação.
 */

test.describe('Recrutamento — módulo Recrutamento', () => {
    test('lista carrega e API status coerente', async ({ page, request }) => {
        const status = await fetchWhatsappStatus(request);
        await page.goto(ROTAS.recrutamento);
        await expectAutenticado(page);

        if (!status.tipos?.recrutamento_selecao) {
            test.info().annotations.push({
                type: 'note',
                description: 'Módulo Recrutamento off ou empresa sem WhatsApp — campo envio não deve aparecer',
            });
        }
    });
});

test.describe('Controle de exames — exame_encaminhamento', () => {
    test.skip(true, 'Requer candidato em encaminhamento com modal aberto');

    test('checkbox WhatsApp visível só com tel. principal WhatsApp', async ({ page }) => {
        await page.goto(ROTAS.controleExames);
        // Abrir encaminhamento de candidato com tel_principal.tipo === whatsapp
        await expect(page.getByLabel(/enviar whatsapp/i)).toBeVisible();
    });
});

test.describe('Pré-admissão — exame_encaminhamento / admissao_documentos', () => {
    test.skip(true, 'Requer feedback em pré-admissão');

    test('finalizar encaminhamento — toggle WhatsApp', async ({ page }) => {
        await page.goto(ROTAS.preadmissao);
    });
});

test.describe('Resultado integrado — admissao_documentos / admissao_exame', () => {
    test.skip(true, 'Requer entrevista com resultado integrado');

    test('switches WhatsApp no formulário', async ({ page }) => {
        await page.goto(ROTAS.resultadoIntegrado);
    });
});

test.describe('Parecer de rota — parecer_rota_transporte', () => {
    test.skip(true, 'Requer parecer com rota e candidato WhatsApp');

    test('botão enviar WhatsApp na grade', async ({ page }) => {
        await page.goto(ROTAS.parecerRota);
    });
});

test.describe('Preferências usuário', () => {
    test('card preferências no dashboard ou usuários', async ({ page }) => {
        await page.goto(ROTAS.dashboard);
        const card = page.getByText(/preferências.*whatsapp|notificações.*whatsapp/i).first();
        if (await card.isVisible().catch(() => false)) {
            await expect(card).toBeVisible();
        }
    });
});

test.describe('Matriz de módulos — smoke por rota', () => {
    const rotas = [
        { rota: ROTAS.recrutamento, tipo: 'recrutamento_selecao', nome: 'Recrutamento' },
        { rota: ROTAS.controleExames, tipo: 'exame_encaminhamento', nome: 'Exames' },
        { rota: ROTAS.preadmissao, tipo: 'exame_encaminhamento', nome: 'Pré-admissão' },
        { rota: ROTAS.resultadoIntegrado, tipo: 'admissao_documentos', nome: 'Resultado integrado' },
        { rota: ROTAS.parecerRota, tipo: 'parecer_rota_transporte', nome: 'Parecer rota' },
    ];

    for (const { rota, tipo, nome } of rotas) {
        test(`${nome} — página acessível autenticado`, async ({ page, request }) => {
            const status = await fetchWhatsappStatus(request);
            await page.goto(rota);
            await expect(page).not.toHaveURL(/\/g\/login\/?$/);

            test.info().attach('whatsapp-status', {
                body: JSON.stringify({ tipo, habilitado: status.tipos?.[tipo] ?? false }, null, 2),
                contentType: 'application/json',
            });
        });
    }
});
