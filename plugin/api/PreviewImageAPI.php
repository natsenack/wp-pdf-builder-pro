<?php

namespace PDF_Builder\Api;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Preview Image API (Legacy)
 * DEPRECATED - Use PreviewSystem instead
 */

// Charger le nouveau système
require_once dirname(__FILE__) . '/PreviewSystem.php';

// Alias pour compatibilité
class PreviewImageAPI extends PreviewSystem
{
}