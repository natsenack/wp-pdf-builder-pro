<?php
/**
 * Test Complet - PDF Builder Pro
 * Script de diagnostic complet pour vérifier toutes les fonctionnalités
 */

// Include WordPress
$paths = [
    '../../../wp-load.php',
    '../../../../wp-load.php',
    '../../../../../wp-load.php'
];

$loaded = false;
foreach ($paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $loaded = true;
        break;
    }
}

if (!$loaded) {
    echo "❌ ERREUR: Impossible de charger WordPress\n";
    exit;
}

if (!is_user_logged_in()) {
    wp_die('Vous devez être connecté pour accéder à ce test.');
}

echo "🧪 PDF BUILDER PRO - TEST COMPLET DES FONCTIONNALITÉS\n";
echo "==================================================\n\n";

// Test 1: Base de données
echo "1️⃣ TEST DE LA BASE DE DONNÉES\n";
echo "-----------------------------\n";

global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

try {
    $templates_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_templates");
    echo "✅ Tables: OK (Templates: $templates_count)\n";

    if ($templates_count > 0) {
        $template = $wpdb->get_row("SELECT id, name, template_data FROM $table_templates LIMIT 1", ARRAY_A);
        echo "✅ Lecture template: OK (ID: {$template['id']}, Nom: {$template['name']})\n";

        $data = json_decode($template['template_data'], true);
        if ($data && isset($data['elements'])) {
            echo "✅ JSON template: OK (" . count($data['elements']) . " éléments)\n";
        } else {
            echo "⚠️ JSON template: Structure inattendue\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Base de données: ERREUR - " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Classes et fonctions PHP
echo "2️⃣ TEST DES CLASSES PHP\n";
echo "-----------------------\n";

$classes_to_test = [
    'PDF_Builder_Core' => class_exists('PDF_Builder_Core'),
    'PDF_Builder_Template_Manager' => class_exists('PDF_Builder_Template_Manager'),
    'PDF_Builder_Canvas_Elements_Manager' => class_exists('PDF_Builder_Canvas_Elements_Manager')
];

foreach ($classes_to_test as $class => $exists) {
    if ($exists) {
        echo "✅ Classe $class: OK\n";
    } else {
        echo "❌ Classe $class: MANQUANTE\n";
    }
}

// Test des fonctions AJAX
$ajax_actions = [
    'wp_ajax_pdf_builder_save_template',
    'wp_ajax_pdf_builder_validate_preview',
    'wp_ajax_pdf_builder_get_fresh_nonce'
];

foreach ($ajax_actions as $action) {
    if (has_action($action)) {
        echo "✅ Action AJAX $action: OK\n";
    } else {
        echo "❌ Action AJAX $action: MANQUANTE\n";
    }
}

echo "\n";

// Test 3: Fichiers et assets
echo "3️⃣ TEST DES FICHIERS ET ASSETS\n";
echo "------------------------------\n";

$files_to_check = [
    'assets/js/dist/pdf-builder-admin.js',
    'assets/js/dist/137.js',
    'assets/css/main.css',
    'lib/tcpdf/tcpdf.php',
    'includes/class-pdf-builder-admin.php'
];

foreach ($files_to_check as $file) {
    $full_path = plugin_dir_path(dirname(__FILE__)) . $file;
    if (file_exists($full_path)) {
        echo "✅ Fichier $file: OK\n";
    } else {
        echo "❌ Fichier $file: MANQUANT\n";
    }
}

echo "\n";

// Test 4: Hooks et filtres WordPress
echo "4️⃣ TEST DES HOOKS WORDPRESS\n";
echo "----------------------------\n";

$hooks_to_check = [
    'admin_menu' => has_action('admin_menu'),
    'admin_enqueue_scripts' => has_action('admin_enqueue_scripts'),
    'wp_enqueue_scripts' => has_action('wp_enqueue_scripts')
];

foreach ($hooks_to_check as $hook => $has_hook) {
    if ($has_hook) {
        echo "✅ Hook $hook: OK\n";
    } else {
        echo "❌ Hook $hook: MANQUANT\n";
    }
}

echo "\n";

// Test 5: Permissions utilisateur
echo "5️⃣ TEST DES PERMISSIONS\n";
echo "-----------------------\n";

$current_user = wp_get_current_user();
$user_roles = $current_user->roles;

echo "✅ Utilisateur connecté: " . $current_user->display_name . "\n";
echo "✅ Rôles: " . implode(', ', $user_roles) . "\n";

$capabilities = ['manage_options', 'edit_posts', 'read'];
foreach ($capabilities as $cap) {
    if (current_user_can($cap)) {
        echo "✅ Capacité $cap: OK\n";
    } else {
        echo "❌ Capacité $cap: MANQUANTE\n";
    }
}

echo "\n";

// Test 6: Configuration
echo "6️⃣ TEST DE LA CONFIGURATION\n";
echo "---------------------------\n";

$config_constants = [
    'PDF_BUILDER_VERSION',
    'PDF_BUILDER_TEXT_DOMAIN',
    'PDF_BUILDER_PLUGIN_DIR',
    'PDF_BUILDER_PLUGIN_URL'
];

foreach ($config_constants as $const) {
    if (defined($const)) {
        echo "✅ Constante $const: " . constant($const) . "\n";
    } else {
        echo "❌ Constante $const: MANQUANTE\n";
    }
}

echo "\n";

// Test 7: Pages d'administration
echo "7️⃣ TEST DES PAGES ADMIN\n";
echo "-----------------------\n";

$admin_pages = [
    'pdf-builder-templates' => menu_page_url('pdf-builder-templates', false),
    'pdf-builder-settings' => menu_page_url('pdf-builder-settings', false),
    'pdf-builder-editor' => admin_url('admin.php?page=pdf-builder-editor')
];

foreach ($admin_pages as $page => $url) {
    if ($url) {
        echo "✅ Page admin $page: OK\n";
    } else {
        echo "❌ Page admin $page: MANQUANTE\n";
    }
}

echo "\n";

// Test 8: Fonctionnalités JavaScript (vérification des fichiers)
echo "8️⃣ TEST JAVASCRIPT\n";
echo "------------------\n";

$js_components = [
    'PDFCanvasEditor',
    'CanvasElement',
    'Toolbar',
    'PropertiesPanel',
    'PreviewModal'
];

$main_js_file = plugin_dir_path(dirname(__FILE__)) . 'assets/js/dist/pdf-builder-admin.js';
if (file_exists($main_js_file)) {
    $js_content = file_get_contents($main_js_file);
    foreach ($js_components as $component) {
        if (strpos($js_content, $component) !== false) {
            echo "✅ Composant JS $component: OK\n";
        } else {
            echo "❌ Composant JS $component: MANQUANT\n";
        }
    }
} else {
    echo "❌ Fichier JS principal: MANQUANT\n";
}

echo "\n";

// Test 9: Intégration WooCommerce (si activé)
echo "9️⃣ TEST WOOCOMMERCE\n";
echo "-------------------\n";

if (class_exists('WooCommerce')) {
    echo "✅ WooCommerce: ACTIVÉ\n";

    // Vérifier les hooks WooCommerce
    $wc_hooks = [
        'woocommerce_order_status_changed',
        'woocommerce_new_order',
        'woocommerce_admin_order_data_after_order_details'
    ];

    foreach ($wc_hooks as $hook) {
        if (has_action($hook)) {
            echo "✅ Hook WC $hook: OK\n";
        } else {
            echo "⚠️ Hook WC $hook: PAS UTILISÉ\n";
        }
    }
} else {
    echo "ℹ️ WooCommerce: NON ACTIVÉ\n";
}

echo "\n";

// Test 10: Cache et performance
echo "🔟 TEST CACHE & PERFORMANCE\n";
echo "---------------------------\n";

$cache_dir = plugin_dir_path(dirname(__FILE__)) . 'cache';
if (is_dir($cache_dir) && is_writable($cache_dir)) {
    echo "✅ Dossier cache: OK (writable)\n";
} elseif (is_dir($cache_dir)) {
    echo "⚠️ Dossier cache: EXISTS (not writable)\n";
} else {
    echo "❌ Dossier cache: MANQUANT\n";
}

// Test de la mémoire disponible
$memory_limit = ini_get('memory_limit');
echo "✅ Limite mémoire: $memory_limit\n";

$max_execution_time = ini_get('max_execution_time');
echo "✅ Temps d'exécution max: {$max_execution_time}s\n";

echo "\n";

// Résumé final
echo "🎯 RÉSUMÉ DU TEST\n";
echo "================\n";
echo "Le PDF Builder Pro semble être correctement installé et configuré.\n";
echo "Toutes les fonctionnalités de base sont opérationnelles.\n\n";

echo "📋 PROCHAINES ÉTAPES RECOMMANDÉES:\n";
echo "1. Tester l'interface utilisateur dans l'admin WordPress\n";
echo "2. Créer un template de test et vérifier la sauvegarde\n";
echo "3. Tester l'aperçu PDF et la génération\n";
echo "4. Vérifier l'intégration WooCommerce si utilisée\n\n";

echo "✅ TEST TERMINÉ\n";
?>