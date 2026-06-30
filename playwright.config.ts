import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8000';

export default defineConfig({
    testDir: './tests/e2e',
    timeout: 120_000,
    expect: { timeout: 15_000 },
    fullyParallel: false,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 1 : 0,
    workers: 1,
    reporter: [['list'], ['html', { open: 'never', outputFolder: 'output/playwright/report' }]],
    outputDir: 'output/playwright/test-results',
    use: {
        baseURL,
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
        locale: 'pt-BR',
    },
    projects: [
        {
            name: 'setup',
            testMatch: /auth\.setup\.ts/,
            use: {
                ...devices['Desktop Chrome'],
                channel: 'chrome',
                headless: false,
            },
        },
        {
            name: 'whatsapp-chrome',
            testMatch: /whatsapp\/.*\.spec\.ts/,
            dependencies: ['setup'],
            use: {
                ...devices['Desktop Chrome'],
                channel: 'chrome',
                headless: false,
                storageState: 'playwright/.auth/user.json',
            },
        },
    ],
});
