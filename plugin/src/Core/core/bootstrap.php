<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed

/**
 * PDF Builder Pro - Core Bootstrap
 * Minimal bootstrap for tests - delegates to main bootstrap
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    if ( ! defined( 'PHPUNIT_RUNNING' ) ) {
        exit( 'Direct access not allowed' );
    }
}
}

// For tests, just load the main bootstrap if it exists
if (defined('PHPUNIT_RUNNING')) {
    $main_bootstrap = __DIR__ . '/../bootstrap.php';
    if (file_exists($main_bootstrap)) {
        require_once $main_bootstrap;
    }
}



