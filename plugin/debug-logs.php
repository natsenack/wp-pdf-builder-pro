<?php
/**
 * Script pour capturer les logs d'erreur directement
 */

require_once '../../../wp-load.php';

echo "<h1>Capture des logs de validation</h1>";

// Démarrer la capture des logs
ob_start();

// Forcer l'appel à get_builtin_templates
require_once 'src/Managers/PDF_Builder_Template_Manager.php';

$template_manager = new PDF_Builder_Template_Manager(null);
$templates = $template_manager->get_builtin_templates();

$content = ob_get_clean();

echo "<h2>Résultat:</h2>";
echo "<p>Templates trouvés: " . count($templates) . "</p>";

// Essayer de récupérer les logs d'erreur
$log_file = ini_get('error_log');
if ($log_file && file_exists($log_file)) {
    echo "<h2>Logs d'erreur récents:</h2>";
    $logs = file($log_file);
    $recent_logs = array_slice($logs, -20); // Dernières 20 lignes

    echo "<pre>";
    foreach ($recent_logs as $log) {
        if (strpos($log, 'PDF Builder') !== false) {
            echo htmlspecialchars($log);
        }
    }
    echo "</pre>";
} else {
    echo "<p>Impossible d'accéder au fichier de logs: $log_file</p>";
}