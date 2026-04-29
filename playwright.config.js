import { chromium } from '@playwright/test';

/**
 * Playwright global setup — configures base URL and auth state.
 */
const config = {
    testDir: './tests/e2e/playwright',
    timeout: 30_000,
    retries: process.env.CI ? 2 : 0,
    use: {
        baseURL: process.env.WP_BASE_URL ?? 'http://localhost:8888',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
        trace: 'on-first-retry',
    },
    projects: [
        {
            name: 'chromium',
            use: { browserName: 'chromium' },
        },
    ],
    reporter: [
        ['html', { outputFolder: 'playwright-report' }],
        ['list'],
    ],
};

export default config;
