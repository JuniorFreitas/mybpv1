/**
 * Smoke manual via Chromium (útil quando a sessão do Cursor Browser está ativa
 * e queremos validar rotas sem storageState do Playwright).
 *
 * Uso:
 *   PLAYWRIGHT_LOGIN=... PLAYWRIGHT_PASSWORD=... node tests/e2e/regressao/browser-smoke.mjs
 *   # ou: node ... --manual   (abre headed e espera Enter no terminal após login)
 */
import { chromium } from '@playwright/test';
import fs from 'fs';
import path from 'path';
import readline from 'readline';

const baseURL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8000';
const authFile = path.join(process.cwd(), 'playwright', '.auth', 'user.json');
const manual = process.argv.includes('--manual');

const rotas = [
    '/g/dashboard',
    '/g/admissao/admissao',
    '/g/preadmissao',
    '/g/posadmissao',
    '/g/historico',
    '/g/controle-exames',
    '/g/planejamento/movimentacao',
    '/g/planejamento/requisicao-vaga',
    '/g/planejamento/movimentacao/ferias-prevista',
    '/g/planejamento/movimentacao/demissao-prevista',
    '/g/curriculos/recrutamentos',
    '/g/cadastro/centrocusto',
    '/g/cadastro/vagas',
    '/g/administracao/aprovacao-extra-config',
];

async function waitEnter(msg) {
    const rl = readline.createInterface({ input: process.stdin, output: process.stdout });
    await new Promise((resolve) => rl.question(msg, () => { rl.close(); resolve(); }));
}

const browser = await chromium.launch({
    channel: 'chrome',
    headless: !manual && !(process.env.PLAYWRIGHT_LOGIN && process.env.PLAYWRIGHT_PASSWORD),
});
const context = await browser.newContext({ locale: 'pt-BR' });
const page = await context.newPage();

await page.goto(`${baseURL}/g/login`);

if (process.env.PLAYWRIGHT_LOGIN && process.env.PLAYWRIGHT_PASSWORD) {
    await page.locator('#login').fill(process.env.PLAYWRIGHT_LOGIN);
    await page.locator('#password').fill(process.env.PLAYWRIGHT_PASSWORD);
    await page.locator('button[type="submit"], input[type="submit"]').first().click();
    await page.waitForURL((u) => !u.pathname.includes('/g/login'), { timeout: 60_000 });
} else {
    console.log('Faça login no Chrome aberto. Depois pressione Enter neste terminal...');
    await waitEnter('');
}

await context.storageState({ path: authFile });
console.log(`Sessão salva em ${authFile}`);

const resultados = [];
for (const rota of rotas) {
    const res = await page.goto(`${baseURL}${rota}`, { waitUntil: 'domcontentloaded' });
    const status = res?.status() ?? 0;
    const url = page.url();
    const ok = !url.includes('/g/login') && status < 500 && status !== 404;
    resultados.push({ rota, status, ok, url });
    console.log(`${ok ? 'PASS' : 'FAIL'} ${status} ${rota}`);
}

await browser.close();

const falhas = resultados.filter((r) => !r.ok);
fs.mkdirSync(path.join(process.cwd(), 'output', 'playwright'), { recursive: true });
fs.writeFileSync(
    path.join(process.cwd(), 'output', 'playwright', 'browser-smoke-result.json'),
    JSON.stringify({ at: new Date().toISOString(), resultados }, null, 2),
);

if (falhas.length) {
    console.error(`Falhas: ${falhas.length}/${resultados.length}`);
    process.exit(1);
}
console.log(`OK ${resultados.length}/${resultados.length}`);
