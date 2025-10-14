<?php
/**
 * Script de debug AJAX pour tester l'aperçu PDF
 */

// Simuler une requête AJAX
echo "<h1>🧪 Debug AJAX - Aperçu PDF</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;} .info{color:blue;}</style>";

// Simuler les données POST
$_POST = [
    'action' => 'pdf_builder_pro_preview_order_pdf',
    'order_id' => 9275,
    'template_id' => 0,
    'nonce' => 'test'
];

// Inclure WordPress
require_once('../../../wp-load.php');

echo "<h2>1. Environnement WordPress</h2>";
echo "<p>WP_DEBUG: " . (WP_DEBUG ? 'true' : 'false') . "</p>";
echo "<p>ABSPATH: " . ABSPATH . "</p>";
echo "<p>admin_url('admin-ajax.php'): " . admin_url('admin-ajax.php') . "</p>";

echo "<h2>2. Test de l'action AJAX</h2>";

// Vérifier si l'action est hookée
if (has_action('wp_ajax_pdf_builder_pro_preview_order_pdf')) {
    echo "<p class='success'>✅ Hook wp_ajax_pdf_builder_pro_preview_order_pdf enregistré</p>";

    // Tester l'appel direct
    echo "<h3>Test de l'appel AJAX simulé</h3>";

    // Inclure la classe
    require_once('includes/classes/managers/class-pdf-builder-woocommerce-integration.php');

    if (class_exists('PDF_Builder_WooCommerce_Integration')) {
        echo "<p class='success'>✅ Classe PDF_Builder_WooCommerce_Integration chargée</p>";

        // Créer une instance
        $main_instance = new stdClass();
        $woo_integration = new PDF_Builder_WooCommerce_Integration($main_instance);

        if (method_exists($woo_integration, 'ajax_preview_order_pdf')) {
            echo "<p class='success'>✅ Méthode ajax_preview_order_pdf existe</p>";

            // Tester l'appel
            try {
                ob_start();
                $woo_integration->ajax_preview_order_pdf();
                $output = ob_get_clean();

                echo "<h4>Réponse de l'AJAX:</h4>";
                echo "<pre>" . htmlspecialchars($output) . "</pre>";

                // Vérifier si c'est du JSON
                $json = json_decode($output, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    echo "<p class='success'>✅ Réponse JSON valide</p>";
                    echo "<p>Success: " . ($json['success'] ? 'true' : 'false') . "</p>";
                    if (!$json['success']) {
                        echo "<p class='error'>Erreur: " . ($json['data'] ?? 'Inconnue') . "</p>";
                    }
                } else {
                    echo "<p class='error'>❌ Réponse n'est pas du JSON valide</p>";
                }

            } catch (Exception $e) {
                echo "<p class='error'>❌ Exception lors de l'appel: " . $e->getMessage() . "</p>";
            }

        } else {
            echo "<p class='error'>❌ Méthode ajax_preview_order_pdf n'existe pas</p>";
        }

    } else {
        echo "<p class='error'>❌ Classe PDF_Builder_WooCommerce_Integration non trouvée</p>";
    }

} else {
    echo "<p class='error'>❌ Hook wp_ajax_pdf_builder_pro_preview_order_pdf non enregistré</p>";

    // Lister tous les hooks AJAX
    echo "<h3>Hooks AJAX enregistrés:</h3>";
    global $wp_filter;
    $ajax_hooks = [];

    if (isset($wp_filter['wp_ajax_pdf_builder_pro_preview_order_pdf'])) {
        $ajax_hooks[] = 'wp_ajax_pdf_builder_pro_preview_order_pdf';
    }

    if (empty($ajax_hooks)) {
        echo "<p>Aucun hook AJAX trouvé pour pdf_builder_pro_preview_order_pdf</p>";
    } else {
        echo "<ul>";
        foreach ($ajax_hooks as $hook) {
            echo "<li>$hook</li>";
        }
        echo "</ul>";
    }
}

echo "<h2>3. Test de la base de données</h2>";

// Vérifier la connexion DB
global $wpdb;
if ($wpdb->check_connection()) {
    echo "<p class='success'>✅ Connexion base de données OK</p>";

    // Vérifier les tables
    $tables = [
        $wpdb->prefix . 'pdf_builder_templates',
        $wpdb->prefix . 'pdf_builder_order_canvases'
    ];

    foreach ($tables as $table) {
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
        echo "<p>" . ($exists ? '✅' : '❌') . " Table $table " . ($exists ? 'existe' : 'n\'existe pas') . "</p>";
    }

} else {
    echo "<p class='error'>❌ Connexion base de données échouée</p>";
}

?>