import { test as setup, expect } from '@playwright/test';
import fs from 'fs';
import path from 'path';

const authDir = path.join(process.cwd(), 'playwright', '.auth');
const authFile = path.join(authDir, 'user.json');

const forceLogin = process.env.PLAYWRIGHT_FORCE_LOGIN === '1';

setup('login manual — Chrome aguarda autenticação', async ({ page }) => {
    setup.setTimeout(600_000);

    if (!fs.existsSync(authDir)) {
        fs.mkdirSync(authDir, { recursive: true });
    }

    if (fs.existsSync(authFile) && !forceLogin) {
        setup.skip(true, 'Sessão existente em playwright/.auth/user.json — use PLAYWRIGHT_FORCE_LOGIN=1 para refazer login');
        return;
    }

    await page.goto('/g/login');

    // eslint-disable-next-line no-console
    console.log('\n╔══════════════════════════════════════════════════════════════╗');
    // eslint-disable-next-line no-console
    console.log('║  PLAYWRIGHT — LOGIN MANUAL (WhatsApp MyBP)                   ║');
    // eslint-disable-next-line no-console
    console.log('╠══════════════════════════════════════════════════════════════╣');
    // eslint-disable-next-line no-console
    console.log('║  1. Faça login no Chrome                                     ║');
    // eslint-disable-next-line no-console
    console.log('║  2. Aceite termos / NPS / telefone se aparecer               ║');
    // eslint-disable-next-line no-console
    console.log('║  3. Use usuário da empresa_id=100 + habilidade configuracao_whatsapp ║');
    // eslint-disable-next-line no-console
    console.log('║  4. Confirme que está no dashboard ou menu principal          ║');
    // eslint-disable-next-line no-console
    console.log('║  5. No Playwright Inspector, clique em RESUME (▶)            ║');
    // eslint-disable-next-line no-console
    console.log('╚══════════════════════════════════════════════════════════════╝\n');

    await page.pause();

    await expect(page).not.toHaveURL(/\/g\/login\/?$/);

    await page.context().storageState({ path: authFile });
    await page.context().close();
});
