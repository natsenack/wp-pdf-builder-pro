<?php
/**
 * Script de test pour les canvas personnalisés par commande
 */

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclure WordPress
require_once('../../../../wp-load.php');

echo "<h1>🧪 Test des Canvas Personnalisés par Commande</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;} .info{color:blue;}</style>";

global $wpdb;

// 1. Vérifier que la table existe
$table_order_canvases = $wpdb->prefix . 'pdf_builder_order_canvases';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_order_canvases'") === $table_order_canvases;

echo "<h2>1. Vérification de la table</h2>";
if ($table_exists) {
    echo "<p class='success'>✅ Table $table_order_canvases existe</p>";

    // Vérifier la structure
    $columns = $wpdb->get_results("DESCRIBE $table_order_canvases");
    echo "<p><strong>Colonnes de la table :</strong></p>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>{$column->Field}: {$column->Type}</li>";
    }
    echo "</ul>";
} else {
    echo "<p class='error'>❌ Table $table_order_canvases n'existe pas</p>";
}

// 2. Tester la classe WooCommerce Integration
echo "<h2>2. Test de la classe WooCommerce Integration</h2>";

if (class_exists('PDF_Builder_WooCommerce_Integration')) {
    echo "<p class='success'>✅ Classe PDF_Builder_WooCommerce_Integration chargée</p>";

    // Créer une instance
    $main_instance = new stdClass(); // Mock instance
    $woo_integration = new PDF_Builder_WooCommerce_Integration($main_instance);

    // Tester la méthode load_order_canvas
    if (method_exists($woo_integration, 'load_order_canvas')) {
        echo "<p class='success'>✅ Méthode load_order_canvas existe</p>";

        // Tester avec un order_id qui n'existe pas
        $result = $woo_integration->load_order_canvas(999999);
        if ($result === false) {
            echo "<p class='success'>✅ load_order_canvas retourne false pour un canvas inexistant</p>";
        } else {
            echo "<p class='error'>❌ load_order_canvas ne retourne pas false pour un canvas inexistant</p>";
        }
    } else {
        echo "<p class='error'>❌ Méthode load_order_canvas n'existe pas</p>";
    }

    // Tester la méthode save_order_canvas
    if (method_exists($woo_integration, 'save_order_canvas')) {
        echo "<p class='success'>✅ Méthode save_order_canvas existe</p>";

        // Données de test
        $test_canvas_data = [
            'canvas' => ['width' => 595, 'height' => 842],
            'pages' => [
                [
                    'elements' => [
                        [
                            'type' => 'text',
                            'content' => 'Test Canvas Personnalisé',
                            'position' => ['x' => 50, 'y' => 50],
                            'size' => ['width' => 200, 'height' => 30],
                            'style' => ['fontSize' => 14, 'color' => '#000000']
                        ]
                    ]
                ]
            ]
        ];

        // Sauvegarder
        $save_result = $woo_integration->save_order_canvas(12345, $test_canvas_data, 1);
        if ($save_result === true) {
            echo "<p class='success'>✅ save_order_canvas a réussi</p>";

            // Vérifier que c'est sauvegardé
            $loaded = $woo_integration->load_order_canvas(12345);
            if ($loaded && isset($loaded['pages'][0]['elements'][0]['content'])) {
                echo "<p class='success'>✅ Canvas chargé avec succès depuis la base de données</p>";
                echo "<p><strong>Contenu chargé :</strong> " . esc_html($loaded['pages'][0]['elements'][0]['content']) . "</p>";
            } else {
                echo "<p class='error'>❌ Impossible de charger le canvas sauvegardé</p>";
            }

            // Nettoyer
            $wpdb->delete($table_order_canvases, ['order_id' => 12345]);
            echo "<p class='info'>🧹 Données de test nettoyées</p>";

        } else {
            echo "<p class='error'>❌ save_order_canvas a échoué: " . (is_wp_error($save_result) ? $save_result->get_error_message() : 'Erreur inconnue') . "</p>";
        }
    } else {
        echo "<p class='error'>❌ Méthode save_order_canvas n'existe pas</p>";
    }

} else {
    echo "<p class='error'>❌ Classe PDF_Builder_WooCommerce_Integration non trouvée</p>";
}

// 3. Vérifier les hooks AJAX
echo "<h2>3. Vérification des hooks AJAX</h2>";

$ajax_actions = [
    'pdf_builder_pro_preview_order_pdf',
    'pdf_builder_save_order_canvas'
];

foreach ($ajax_actions as $action) {
    if (has_action('wp_ajax_' . $action)) {
        echo "<p class='success'>✅ Hook wp_ajax_$action enregistré</p>";
    } else {
        echo "<p class='error'>❌ Hook wp_ajax_$action non enregistré</p>";
    }
}

echo "<h2>4. Instructions d'utilisation</h2>";
echo "<p>Pour utiliser les canvas personnalisés par commande :</p>";
echo "<ol>";
echo "<li>Les canvas sont automatiquement sauvegardés dans la table <code>$table_order_canvases</code></li>";
echo "<li>L'aperçu récupère d'abord le canvas personnalisé de la commande, sinon utilise le template</li>";
echo "<li>Utilisez <code>save_order_canvas(order_id, canvas_data, template_id)</code> pour sauvegarder</li>";
echo "<li>Utilisez <code>load_order_canvas(order_id)</code> pour charger</li>";
echo "</ol>";

?>