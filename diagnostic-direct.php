<?php
/**
 * Diagnostic direct pour PDF Builder Pro
 * Accessible via URL directe : /wp-content/plugins/wp-pdf-builder-pro/diagnostic-direct.php
 */

// Configuration pour afficher toutes les erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Empêcher l'accès direct si ABSPATH n'est pas défini
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
}

// Fonction de logging sécurisée
function diagnostic_log($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] [$level] $message\n";
    echo $log_message;

    // Essayer de logger dans le fichier d'erreurs PHP si possible
    if (function_exists('error_log')) {
        error_log($log_message);
    }
}

diagnostic_log("=== DIAGNOSTIC DIRECT PDF BUILDER PRO ===");
diagnostic_log("PHP Version: " . phpversion());
diagnostic_log("ABSPATH: " . ABSPATH);

// Test 1: Vérifier si WordPress est chargé
diagnostic_log("Test 1: Chargement WordPress");
if (function_exists('get_bloginfo')) {
    diagnostic_log("WordPress chargé - Version: " . get_bloginfo('version'));
} else {
    diagnostic_log("WordPress NON chargé - Fonctions de base indisponibles", "WARNING");
}

// Test 2: Vérifier les fichiers critiques
diagnostic_log("Test 2: Fichiers critiques");
$critical_files = [
    'pdf-builder-pro.php',
    'bootstrap.php',
    'core/autoloader.php',
    'src/Core/PDF_Builder_Update_Manager.php',
    'src/Core/PDF_Builder_Metrics_Analytics.php',
    'src/utilities/PDF_Builder_Notification_Manager.php'
];

$current_dir = __DIR__ . '/';
foreach ($critical_files as $file) {
    $full_path = $current_dir . $file;
    if (file_exists($full_path)) {
        $size = filesize($full_path);
        diagnostic_log("✓ $file existe ($size bytes)");
    } else {
        diagnostic_log("✗ $file MANQUANT", "ERROR");
    }
}

// Test 3: Tester le chargement du plugin principal
diagnostic_log("Test 3: Chargement du plugin principal");
try {
    $main_file = $current_dir . 'pdf-builder-pro.php';
    if (file_exists($main_file)) {
        diagnostic_log("Tentative de chargement de pdf-builder-pro.php...");

        // Inclure le fichier principal
        ob_start();
        include_once $main_file;
        $output = ob_get_clean();

        if (!empty($output)) {
            diagnostic_log("Sortie du plugin: $output", "WARNING");
        }

        diagnostic_log("✓ pdf-builder-pro.php chargé sans erreur fatale");
    } else {
        diagnostic_log("✗ pdf-builder-pro.php introuvable", "ERROR");
    }
} catch (Exception $e) {
    diagnostic_log("✗ ERREUR lors du chargement: " . $e->getMessage(), "ERROR");
    diagnostic_log("Fichier: " . $e->getFile() . " Ligne: " . $e->getLine(), "ERROR");
} catch (Error $e) {
    diagnostic_log("✗ ERREUR FATALE lors du chargement: " . $e->getMessage(), "ERROR");
    diagnostic_log("Fichier: " . $e->getFile() . " Ligne: " . $e->getLine(), "ERROR");
}

// Test 4: Vérifier les classes
diagnostic_log("Test 4: Classes disponibles");
$classes_to_check = [
    'PDF_Builder_Update_Manager',
    'PDF_Builder_Metrics_Analytics',
    'PDF_Builder_UI_Notification_Manager',
    'PDF_Builder_Intelligent_Loader',
    'PDF_Builder_Config_Manager'
];

foreach ($classes_to_check as $class) {
    if (class_exists($class)) {
        diagnostic_log("✓ Classe $class disponible");
    } else {
        diagnostic_log("✗ Classe $class MANQUANTE", "WARNING");
    }
}

// Test 5: Vérifier les fonctions
diagnostic_log("Test 5: Fonctions disponibles");
$functions_to_check = [
    'pdf_builder_get_db_update_status',
    'pdf_builder_get_metrics_analytics',
    'pdf_builder_translate',
    'pdf_builder_reporting'
];

foreach ($functions_to_check as $function) {
    if (function_exists($function)) {
        diagnostic_log("✓ Fonction $function disponible");
    } else {
        diagnostic_log("✗ Fonction $function MANQUANTE", "WARNING");
    }
}

// Test 6: Vérifier les constantes
diagnostic_log("Test 6: Constantes du plugin");
$constants_to_check = [
    'PDF_BUILDER_PLUGIN_DIR',
    'PDF_BUILDER_VERSION'
];

foreach ($constants_to_check as $constant) {
    if (defined($constant)) {
        $value = constant($constant);
        diagnostic_log("✓ Constante $constant = $value");
    } else {
        diagnostic_log("✗ Constante $constant NON définie", "WARNING");
    }
}

// Test 7: Vérifier les anciens doublons
diagnostic_log("Test 7: Vérification des doublons");
$doublons_to_check = [
    'pdf_builder_get_update_status' => 'pdf_builder_get_db_update_status',
    'pdf_builder_get_analytics' => 'pdf_builder_get_metrics_analytics',
    'PDF_Builder_Notification_Manager' => 'PDF_Builder_UI_Notification_Manager'
];

foreach ($doublons_to_check as $old => $new) {
    if (function_exists($old)) {
        diagnostic_log("⚠ ANCIENNE FONCTION $old existe encore (remplacer par $new)", "WARNING");
    }
    if (class_exists($old)) {
        diagnostic_log("⚠ ANCIENNE CLASSE $old existe encore (remplacer par $new)", "WARNING");
    }
}

// Test 8: Informations système
diagnostic_log("Test 8: Informations système");
diagnostic_log("Mémoire utilisée: " . memory_get_peak_usage(true) . " bytes");
diagnostic_log("Temps d'exécution: " . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) . " secondes");

// Test 9: Vérifier les erreurs PHP récentes
diagnostic_log("Test 9: Dernières erreurs PHP");
$log_file = ini_get('error_log');
if ($log_file && file_exists($log_file) && is_readable($log_file)) {
    $log_content = file_get_contents($log_file);
    $lines = explode("\n", $log_content);
    $recent_lines = array_slice($lines, -10); // Dernières 10 lignes

    diagnostic_log("Dernières entrées du log d'erreurs:");
    foreach ($recent_lines as $line) {
        if (!empty(trim($line))) {
            diagnostic_log("  " . trim($line));
        }
    }
} else {
    diagnostic_log("Fichier de log non accessible ou non configuré");
}

diagnostic_log("=== FIN DU DIAGNOSTIC DIRECT ===");

// Afficher un résumé HTML simple
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnostic Direct - PDF Builder Pro</title>
    <meta charset="utf-8">
    <style>
        body { font-family: monospace; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .success { color: #28a745; }
        .info { color: #17a2b8; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnostic Direct - PDF Builder Pro</h1>
        <p><strong>URL d'accès direct:</strong> <code><?php echo $_SERVER['REQUEST_URI']; ?></code></p>
        <p><strong>Date:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

        <div style="margin: 20px 0;">
            <h3>📋 Résumé du diagnostic</h3>
            <p>Le diagnostic complet a été exécuté. Vérifiez les logs ci-dessus pour identifier les problèmes.</p>

            <h4>Actions recommandées:</h4>
            <ul>
                <li>Si vous voyez des erreurs "MANQUANT", vérifiez que les fichiers ont été déployés correctement</li>
                <li>Si vous voyez des erreurs "ERREUR FATALE", corrigez les problèmes de code PHP</li>
                <li>Si des "ANCIENNES" fonctions/classes existent, elles doivent être supprimées</li>
                <li>Vérifiez les logs d'erreurs PHP pour plus de détails</li>
            </ul>
        </div>

        <div style="margin: 20px 0;">
            <h3>🔗 Liens utiles</h3>
            <ul>
                <li><a href="../wp-admin/tools.php?page=pdf-builder-diagnostic">Interface de diagnostic WordPress</a> (si accessible)</li>
                <li><a href="../wp-admin/plugins.php">Gestionnaire de plugins WordPress</a></li>
                <li><a href="../wp-admin/">Administration WordPress</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
<?php
diagnostic_log("Page HTML affichée avec succès");
?>