import { test, expect } from '@playwright/test';

/**
 * E2E tests for product FAQ metabox.
 */
test.describe( 'Product FAQ Metabox', () => {
    test.beforeEach( async ( { page } ) => {
        await page.goto( '/wp-login.php' );
    } );
} );
