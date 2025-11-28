<?php
/**
 * DIAGNOSTIC COMPLET - Capture toutes les erreurs PHP et WordPress
 */

// Activer TOUS les modes de débogage possibles
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL | E_STRICT | E_DEPRECATED | E_NOTICE | E_WARNING);

// Définir les constantes WordPress de debug
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);
define('WP_DISABLE_FATAL_ERROR_HANDLER', true);

// Fonction pour capturer toutes les erreurs
function capture_all_errors($errno, $errstr, $errfile, $errline) {
    $error_types = [
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_STRICT => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED => 'E_DEPRECATED',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        E_ALL => 'E_ALL'
    ];

    $type = isset($error_types[$errno]) ? $error_types[$errno] : 'UNKNOWN';

    echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 5px; border-radius: 3px;'>";
    echo "<strong style='color: #d32f2f;'>$type:</strong> $errstr<br>";
    echo "<small style='color: #666;'>Fichier: $errfile (ligne $errline)</small>";
    echo "</div>";

    // Continuer l'exécution malgré les erreurs
    return true;
}

// Définir le gestionnaire d'erreurs personnalisé
set_error_handler('capture_all_errors');

// Capturer les exceptions non gérées
function capture_exceptions($exception) {
    echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 5px; border-radius: 3px;'>";
    echo "<strong style='color: #d32f2f;'>EXCEPTION NON GÉRÉE:</strong> " . $exception->getMessage() . "<br>";
    echo "<small style='color: #666;'>Fichier: " . $exception->getFile() . " (ligne " . $exception->getLine() . ")</small><br>";
    echo "<pre style='background: #f5f5f5; padding: 5px; margin-top: 5px;'>" . $exception->getTraceAsString() . "</pre>";
    echo "</div>";
}

set_exception_handler('capture_exceptions');

// Capturer les erreurs fatales
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 5px; border-radius: 3px;'>";
        echo "<strong style='color: #d32f2f;'>ERREUR FATALE:</strong> " . $error['message'] . "<br>";
        echo "<small style='color: #666;'>Fichier: " . $error['file'] . " (ligne " . $error['line'] . ")</small>";
        echo "</div>";
    }
});

echo "<!DOCTYPE html><html><head><title>DIAGNOSTIC COMPLET PDF BUILDER</title></head><body>";
echo "<h1 style='color: #1976d2;'>🔍 DIAGNOSTIC COMPLET - PDF Builder Pro</h1>";
echo "<p style='background: #e3f2fd; padding: 10px; border-radius: 3px;'>Ce script capture TOUTES les erreurs PHP et teste le chargement du plugin étape par étape.</p>";

// Étape 1: Informations système
echo "<h2>📊 Informations système</h2>";
echo "<ul>";
echo "<li><strong>PHP Version:</strong> " . phpversion() . "</li>";
echo "<li><strong>Système d'exploitation:</strong> " . php_uname() . "</li>";
echo "<li><strong>Memory limit:</strong> " . ini_get('memory_limit') . "</li>";
echo "<li><strong>Max execution time:</strong> " . ini_get('max_execution_time') . "</li>";
echo "<li><strong>Error reporting:</strong> " . error_reporting() . "</li>";
echo "<li><strong>Display errors:</strong> " . (ini_get('display_errors') ? 'ON' : 'OFF') . "</li>";
echo "</ul>";

// Étape 2: Test des constantes WordPress
echo "<h2>🔧 Test des constantes WordPress</h2>";
$constants = ['ABSPATH', 'WPINC', 'WP_CONTENT_DIR', 'WP_PLUGIN_DIR'];
foreach ($constants as $const) {
    if (defined($const)) {
        $value = constant($const);
        echo "<p>✅ <strong>$const:</strong> $value</p>";
    } else {
        echo "<p>❌ <strong>$const:</strong> NON DÉFINIE</p>";
    }
}

// Étape 3: Test du répertoire du plugin
echo "<h2>📁 Test du répertoire plugin</h2>";
$plugin_dir = '/var/www/nats/data/www/threeaxe.fr/wp-content/plugins/wp-pdf-builder-pro';
echo "<p><strong>Répertoire testé:</strong> $plugin_dir</p>";

if (is_dir($plugin_dir)) {
    echo "<p>✅ Répertoire existe</p>";

    $files = scandir($plugin_dir);
    echo "<p><strong>Fichiers dans le répertoire:</strong></p>";
    echo "<ul>";
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $path = $plugin_dir . '/' . $file;
            $type = is_dir($path) ? '📁' : '📄';
            echo "<li>$type $file</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p>❌ Répertoire n'existe pas</p>";
}

// Étape 4: Test du fichier principal
echo "<h2>📄 Test du fichier principal (pdf-builder-pro.php)</h2>";
$main_file = $plugin_dir . '/pdf-builder-pro.php';

if (file_exists($main_file)) {
    echo "<p>✅ Fichier pdf-builder-pro.php existe (" . filesize($main_file) . " octets)</p>";

    echo "<h3>Test de syntaxe PHP</h3>";
    $syntax_check = shell_exec("php -l \"$main_file\" 2>&1");
    if (strpos($syntax_check, 'No syntax errors') !== false) {
        echo "<p>✅ Syntaxe PHP correcte</p>";
    } else {
        echo "<p>❌ Erreur de syntaxe: $syntax_check</p>";
    }

    echo "<h3>Test d'inclusion du fichier principal</h3>";
    try {
        require_once $main_file;
        echo "<p>✅ Fichier principal inclus sans erreur fatale</p>";
    } catch (Exception $e) {
        echo "<p>❌ Exception lors de l'inclusion: " . $e->getMessage() . "</p>";
    } catch (Error $e) {
        echo "<p>❌ Erreur fatale lors de l'inclusion: " . $e->getMessage() . "</p>";
        echo "<p><strong>Ligne:</strong> " . $e->getLine() . "</p>";
        echo "<p><strong>Fichier:</strong> " . $e->getFile() . "</p>";
    }

} else {
    echo "<p>❌ Fichier pdf-builder-pro.php n'existe pas</p>";
}

// Étape 5: Test du bootstrap
echo "<h2>🚀 Test du bootstrap</h2>";
$bootstrap_file = $plugin_dir . '/bootstrap.php';

if (file_exists($bootstrap_file)) {
    echo "<p>✅ Fichier bootstrap.php existe (" . filesize($bootstrap_file) . " octets)</p>";

    echo "<h3>Test d'inclusion du bootstrap</h3>";
    try {
        require_once $bootstrap_file;
        echo "<p>✅ Bootstrap inclus sans erreur fatale</p>";
    } catch (Exception $e) {
        echo "<p>❌ Exception lors de l'inclusion du bootstrap: " . $e->getMessage() . "</p>";
    } catch (Error $e) {
        echo "<p>❌ Erreur fatale lors de l'inclusion du bootstrap: " . $e->getMessage() . "</p>";
        echo "<p><strong>Ligne:</strong> " . $e->getLine() . "</p>";
        echo "<p><strong>Fichier:</strong> " . $e->getFile() . "</p>";
    }

} else {
    echo "<p>❌ Fichier bootstrap.php n'existe pas</p>";
}

// Étape 6: Test du Security Validator
echo "<h2>🔒 Test du Security Validator</h2>";
$security_file = $plugin_dir . '/src/Core/PDF_Builder_Security_Validator.php';

if (file_exists($security_file)) {
    echo "<p>✅ Fichier Security Validator existe (" . filesize($security_file) . " octets)</p>";

    echo "<h3>Test d'inclusion du Security Validator</h3>";
    try {
        require_once $security_file;
        echo "<p>✅ Security Validator inclus sans erreur fatale</p>";

        if (class_exists('PDF_Builder_Security_Validator')) {
            echo "<p>✅ Classe PDF_Builder_Security_Validator trouvée</p>";

            // Test d'instanciation
            try {
                $instance = PDF_Builder_Security_Validator::get_instance();
                echo "<p>✅ Instance créée avec succès</p>";
            } catch (Exception $e) {
                echo "<p>❌ Erreur lors de l'instanciation: " . $e->getMessage() . "</p>";
            }

        } else {
            echo "<p>❌ Classe PDF_Builder_Security_Validator NON trouvée</p>";
        }

    } catch (Exception $e) {
        echo "<p>❌ Exception lors de l'inclusion: " . $e->getMessage() . "</p>";
    } catch (Error $e) {
        echo "<p>❌ Erreur fatale lors de l'inclusion: " . $e->getMessage() . "</p>";
        echo "<p><strong>Ligne:</strong> " . $e->getLine() . "</p>";
        echo "<p><strong>Fichier:</strong> " . $e->getFile() . "</p>";
    }

} else {
    echo "<p>❌ Fichier Security Validator n'existe pas</p>";
}

// Étape 7: Test des fonctions WordPress
echo "<h2>🔧 Test des fonctions WordPress critiques</h2>";
$wp_functions = [
    'add_action', 'add_filter', 'wp_die', 'wp_verify_nonce',
    'current_user_can', 'get_current_user_id', 'sanitize_text_field',
    'wp_kses', 'get_option', 'update_option'
];

echo "<ul>";
foreach ($wp_functions as $func) {
    if (function_exists($func)) {
        echo "<li>✅ <strong>$func</strong> - disponible</li>";
    } else {
        echo "<li>❌ <strong>$func</strong> - NON disponible</li>";
    }
}
echo "</ul>";

// Étape 8: Test de chargement WordPress complet
echo "<h2>🌐 Test de chargement WordPress</h2>";
if (defined('ABSPATH') && file_exists(ABSPATH . 'wp-load.php')) {
    echo "<p>✅ wp-load.php trouvé</p>";

    echo "<h3>Test d'inclusion de wp-load.php</h3>";
    try {
        require_once ABSPATH . 'wp-load.php';
        echo "<p>✅ WordPress chargé avec succès</p>";

        if (function_exists('wp_get_current_user')) {
            echo "<p>✅ Fonctions WordPress disponibles</p>";
        }

    } catch (Exception $e) {
        echo "<p>❌ Exception lors du chargement WordPress: " . $e->getMessage() . "</p>";
    } catch (Error $e) {
        echo "<p>❌ Erreur fatale lors du chargement WordPress: " . $e->getMessage() . "</p>";
    }

} else {
    echo "<p>❌ wp-load.php non trouvé</p>";
}

// Étape 9: Résumé
echo "<h2>📋 RÉSUMÉ DU DIAGNOSTIC</h2>";
echo "<div style='background: #e8f5e8; border: 1px solid #4caf50; padding: 15px; border-radius: 5px;'>";
echo "<h3 style='color: #2e7d32; margin-top: 0;'>Si vous voyez ce message, le diagnostic s'est terminé sans erreur fatale critique.</h3>";
echo "<p>Le problème de page blanche peut être :</p>";
echo "<ul>";
echo "<li>Une erreur dans un autre plugin ou thème</li>";
echo "<li>Un problème de configuration PHP</li>";
echo "<li>Un conflit avec un autre composant</li>";
echo "<li>Un problème de base de données</li>";
echo "</ul>";
echo "<p><strong>Action recommandée:</strong> Désactivez temporairement le plugin PDF Builder Pro pour voir si la page blanche disparaît.</p>";
echo "</div>";

// Étape 10: Logs d'erreur
echo "<h2>📝 Logs d'erreur PHP</h2>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    echo "<p><strong>Fichier de log:</strong> $error_log</p>";
    $log_content = file_get_contents($error_log);
    $lines = explode("\n", $log_content);
    $recent_lines = array_slice($lines, -10); // Dernières 10 lignes

    echo "<h3>Dernières erreurs dans le log:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 3px; max-height: 200px; overflow-y: auto;'>";
    foreach ($recent_lines as $line) {
        if (!empty(trim($line))) {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p>Aucun fichier de log d'erreur trouvé ou accessible.</p>";
}

echo "</body></html>";
?>