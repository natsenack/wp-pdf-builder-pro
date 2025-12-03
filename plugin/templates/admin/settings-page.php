<?php
/**
 * PDF Builder Pro - Settings Page (Simplified)
 * Main settings dispatcher - includes modular parts
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

// Include main settings logic (contains all navigation and tabs)
require_once plugin_dir_path(__FILE__) . 'settings-parts/settings-main.php';

// Dummy script to close any unclosed script tags
?>
<script></script>