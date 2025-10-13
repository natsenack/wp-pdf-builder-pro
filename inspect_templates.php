<?php
/**
 * Script pour inspecter les données des templates en base de données
 */

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclure WordPress
require_once('../../../wp-load.php');

echo "<h1>🔍 Inspection des Templates PDF Builder Pro</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;} .info{color:blue;}</style>";

global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

echo "<h2>Templates en base de données</h2>";

// Récupérer tous les templates
$templates = $wpdb->get_results("SELECT id, name, template_data FROM $table_templates ORDER BY id DESC", ARRAY_A);

if (empty($templates)) {
    echo "<p class='error'>Aucun template trouvé en base de données.</p>";
} else {
    echo "<p><strong>" . count($templates) . " templates trouvés :</strong></p>";

    foreach ($templates as $template) {
        echo "<h3>Template ID {$template['id']}: {$template['name']}</h3>";

        $template_data = $template['template_data'];
        echo "<p><strong>Longueur des données :</strong> " . strlen($template_data) . " caractères</p>";

        // Essayer de décoder le JSON
        $decoded = json_decode($template_data, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<p class='success'>✅ JSON valide</p>";

            // Afficher la structure
            echo "<h4>Structure des données :</h4>";
            echo "<pre>" . print_r(array_keys($decoded), true) . "</pre>";

            // Vérifier les pages
            if (isset($decoded['pages'])) {
                echo "<p><strong>Pages :</strong> " . count($decoded['pages']) . "</p>";
                if (!empty($decoded['pages'])) {
                    echo "<p><strong>Éléments dans la première page :</strong> " . (isset($decoded['pages'][0]['elements']) ? count($decoded['pages'][0]['elements']) : 'AUCUN') . "</p>";

                    if (isset($decoded['pages'][0]['elements']) && !empty($decoded['pages'][0]['elements'])) {
                        echo "<h4>Premiers éléments :</h4>";
                        echo "<pre>" . print_r(array_slice($decoded['pages'][0]['elements'], 0, 3), true) . "</pre>";
                    }
                }
            }

            // Vérifier les éléments à la racine
            if (isset($decoded['elements'])) {
                echo "<p><strong>Éléments à la racine :</strong> " . count($decoded['elements']) . "</p>";
                if (!empty($decoded['elements'])) {
                    echo "<h4>Premiers éléments à la racine :</h4>";
                    echo "<pre>" . print_r(array_slice($decoded['elements'], 0, 3), true) . "</pre>";
                }
            }

        } else {
            echo "<p class='error'>❌ JSON invalide: " . json_last_error_msg() . "</p>";
            echo "<p><strong>Aperçu des données brutes :</strong></p>";
            echo "<pre>" . htmlspecialchars(substr($template_data, 0, 500)) . "...</pre>";
        }

        echo "<hr>";
    }
}

echo "<h2>Test de chargement d'un template spécifique</h2>";
if (!empty($templates)) {
    $first_template = $templates[0];
    echo "<p>Test du template ID {$first_template['id']} ({$first_template['name']})</p>";

    // Simuler le chargement comme le fait la classe admin
    $admin = new PDF_Builder_Admin(null);
    $loaded_data = $admin->load_template_robust($first_template['id']);

    echo "<h3>Résultat du chargement :</h3>";
    if ($loaded_data) {
        echo "<p class='success'>✅ Template chargé avec succès</p>";
        echo "<pre>" . print_r(array_keys($loaded_data), true) . "</pre>";

        if (isset($loaded_data['pages'])) {
            echo "<p><strong>Pages dans les données chargées :</strong> " . count($loaded_data['pages']) . "</p>";
            if (!empty($loaded_data['pages']) && isset($loaded_data['pages'][0]['elements'])) {
                echo "<p><strong>Éléments dans la première page :</strong> " . count($loaded_data['pages'][0]['elements']) . "</p>";
            }
        }
    } else {
        echo "<p class='error'>❌ Échec du chargement du template</p>";
    }
}

?>