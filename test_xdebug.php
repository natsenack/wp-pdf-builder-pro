<?php
// Test script pour Xdebug
echo "Testing Xdebug installation...\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Loaded extensions:\n";

$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    if (stripos($ext, 'xdebug') !== false) {
        echo "- $ext (Xdebug found!)\n";
    }
}

if (!extension_loaded('xdebug')) {
    echo "Xdebug is NOT loaded.\n";
    echo "Checking for common issues...\n";

    // Vérifier si la DLL existe
    $dll_path = 'C:\\php\\ext\\php_xdebug.dll';
    if (file_exists($dll_path)) {
        echo "DLL file exists at: $dll_path\n";
        echo "DLL size: " . filesize($dll_path) . " bytes\n";
    } else {
        echo "DLL file NOT found at: $dll_path\n";
    }

    // Vérifier les erreurs dans les logs PHP
    $error_log = ini_get('error_log');
    if ($error_log && file_exists($error_log)) {
        echo "PHP Error log: $error_log\n";
        $lines = file($error_log);
        $last_lines = array_slice($lines, -5);
        echo "Last 5 lines of error log:\n";
        foreach ($last_lines as $line) {
            echo "  $line";
        }
    }
} else {
    echo "Xdebug is loaded successfully!\n";
    echo "Xdebug version: " . phpversion('xdebug') . "\n";
}
?>