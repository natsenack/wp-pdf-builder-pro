<?php
try {
    require_once 'plugin/pdf-builder-pro.php';
    echo 'Plugin loaded successfully without fatal errors' . PHP_EOL;
} catch (Throwable $e) {
    echo 'Fatal error: ' . $e->getMessage() . PHP_EOL;
    echo 'File: ' . $e->getFile() . ' Line: ' . $e->getLine() . PHP_EOL;
}
?>