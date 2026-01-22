<?php
/**
 * PDF Builder Pro - Preview System
 * Centralized preview system loader
 */

namespace PDF_Builder\PreviewSystem;

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

// Load preview system components
require_once __DIR__ . '/php/PreviewSystem.php';
require_once __DIR__ . '/php/PreviewImageAPI.php';
require_once __DIR__ . '/php/PreviewAjaxHandler.php';

// Export main classes for backward compatibility
class Api {
    // Alias for backward compatibility
    public static function load() {
        // Preview system is minimal - no need for boot
    }
}
