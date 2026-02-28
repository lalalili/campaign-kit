#!/usr/bin/env node

import fs from 'node:fs/promises';
import path from 'node:path';
import { chromium } from 'playwright';

const parseArgs = () => {
    const result = {};

    for (const arg of process.argv.slice(2)) {
        if (!arg.startsWith('--')) {
            continue;
        }

        const [rawKey, ...rest] = arg.slice(2).split('=');
        const key = rawKey.trim();
        const value = rest.join('=').trim();

        if (key !== '') {
            result[key] = value;
        }
    }

    return result;
};

const args = parseArgs();
const url = args.url;
const output = args.output;
const waitForSelector = args['wait-for-selector'] || '.campaign-preview-root';
const width = Number.parseInt(args.width || '1366', 10);
const height = Number.parseInt(args.height || '1024', 10);
const ignoreHttpsErrors = String(args['ignore-https-errors'] || '0') === '1';
const toolbarSelectors = [
    '[id^="sfwdt"]',
    '[id^="sfToolbar"]',
    '.sf-toolbarreset',
    '.sf-minitoolbar',
    '.sf-toolbar',
];

if (!url || !output) {
    console.error('Missing required arguments: --url and --output');
    process.exit(1);
}

const run = async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width, height },
        ignoreHTTPSErrors: ignoreHttpsErrors,
    });
    const page = await context.newPage();

    try {
        await page.goto(url, { waitUntil: 'networkidle', timeout: 120000 });
        await page.waitForSelector(waitForSelector, { state: 'visible', timeout: 60000 });
        await page.addStyleTag({
            content: `${toolbarSelectors.join(',')} { display: none !important; visibility: hidden !important; }`,
        });
        await page.evaluate((selectors) => {
            for (const selector of selectors) {
                for (const node of document.querySelectorAll(selector)) {
                    node.remove();
                }
            }
        }, toolbarSelectors);
        await fs.mkdir(path.dirname(output), { recursive: true });
        await page.screenshot({ path: output, type: 'png', fullPage: true });
    } finally {
        await page.close();
        await context.close();
        await browser.close();
    }
};

run().catch((error) => {
    const message = error instanceof Error ? error.stack || error.message : String(error);
    console.error(message);
    process.exit(1);
});
