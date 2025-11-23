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

// Include canvas parameters first (defines window.pdfBuilderCanvasSettings)
require_once $settings_parts_dir . 'settings-canvas-params.php';

// Include main settings logic
require_once $settings_parts_dir . 'settings-main.php';

// Include styles and scripts
require_once $settings_parts_dir . 'settings-styles.php';
require_once $settings_parts_dir . 'settings-scripts.php';

// Include modal components
require_once $settings_parts_dir . 'settings-modals.php';