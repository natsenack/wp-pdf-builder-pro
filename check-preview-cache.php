<?php
// Script de diagnostic pour les previews PDF
echo "=== DIAGNOSTIC PREVIEW PDF ===\n";

// Vérifier les dossiers
$content_dir = WP_CONTENT_DIR;
$cache_dir = $content_dir . '/cache/wp-pdf-builder-previews';
$cache_url = content_url() . '/cache/wp-pdf-builder-previews';

echo "Dossier de stockage: $cache_dir\n";
echo "URL d'accès: $cache_url\n";

if (!file_exists($cache_dir)) {
    echo "❌ Dossier cache n'existe pas\n";
    if (wp_mkdir_p($cache_dir)) {
        echo "✅ Dossier créé avec succès\n";
        chmod($cache_dir, 0755);
        echo "✅ Permissions définies (755)\n";
    } else {
        echo "❌ Impossible de créer le dossier\n";
    }
} else {
    echo "✅ Dossier cache existe\n";
    echo "Permissions: " . substr(sprintf('%o', fileperms($cache_dir)), -4) . "\n";

    $files = glob($cache_dir . '/*.png');
    echo "Images en cache: " . count($files) . "\n";

    if (count($files) > 0) {
        echo "Dernière image: " . basename($files[0]) . "\n";
    }
}

// Tester l'accès à l'URL
echo "\nTest d'accès à l'URL...\n";
$test_file = $cache_dir . '/test-access-' . time() . '.txt';
if (file_put_contents($test_file, 'test')) {
    $test_url = $cache_url . '/' . basename($test_file);
    echo "Fichier test créé: $test_file\n";
    echo "URL test: $test_url\n";

    // Tester si l'URL est accessible
    $headers = get_headers($test_url, 1);
    if ($headers && strpos($headers[0], '200') !== false) {
        echo "✅ URL accessible\n";
    } else {
        echo "❌ URL non accessible (code: " . ($headers ? $headers[0] : 'unknown') . ")\n";
    }

    unlink($test_file);
    echo "Fichier test supprimé\n";
} else {
    echo "❌ Impossible de créer le fichier test\n";
}

echo "\n=== FIN DIAGNOSTIC ===\n";
?>