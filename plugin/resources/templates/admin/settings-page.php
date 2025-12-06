<?php
/**
 * PDF Builder Pro - Settings Page (Simplified)
 * Main settings dispatcher - includes modular parts
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

// Include settings loader (loads JavaScript and CSS assets)
require_once plugin_dir_path(__FILE__) . 'settings-loader.php';

// DEBUG: Add visible marker to confirm this file is executed
echo '<div style="background: yellow; color: red; padding: 20px; border: 2px solid red; font-size: 20px; font-weight: bold; position: fixed; top: 10px; left: 10px; z-index: 99999;">';
echo '🚨 DEBUG: settings-page.php IS EXECUTED! File loaded successfully.';
echo '</div>';

// Include main settings logic (contains all navigation and tabs)
require_once plugin_dir_path(__FILE__) . 'settings-parts/settings-main.php';

// Add floating save button and JavaScript here for better execution context
echo '<!-- PDF BUILDER SETTINGS PAGE LOADED - DEBUG MARKER -->';

// All JavaScript moved to settings-main.php to avoid conflicts
