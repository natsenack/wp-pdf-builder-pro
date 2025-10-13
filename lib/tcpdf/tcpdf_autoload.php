<?php
/**
 * TCPDF Autoload Wrapper - Minimal version
 */

// Prevent TCPDF from loading autoconfig which may cause permission issues
define('K_TCPDF_EXTERNAL_CONFIG', true);

// Set safe DOCUMENT_ROOT to prevent permission issues
if (!isset($_SERVER['DOCUMENT_ROOT']) || empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../../..';
}

// Load only the core TCPDF class
require_once __DIR__ . '/tcpdf.php';