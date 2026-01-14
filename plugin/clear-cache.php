<?php
/**
 * PDF Builder Pro - Cache Clearing Utility
 * Script temporaire pour vider les caches serveur
 * À placer dans wp-content/clear-cache.php et accéder via browser
 */

// Sécurité basique
if (!isset($_GET['token']) || $_GET['token'] !== md5('pdf_builder_pro_cache')) {
    die('Accès refusé');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$results = array();

// 1. Vider OPcache
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        $results[] = '✅ OPcache vidé avec succès';
    } else {
        $results[] = '⚠️ OPcache non accessible (peut être désactivé)';
    }
} else {
    $results[] = '⚠️ OPcache non disponible';
}

// 2. Vider WordPress cache
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    $results[] = '✅ WordPress cache vidé';
}

// 3. Vider le cache des fichiers statiques
$uploads_dir = wp_upload_dir();
$cache_files = array(
    $uploads_dir['basedir'] . '/cache',
    WP_CONTENT_DIR . '/cache',
);

foreach ($cache_files as $cache_dir) {
    if (is_dir($cache_dir)) {
        $files = array_diff(scandir($cache_dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $cache_dir . '/' . $file;
            if (is_file($path)) {
                @unlink($path);
            }
        }
        $results[] = '✅ Cache dossier vidé: ' . $cache_dir;
    }
}

// 4. Vider Transients WordPress
$wpdb = $GLOBALS['wpdb'];
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_%'");
$results[] = '✅ Transients WordPress supprimés';

// Réponse HTML
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PDF Builder Pro - Cache Clearer</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; }
        .success { color: green; padding: 10px; margin: 5px 0; }
        .warning { color: orange; padding: 10px; margin: 5px 0; }
        .error { color: red; padding: 10px; margin: 5px 0; }
        .box { border: 1px solid #ccc; padding: 20px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>✅ Cache Nettoyé</h1>
        <p>Les caches serveur ont été vidés avec succès.</p>
        <h3>Résultats:</h3>
        <ul>
<?php foreach ($results as $result): ?>
            <li><span class="<?php echo strpos($result, '✅') !== false ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($result); ?></span></li>
<?php endforeach; ?>
        </ul>
        <p><strong>⚠️ Important:</strong></p>
        <ol>
            <li>Videz le cache de votre navigateur: <code>Ctrl+Shift+R</code> (Windows) ou <code>Cmd+Shift+R</code> (Mac)</li>
            <li>Rechargez la page admin de PDF Builder</li>
            <li>Vérifiez la console pour s'assurer que l'erreur "export" a disparu</li>
            <li><strong>Supprimez ce fichier après usage</strong></li>
        </ol>
    </div>
</body>
</html>
<?php
