import { test as setup, expect } from '@playwright/test';
import fs from 'fs';
import path from 'path';

const authDir = path.join(process.cwd(), 'playwright', '.auth');
const authFile = path.join(authDir, 'user.json');
const forceLogin = process.env.PLAYWRIGHT_FORCE_LOGIN === '1';
const email = process.env.PLAYWRIGHT_LOGIN ?? process.env.PLAYWRIGHT_EMAIL;
const password = process.env.PLAYWRIGHT_PASSWORD;

setup('autenticação — regressão plataforma', async ({ page }) => {
    setup.setTimeout(600_000);

    if (!fs.existsSync(authDir)) {
        fs.mkdirSync(authDir, { recursive: true });
    }

    if (fs.existsSync(authFile) && !forceLogin) {
        setup.skip(true, 'Sessão em playwright/.auth/user.json — PLAYWRIGHT_FORCE_LOGIN=1 para renovar');
        return;
    }

    await page.goto('/g/login');

    if (email && password) {
        await page.locator('#login').fill(email);
        await page.locator('#password').fill(password);
        await page.locator('button[type="submit"], input[type="submit"]').first().click();
        await page.waitForURL((url) => !url.pathname.includes('/g/login'), { timeout: 60_000 });
    } else {
        // eslint-disable-next-line no-console
        console.log('\n╔══════════════════════════════════════════════════════════════╗');
        // eslint-disable-next-line no-console
        console.log('║  PLAYWRIGHT — LOGIN MANUAL (Regressão MyBPIN)                ║');
        // eslint-disable-next-line no-console
        console.log('║  1. Faça login no Chrome                                     ║');
        // eslint-disable-next-line no-console
        console.log('║  2. Aceite termos / NPS / telefone se aparecer               ║');
        // eslint-disable-next-line no-console
        console.log('║  3. Confirme dashboard / menu                                ║');
        // eslint-disable-next-line no-console
        console.log('║  4. No Inspector, clique RESUME (▶)                          ║');
        // eslint-disable-next-line no-console
        console.log('║  Opcional: PLAYWRIGHT_LOGIN + PLAYWRIGHT_PASSWORD            ║');
        // eslint-disable-next-line no-console
        console.log('╚══════════════════════════════════════════════════════════════╝\n');
        await page.pause();
    }

    await expect(page).not.toHaveURL(/\/g\/login\/?$/);

    // Fecha overlays comuns que bloqueiam navegação
    const aceite = page.getByLabel(/declaro que li e aceito/i);
    if (await aceite.isVisible().catch(() => false)) {
        await aceite.check();
        const confirmar = page.getByRole('button', { name: /aceitar|confirmar|continuar/i }).first();
        if (await confirmar.isVisible().catch(() => false)) {
            await confirmar.click();
        }
    }

    await page.context().storageState({ path: authFile });
    await page.context().close();
});
