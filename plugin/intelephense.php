<?php
/**
 * Intelephense Configuration File
 * This file provides configuration for Intelephense PHP language server
 */

// Tell Intelephense to include WordPress stubs
/** @intelephense-file:stub wordpress-stubs.php */
/** @intelephense-file:stub woocommerce-stubs.php */

// Define WordPress constants for better analysis
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', __DIR__ . '/wp-content');
}

if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
}