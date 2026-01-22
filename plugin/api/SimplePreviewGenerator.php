<?php
/**
 * PDF Builder Pro - Simple Preview Generator
 * DEPRECATED - Use PreviewSystem instead
 */

namespace PDF_Builder\Api;

if (!defined('ABSPATH')) {
    exit;
}

// Charger le nouveau système
require_once dirname(__FILE__) . '/PreviewSystem.php';
