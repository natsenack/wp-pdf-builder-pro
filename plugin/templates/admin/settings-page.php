<?php
/**
 * PDF Builder Pro - Settings Page (Refactored)
 * Main settings dispatcher - includes modular parts
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

// Include all settings parts
$settings_parts_dir = plugin_dir_path(__FILE__) . 'settings-parts/';

// Include AJAX handlers first (they need to run before any HTML output)
require_once $settings_parts_dir . 'settings-ajax.php';

// Include main settings logic
require_once $settings_parts_dir . 'settings-main.php';

// Include styles and scripts
require_once $settings_parts_dir . 'settings-styles.php';
require_once $settings_parts_dir . 'settings-scripts.php';

// Include individual tab contents
require_once $settings_parts_dir . 'settings-general.php';
require_once $settings_parts_dir . 'settings-licence.php';
require_once $settings_parts_dir . 'settings-systeme.php';
require_once $settings_parts_dir . 'settings-acces.php';
require_once $settings_parts_dir . 'settings-securite.php';
require_once $settings_parts_dir . 'settings-pdf.php';
require_once $settings_parts_dir . 'settings-contenu.php';
require_once $settings_parts_dir . 'settings-developpeur.php';

// Include modal components
require_once $settings_parts_dir . 'settings-modals.php';