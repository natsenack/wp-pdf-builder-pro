<?php
/**
 * Script de nettoyage - À placer temporairement sur le serveur
 * Accès: http://votre-site.fr/wp-content/plugins/cleanup.php
 * 
 * ⚠️ À SUPPRIMER APRÈS UTILISATION
 */

// Sécurité - Vérifier que c'est un appel local
if (!isset($_GET['cleanup_key']) || $_GET['cleanup_key'] !== 'clean-wp-pdf-builder-pro-2025') {
    die('❌ Accès refusé');
}

$plugin_dir = dirname(__FILE__) . '/wp-pdf-builder-pro';

if (!is_dir($plugin_dir)) {
    die('✅ Le dossier wp-pdf-builder-pro n\'existe pas ou a déjà été supprimé');
}

// Fonction récursive de suppression
function rrmdir($dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    rrmdir($path);
                } else {
                    @unlink($path);
                }
            }
        }
        @rmdir($dir);
    }
}

echo "<pre>";
echo "🧹 Suppression de: " . $plugin_dir . "\n";
echo "\n";

$start_time = microtime(true);
rrmdir($plugin_dir);
$time_taken = microtime(true) - $start_time;

if (!is_dir($plugin_dir)) {
    echo "✅ Suppression complète réussie !\n";
    echo "⏱️  Temps : " . round($time_taken, 2) . "s\n";
} else {
    echo "❌ Erreur lors de la suppression\n";
}

echo "\n";
echo "🚀 Vous pouvez maintenant redéployer le plugin\n";
echo "⚠️  N'oubliez pas de supprimer ce fichier (cleanup.php) !\n";
echo "</pre>";

// Optionnel: Supprimer ce script lui-même
// @unlink(__FILE__);
?>
