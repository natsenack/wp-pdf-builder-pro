<?php
/**
 * Preview System Loader
 * Charge tous les fichiers du système d'aperçu centralisé
 * 
 * Architecture:
 * Layer 1: Générateurs (BaseGenerator, PDFGenerator, CanvasGenerator, ImageGenerator, GeneratorManager)
 * Layer 2: Managers (caching, WooCommerce integration, thumbnails)
 * Layer 3: Stub API (UI-only, génération désactivée)
 * Layer 4: Hooks & API (React, TypeScript)
 */

namespace PDF_Builder\PreviewSystem;

if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

// === LAYER 1: GENERATORS ===
// Core generator architecture with automatic fallback system
// BaseGenerator -> PDFGenerator -> CanvasGenerator -> ImageGenerator

require_once __DIR__ . '/generators/BaseGenerator.php';
require_once __DIR__ . '/generators/PDFGenerator.php';
require_once __DIR__ . '/generators/CanvasGenerator.php';
require_once __DIR__ . '/generators/ImageGenerator.php';
require_once __DIR__ . '/generators/GeneratorManager.php';

// === LAYER 2: MANAGERS ===
// Business logic, caching, integration
// TODO: Uncomment when migrated from plugin/src/Managers/
// require_once __DIR__ . '/managers/PdfBuilderPreviewGenerator.php';
// require_once __DIR__ . '/managers/PDF_Builder_Screenshot_Renderer.php';
// require_once __DIR__ . '/managers/PDF_Builder_Thumbnail_Manager.php';

// === LAYER 3: PREVIEW SYSTEM (STUB) ===
// Minimal UI-only preview system
require_once __DIR__ . '/php/PreviewSystem.php';
require_once __DIR__ . '/php/PreviewImageAPI.php';
require_once __DIR__ . '/php/PreviewAjaxHandler.php';

// === LAYER 4: HOOKS & API ===
// React hooks and TypeScript API (loaded via webpack if needed)
// Note: React hooks loaded via webpack compilation
// - usePreview hook for React component state management
// - PreviewImageAPI TypeScript class for API integration

// Enqueue JavaScript files
add_action('admin_enqueue_scripts', function() {
    wp_enqueue_script(
        'pdf-preview-api-client',
        plugin_dir_url(__FILE__) . 'js/pdf-preview-api-client.js',
        ['jquery'],
        '1.0.0',
        true
    );
    
    wp_enqueue_script(
        'pdf-preview-integration',
        PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-preview-integration.min.js',
        ['jquery', 'pdf-preview-api-client'],
        '1.0.0',
        true
    );
});

// Export main classes for backward compatibility
class Api {
    public static function load() {
        // Preview system is minimal - no additional boot needed
    }
}

