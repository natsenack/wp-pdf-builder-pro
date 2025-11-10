<?php
/**
 * Script pour corriger automatiquement les noms de méthodes PSR12
 * Convertit les underscores en camelCase
 */

function convertMethodNames($content) {
    // Pattern pour les déclarations de méthodes avec underscores
    $pattern = '/function\s+([a-zA-Z_][a-zA-Z0-9_]*_+[a-zA-Z_][a-zA-Z0-9_]*)\s*\(/';

    return preg_replace_callback($pattern, function($matches) {
        $methodName = $matches[1];

        // Convertir snake_case en camelCase
        $parts = explode('_', $methodName);
        $camelCase = $parts[0];
        for ($i = 1; $i < count($parts); $i++) {
            $camelCase .= ucfirst($parts[$i]);
        }

        return 'function ' . $camelCase . '(';
    }, $content);
}

function convertMethodCalls($content) {
    // Pattern pour les appels de méthodes avec underscores
    $pattern = '/->\s*([a-zA-Z_][a-zA-Z0-9_]*_+[a-zA-Z_][a-zA-Z0-9_]*)\s*\(/';

    return preg_replace_callback($pattern, function($matches) {
        $methodName = $matches[1];

        // Convertir snake_case en camelCase
        $parts = explode('_', $methodName);
        $camelCase = $parts[0];
        for ($i = 1; $i < count($parts); $i++) {
            $camelCase .= ucfirst($parts[$i]);
        }

        return '->' . $camelCase . '(';
    }, $content);
}

// Trouver tous les fichiers PHP sauf vendor
$files = glob('plugin/**/*.php');
$files = array_filter($files, function($file) {
    return strpos($file, 'vendor') === false;
});

echo 'Traitement de ' . count($files) . ' fichiers PHP...' . PHP_EOL;

$processed = 0;
foreach ($files as $file) {
    $content = file_get_contents($file);

    // Sauvegarder l'original
    // file_put_contents($file . '.backup', $content);

    // Convertir les déclarations de méthodes
    $newContent = convertMethodNames($content);

    // Convertir les appels de méthodes
    $newContent = convertMethodCalls($newContent);

    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo "Modifié: $file" . PHP_EOL;
        $processed++;
    }
}

echo "Terminé! $processed fichiers modifiés." . PHP_EOL;
?>