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
?>
<!-- Bouton de sauvegarde flottant global -->
<div id="pdf-builder-save-floating" class="pdf-builder-save-floating" style="display: block !important; visibility: visible !important; background: red !important; padding: 20px !important; border: 2px solid yellow !important;">
    <button type="button" id="pdf-builder-save-floating-btn" class="button button-primary button-hero pdf-builder-save-btn">
        <span class="dashicons dashicons-yes"></span>
        💾 Enregistrer (DEBUG VISIBLE)
    </button>
    <div id="save-status-indicator" class="save-status-indicator">
        <span id="save-status-text">Prêt à enregistrer</span>
    </div>
</div>

<!-- Styles pour le bouton flottant -->
<style>
.pdf-builder-save-floating {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
}

.pdf-builder-save-btn {
    padding: 12px 24px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    transition: all 0.3s ease !important;
    min-width: 140px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.pdf-builder-save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2) !important;
}

.pdf-builder-save-btn:active {
    transform: translateY(0);
}

.pdf-builder-save-btn.saving {
    opacity: 0.7;
    pointer-events: none;
}

.save-status-indicator {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 12px;
    color: #666;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.save-status-indicator.visible {
    opacity: 1;
}

.save-status-indicator.success {
    color: #46b450;
    border-color: #46b450;
}

.save-status-indicator.error {
    color: #dc3232;
    border-color: #dc3232;
}
</style>
<?php

// All JavaScript moved to settings-main.php to avoid conflicts
