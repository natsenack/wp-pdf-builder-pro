<?php
echo "Vérification des dossiers cache..." . PHP_EOL;
$upload_dir = wp_upload_dir();
$cache_dir = $upload_dir['basedir'] . '/cache/wp-pdf-builder-previews';
echo 'Dossier cache: ' . $cache_dir . PHP_EOL;
if (file_exists($cache_dir)) {
    echo "✅ Dossier existe" . PHP_EOL;
    echo 'Permissions: ' . substr(sprintf('%o', fileperms($cache_dir)), -4) . PHP_EOL;
    $files = glob($cache_dir . '/*.png');
    echo 'Images dans cache: ' . count($files) . PHP_EOL;
    if (count($files) > 0) {
        echo 'Dernière image: ' . basename(end($files)) . PHP_EOL;
    }
} else {
    echo "❌ Dossier n'existe pas" . PHP_EOL;
}
?>