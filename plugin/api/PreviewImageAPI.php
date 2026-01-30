<?php

namespace PDF_Builder\Api;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Preview Image API
 * DEPRECATED - System moved to preview-system folder
 */

// Load the new location
require_once dirname(__DIR__) . '/preview-system/php/PreviewImageAPI.php';

// Alias for backward compatibility
class PreviewImageAPI extends \PDF_Builder\PreviewSystem\PreviewImageAPI
{
}

