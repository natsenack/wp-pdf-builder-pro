<?php
/**
 * PDF Builder Pro - Simple Preview Generator
 * DEPRECATED - Use PreviewSystem instead
 * 
 * Kept for backward compatibility only
 */

namespace PDF_Builder\Api;

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

// Load the new system
require_once dirname(__FILE__) . '/PreviewSystem.php';
