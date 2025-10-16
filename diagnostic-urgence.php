<?php
/**
 * Diagnostic d'urgence amélioré - PDF Builder Pro
 * Diagnostique et corrige automatiquement les problèmes de chargement
 */

// Test 0: Sécurité et environnement
if (!defined('ABSPATH')) {
    // Si ABSPATH n'est pas défini, on est probablement appelé directement
    echo "<h1>🚨 DIAGNOSTIC D'URGENCE - PDF BUILDER PRO</h1>";
    echo "<p><strong>Problème détecté :</strong> Ce fichier est appelé directement au lieu de passer par WordPress.</p>";
    echo "<p><strong>Solution :</strong> Accédez à ce fichier via l'administration WordPress ou utilisez l'URL correcte.</p>";
    echo "<hr>";

    // Essayer de charger WordPress manuellement pour les tests
    $wp_load_path = dirname(__FILE__, 3) . '/wp-load.php';
    if (file_exists($wp_load_path)) {
        echo "<p>🔄 Tentative de chargement manuel de WordPress...</p>";
        require_once $wp_load_path;
        echo "<p>✅ WordPress chargé manuellement</p>";
    } else {
        echo "<p>❌ Impossible de localiser wp-load.php</p>";
        echo "<p>💡 Vérifiez que vous êtes dans le bon répertoire WordPress</p>";
        exit;
    }
}

/**
 * Diagnostic d'urgence - PDF Builder Pro
 * Ce fichier permet de diagnostiquer pourquoi du code PHP apparaît brut
 */

// Test 1: PHP et environnement
echo "<h2>🧪 Test PHP et Environnement</h2>";
echo "<p>✅ PHP fonctionne - Version: " . phpversion() . "</p>";
echo "<p>✅ Mémoire limite: " . ini_get('memory_limit') . "</p>";
echo "<p>✅ Temps d'exécution max: " . ini_get('max_execution_time') . " secondes</p>";
echo "<p>✅ Upload max: " . ini_get('upload_max_filesize') . "</p>";

// Test 2: WordPress chargé ?
echo "<h2>🔍 Test WordPress</h2>";
if (function_exists('wp_get_current_user')) {
    echo "<p>✅ WordPress est chargé</p>";
    $user = wp_get_current_user();
    echo "<p>Utilisateur actuel: " . ($user->ID ? $user->display_name : 'Non connecté') . "</p>";

    // Test version WordPress
    global $wp_version;
    echo "<p>Version WordPress: $wp_version</p>";
    if (version_compare($wp_version, '5.0', '<')) {
        echo "<p>⚠️ Version WordPress ancienne détectée. Recommandé: 5.0+</p>";
    } else {
        echo "<p>✅ Version WordPress compatible</p>";
    }
} else {
    echo "<p>❌ WordPress n'est PAS chargé - C'est le problème !</p>";
    echo "<p>💡 Le plugin n'est pas inclus correctement par WordPress</p>";
    echo "<p>🔧 <strong>Solution :</strong> Vérifiez que le plugin est activé dans l'administration WordPress</p>";
}

// Test 3: Plugin activé ?
echo "<h2>🔌 Test Plugin</h2>";
if (function_exists('is_plugin_active')) {
    $plugin_file = 'pdf-builder-pro/pdf-builder-pro.php';
    if (is_plugin_active($plugin_file)) {
        echo "<p>✅ Plugin PDF Builder Pro est activé</p>";

        // Vérifier le statut détaillé
        $active_plugins = get_option('active_plugins', array());
        if (in_array($plugin_file, $active_plugins)) {
            echo "<p>✅ Plugin trouvé dans la liste des plugins actifs</p>";
        }
    } else {
        echo "<p>❌ Plugin PDF Builder Pro n'est PAS activé</p>";
        echo "<p>🔧 <strong>Action requise :</strong> Activez le plugin dans Extensions > Plugins installés</p>";

        // Essayer d'activer automatiquement
        if (current_user_can('activate_plugins')) {
            echo "<p>🔄 Tentative d'activation automatique...</p>";
            $result = activate_plugin($plugin_file);
            if (is_wp_error($result)) {
                echo "<p>❌ Échec activation automatique: " . $result->get_error_message() . "</p>";
            } else {
                echo "<p>✅ Plugin activé automatiquement ! Rafraîchissez la page.</p>";
            }
        }
    }
} else {
    echo "<p>⚠️ Fonction is_plugin_active non disponible</p>";
}

// Test 4: Fichiers présents et permissions
echo "<h2>📁 Test Fichiers et Permissions</h2>";
$files_to_check = [
    'pdf-builder-pro.php' => 'Fichier principal',
    'includes/classes/class-pdf-builder-admin.php' => 'Classe admin',
    'includes/managers/PDF_Builder_Canvas_Elements_Manager.php' => 'Gestionnaire canvas',
    'bootstrap.php' => 'Bootstrap',
    'assets/js/dist/pdf-builder-admin.js' => 'JavaScript compilé',
    'assets/css/pdf-builder-admin.css' => 'CSS principal'
];

$missing_files = [];
$permission_issues = [];

foreach ($files_to_check as $file => $description) {
    $path = plugin_dir_path(__FILE__) . '../' . $file;
    if (file_exists($path)) {
        $size = filesize($path);
        echo "<p>✅ $description ($file) existe ($size octets)</p>";

        // Vérifier les permissions
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        if ($perms < '0644') {
            $permission_issues[] = "$file: $perms (recommandé: 0644+)";
        }
    } else {
        $missing_files[] = $file;
        echo "<p>❌ $description ($file) MANQUANT</p>";
    }
}

if (!empty($permission_issues)) {
    echo "<p>⚠️ <strong>Problèmes de permissions détectés:</strong></p>";
    echo "<ul>";
    foreach ($permission_issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
    echo "<p>🔧 <strong>Commande de correction:</strong> <code>chmod 644 " . implode(' ', array_keys($files_to_check)) . "</code></p>";
}

if (!empty($missing_files)) {
    echo "<p>❌ <strong>Fichiers manquants:</strong> " . implode(', ', $missing_files) . "</p>";
    echo "<p>🔧 <strong>Solution:</strong> Redéployez les fichiers manquants via FTP</p>";
}

// Test 5: Dépendances PHP
echo "<h2>📦 Test Dépendances PHP</h2>";
$required_extensions = ['json', 'mbstring', 'gd', 'zip'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p>✅ Extension $ext chargée</p>";
    } else {
        $missing_extensions[] = $ext;
        echo "<p>❌ Extension $ext MANQUANTE</p>";
    }
}

if (!empty($missing_extensions)) {
    echo "<p>⚠️ <strong>Extensions PHP manquantes:</strong> " . implode(', ', $missing_extensions) . "</p>";
    echo "<p>🔧 <strong>Solution:</strong> Contactez votre hébergeur pour activer ces extensions</p>";
}

// Test 6: Base de données
echo "<h2>🗄️ Test Base de Données</h2>";
if (function_exists('wp_get_db_version')) {
    echo "<p>✅ Connexion base de données: OK</p>";

    global $wpdb;
    $table_test = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder_templates'");
    if ($table_test) {
        echo "<p>✅ Table pdf_builder_templates existe</p>";

        // Compter les templates
        $template_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}pdf_builder_templates");
        echo "<p>📊 Nombre de templates: $template_count</p>";
    } else {
        echo "<p>⚠️ Table pdf_builder_templates n'existe pas</p>";
        echo "<p>🔧 <strong>Solution:</strong> Le plugin va créer la table automatiquement lors de l'activation</p>";
    }
} else {
    echo "<p>❌ Connexion base de données: ÉCHEC</p>";
}

// Test 7: Cache et transients
echo "<h2>⚡ Test Cache WordPress</h2>";
if (function_exists('wp_cache_flush')) {
    echo "<p>✅ Système de cache WordPress disponible</p>";

    // Vider le cache automatiquement
    echo "<p>🔄 Vidage du cache...</p>";
    wp_cache_flush();
    echo "<p>✅ Cache vidé</p>";
} else {
    echo "<p>⚠️ Système de cache non disponible</p>";
}

// Test 8: Actions correctives automatiques
echo "<h2>🔧 Actions Correctives Automatiques</h2>";

// 1. Vider les transients du plugin
if (function_exists('delete_transient')) {
    $transients_deleted = 0;
    $transient_keys = [
        'pdf_builder_version',
        'pdf_builder_db_version',
        'pdf_builder_assets_version'
    ];

    foreach ($transient_keys as $key) {
        if (delete_transient($key)) {
            $transients_deleted++;
        }
    }

    echo "<p>✅ $transients_deleted transients nettoyés</p>";
}

// 2. Régénérer les permaliens si nécessaire
if (function_exists('flush_rewrite_rules')) {
    echo "<p>🔄 Régénération des permaliens...</p>";
    flush_rewrite_rules();
    echo "<p>✅ Permaliens régénérés</p>";
}

// Test 9: Test final de fonctionnalité
echo "<h2>🎯 Test Fonctionnalité Finale</h2>";
try {
    // Tester l'inclusion du bootstrap
    $bootstrap_path = plugin_dir_path(__FILE__) . '../bootstrap.php';
    if (file_exists($bootstrap_path)) {
        echo "<p>🔍 Test d'inclusion du bootstrap...</p>";
        require_once $bootstrap_path;
        echo "<p>✅ Bootstrap inclus avec succès</p>";
    }

    // Tester la classe principale
    if (class_exists('PDF_Builder_Canvas_Elements_Manager')) {
        echo "<p>✅ Classe PDF_Builder_Canvas_Elements_Manager disponible</p>";

        // Tester une instance
        $manager = PDF_Builder_Canvas_Elements_Manager::getInstance();
        if ($manager) {
            echo "<p>✅ Gestionnaire d'éléments instancié avec succès</p>";
        }
    } else {
        echo "<p>❌ Classe PDF_Builder_Canvas_Elements_Manager non trouvée</p>";
    }

} catch (Exception $e) {
    echo "<p>❌ Erreur lors des tests: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>📋 RÉSUMÉ DU DIAGNOSTIC</h2>";

// Compter les erreurs
$error_count = count($missing_files) + count($missing_extensions);
$warning_count = count($permission_issues);

if ($error_count === 0 && $warning_count === 0) {
    echo "<p style='color: green; font-weight: bold;'>✅ Aucune erreur critique détectée ! Le plugin devrait fonctionner normalement.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ $error_count erreur(s) critique(s) et $warning_count avertissement(s) détecté(s).</p>";
    echo "<p>🔧 Consultez les sections ci-dessus pour les solutions détaillées.</p>";
}

echo "<p><strong>Prochaines étapes :</strong></p>";
echo "<ol>";
echo "<li>Testez l'accès à l'éditeur PDF Builder dans l'administration WordPress</li>";
echo "<li>Si des erreurs persistent, consultez les logs d'erreur PHP</li>";
echo "<li>Vérifiez la console JavaScript du navigateur (F12)</li>";
echo "</ol>";

echo "<hr><p><strong>Diagnostic terminé le " . date('d/m/Y à H:i:s') . "</strong></p>";
?>

// Test 2: WordPress chargé ?
echo "<h2>🔍 Test WordPress</h2>";
if (function_exists('wp_get_current_user')) {
    echo "<p>✅ WordPress est chargé</p>";
    $user = wp_get_current_user();
    echo "<p>Utilisateur actuel: " . ($user->ID ? $user->display_name : 'Non connecté') . "</p>";
} else {
    echo "<p>❌ WordPress n'est PAS chargé - C'est le problème !</p>";
    echo "<p>💡 Le plugin n'est pas inclus correctement par WordPress</p>";
}

// Test 3: Plugin activé ?
echo "<h2>🔌 Test Plugin</h2>";
if (function_exists('is_plugin_active')) {
    if (is_plugin_active('pdf-builder-pro/pdf-builder-pro.php')) {
        echo "<p>✅ Plugin PDF Builder Pro est activé</p>";
    } else {
        echo "<p>❌ Plugin PDF Builder Pro n'est PAS activé</p>";
    }
} else {
    echo "<p>⚠️ Fonction is_plugin_active non disponible</p>";
}

// Test 4: Fichiers présents ?
echo "<h2>📁 Test Fichiers</h2>";
$files_to_check = [
    'pdf-builder-pro.php',
    'includes/classes/class-pdf-builder-admin.php',
    'includes/classes/managers/class-pdf-builder-pdf-generator.php'
];

foreach ($files_to_check as $file) {
    $path = plugin_dir_path(__FILE__) . '../' . $file;
    if (file_exists($path)) {
        $size = filesize($path);
        echo "<p>✅ $file existe ($size octets)</p>";
    } else {
        echo "<p>❌ $file MANQUANT</p>";
    }
}

// Test 5: Erreurs PHP
echo "<h2>🚨 Test Erreurs</h2>";
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test d'inclusion du PDF generator
echo "<h2>📄 Test Inclusion PDF Generator</h2>";
try {
    $generator_path = plugin_dir_path(__FILE__) . '../includes/classes/managers/class-pdf-builder-pdf-generator.php';
    if (file_exists($generator_path)) {
        echo "<p>🔍 Tentative d'inclusion du PDF generator...</p>";
        require_once $generator_path;
        if (class_exists('PDF_Builder_PDF_Generator')) {
            echo "<p>✅ Classe PDF_Builder_PDF_Generator chargée avec succès</p>";
        } else {
            echo "<p>❌ Classe PDF_Builder_PDF_Generator non trouvée après inclusion</p>";
        }
    } else {
        echo "<p>❌ Fichier PDF generator introuvable</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erreur lors de l'inclusion: " . $e->getMessage() . "</p>";
}

echo "<hr><p><strong>Si vous voyez du code PHP brut au lieu de ces tests, c'est que PHP ne fonctionne pas sur le serveur.</strong></p>";
?>