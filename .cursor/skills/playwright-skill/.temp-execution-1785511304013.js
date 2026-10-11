
const { chromium } = require('playwright');
const BASE = 'http://127.0.0.1:8088';
const PWD = 'Password@123';
;(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ locale: 'pt-BR' });
  await page.goto(BASE + '/bp');
  await page.locator('#email').fill('aluno@bpgrowth.com.br');
  await page.locator('#password').fill(PWD);
  await page.getByRole('button', { name: 'Entrar' }).click();
  await page.waitForLoadState('networkidle');

  // links de trilha - procurar botões/links com 'Continuar'
  const links = await page.locator('a').evaluateAll(as => as.map(a => ({ href: a.getAttribute('href'), text: a.innerText.trim().slice(0,50) })));
  console.log('ALL LINKS:', JSON.stringify(links.filter(l => l.href).slice(0, 30), null, 0));
  await browser.close();
})().catch(e => { console.error('ERRO', e.message); process.exit(1); });
