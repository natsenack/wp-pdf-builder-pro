<?php

namespace PDF_Builder\Api;

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

/**
 * Preview Image API (Legacy)
 * DEPRECATED - Use PreviewSystem instead
 * 
 * Kept for backward compatibility only
 */

// Load the new system
require_once dirname(__FILE__) . '/PreviewSystem.php';

// Alias pour compatibilité
class PreviewImageAPI extends PreviewSystem
{
}