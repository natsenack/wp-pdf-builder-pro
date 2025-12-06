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

// Include main settings logic (contains all navigation and tabs)
require_once plugin_dir_path(__FILE__) . 'settings-parts/settings-main.php';

// Add floating save button and JavaScript here for better execution context
?>
<!-- Bouton de sauvegarde flottant global -->
<div id="pdf-builder-save-floating" class="pdf-builder-save-floating">
    <button type="button" id="pdf-builder-save-floating-btn" class="button button-primary button-hero pdf-builder-save-btn">
        <span class="dashicons dashicons-saved"></span>
        💾 Enregistrer
    </button>
</div>


<style>
.pdf-builder-save-floating {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 999999;
}
.pdf-builder-save-floating .button {
    background: #007cba;
    color: white;
    border: 1px solid #007cba;
    font-size: 14px;
    padding: 8px 16px;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.pdf-builder-save-floating .button:hover {
    background: #005a87;
    border-color: #005a87;
}
</style>
<?php

// All JavaScript moved to settings-main.php to avoid conflicts
