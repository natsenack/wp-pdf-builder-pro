<?php
/**
 * PDF Builder Pro - Simple Preview Generator
 * DEPRECATED - System moved to preview-system folder
 */

namespace PDF_Builder\Api;

if (!defined('ABSPATH')) {
    exit;
}

// Load the new location
require_once dirname(__DIR__) . '/preview-system/php/PreviewSystem.php';
