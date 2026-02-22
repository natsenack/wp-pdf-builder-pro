<?php

declare(strict_types=1);

/**
 * PHPStan Configuration Stub Loader
 * Force PHPStan/Intelephense to recognize our custom stubs
 */

// Load custom stubs to ensure IDE recognition
if (file_exists(__DIR__ . '/plugin/lib/pdf-builder-stubs.php')) {
    require_once __DIR__ . '/plugin/lib/pdf-builder-stubs.php';
}
