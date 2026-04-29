import { test, expect } from '@playwright/test';

/**
 * E2E tests for frontend FAQ display.
 */
test.describe( 'Frontend FAQ Display', () => {
    test( 'accordion is present on product page', async ( { page } ) => {
        await page.goto( '/product/test-product/' );
        await expect( page.locator( '.wsf-faq' ) ).toBeVisible();
    } );
} );
