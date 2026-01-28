<?php
/**
 * PDF Builder Pro - Diagnostic du système de permissions
 * Test des fonctions de sécurité WordPress
 */

// Permettre l'exécution en ligne de commande pour les tests
if (defined('WP_DEBUG') && WP_DEBUG && php_sapi_name() === 'cli') {
    // Mode CLI pour les tests
    echo "=== DIAGNOSTIC DU SYSTÈME DE PERMISSIONS (MODE CLI) ===\n\n";
} elseif (!defined('ABSPATH')) {
    exit('Direct access not allowed');
} else {
    // Mode WordPress normal
    echo "=== DIAGNOSTIC DU SYSTÈME DE PERMISSIONS ===\n\n";
}

// Test 1: Fonctions WordPress disponibles
echo "1. Test des fonctions WordPress :\n";
$functions_to_test = [
    'wp_verify_nonce',
    'current_user_can',
    'wp_send_json_error',
    'wp_send_json_success',
    'wp_roles',
    'wp_create_nonce',
    'sanitize_text_field',
    'ARRAY_A'
];

foreach ($functions_to_test as $function) {
    if ($function === 'ARRAY_A') {
        if (defined('ARRAY_A')) {
            echo "   ✓ ARRAY_A défini\n";
        } else {
            echo "   ✗ ARRAY_A non défini\n";
        }
    } elseif (function_exists($function)) {
        echo "   ✓ $function disponible\n";
    } else {
        echo "   ✗ $function non disponible\n";
    }
}

echo "\n2. Test des namespaces :\n";
// Test des namespaces dans les fichiers critiques
$files_to_check = [
    'src/AJAX/Ajax_Handlers.php' => 'Namespace global',
    'src/AJAX/PDF_Builder_Templates_Ajax.php' => 'PDF_Builder\\AJAX',
    'src/Managers/PDF_Builder_PDF_Generator.php' => 'PDF_Builder\\Managers',
    'src/Managers/PDF_Builder_WooCommerce_Integration.php' => 'PDF_Builder\\Managers'
];

foreach ($files_to_check as $file => $expected_namespace) {
    $file_path = __DIR__ . '/' . $file;
    if (file_exists($file_path)) {
        $content = file_get_contents($file_path);
        if (strpos($content, 'namespace ' . str_replace('\\', '\\\\', $expected_namespace)) !== false ||
            ($expected_namespace === 'Namespace global' && strpos($content, 'namespace ') === false)) {
            echo "   ✓ $file : $expected_namespace\n";
        } else {
            echo "   ✗ $file : namespace incorrect\n";
        }
    } else {
        echo "   ✗ $file : fichier introuvable\n";
    }
}

echo "\n3. Test des permissions dans les handlers AJAX :\n";

// Simuler une requête AJAX pour tester les permissions
if (!defined('DOING_AJAX')) {
    define('DOING_AJAX', true);
}

// Test de la classe de base
if (class_exists('PDF_Builder_Ajax_Base')) {
    echo "   ✓ Classe PDF_Builder_Ajax_Base disponible\n";

    // Créer une instance de test
    $test_handler = new class() extends PDF_Builder_Ajax_Base {
        public function handle() {
            // Test method
        }

        public function test_permissions() {
            return $this->required_capability;
        }
    };

    $capability = $test_handler->test_permissions();
    echo "   ✓ Capacité requise par défaut : $capability\n";

} else {
    echo "   ✗ Classe PDF_Builder_Ajax_Base non disponible\n";
}

echo "\n4. Test des handlers spécifiques :\n";

$handlers_to_test = [
    'PDF_Builder_Settings_Ajax_Handler',
    'PDF_Builder_Templates_Ajax',
];

foreach ($handlers_to_test as $handler) {
    if (class_exists($handler)) {
        echo "   ✓ Classe $handler disponible\n";
    } else {
        echo "   ✗ Classe $handler non disponible\n";
    }
}

echo "\n5. Test des fonctions utilitaires :\n";

if (function_exists('pdf_builder_save_allowed_roles')) {
    echo "   ✓ Fonction pdf_builder_save_allowed_roles disponible\n";

    // Test avec des données d'exemple
    $test_roles = ['administrator', 'editor'];
    $result = pdf_builder_save_allowed_roles($test_roles);
    if (is_array($result) && count($result) === 2) {
        echo "   ✓ pdf_builder_save_allowed_roles fonctionne correctement\n";
    } else {
        echo "   ✗ pdf_builder_save_allowed_roles ne fonctionne pas\n";
    }
} else {
    echo "   ✗ Fonction pdf_builder_save_allowed_roles non disponible\n";
}

echo "\n=== DIAGNOSTIC TERMINÉ ===\n";

if (function_exists('wp_die')) {
    wp_die();
} else {
    exit;
}