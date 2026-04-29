<?php
/**
 * Uninstall routine — runs when the plugin is deleted from WP Admin.
 *
 * @package WooSmartFaq
 */

// Abort if not called by WordPress during uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}
