<?php
/**
 * Script de génération des vignettes de prévisualisation pour les templates prédéfinis
 * À exécuter dans un environnement WordPress (avec WP bootstrap)
 */

// Inclure le bootstrap WordPress si nécessaire
if (!defined('ABSPATH')) {
    // Essayer différents chemins possibles pour wp-load.php
    $possible_paths = [
        dirname(__DIR__, 3) . '/wp-load.php', // ../../wp-load.php depuis plugin/dev/
        dirname(__DIR__, 2) . '/wp-load.php', // ../wp-load.php depuis plugin/dev/
        dirname(__DIR__, 4) . '/wp-load.php', // ../../../wp-load.php depuis plugin/dev/
        '/wp-load.php', // Racine absolue
    ];

    $wp_loaded = false;
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $wp_loaded = true;
            break;
        }
    }

    if (!$wp_loaded) {
        echo "Erreur: Impossible de trouver wp-load.php. Chemins essayés:\n";
        foreach ($possible_paths as $path) {
            echo "  - $path\n";
        }
        echo "\nVeuillez exécuter ce script depuis une installation WordPress valide.\n";
        exit(1);
    }
}

require_once dirname(__DIR__) . '/bootstrap.php';

echo "=== Génération des vignettes de prévisualisation pour templates prédéfinis ===\n\n";

try {
    // Charger PreviewImageAPI
    $preview_api = new \WP_PDF_Builder_Pro\Api\PreviewImageAPI();

    // Dossier des templates prédéfinis
    $templates_dir = plugin_dir_path(__FILE__) . '../templates/predefined/';
    $templates = glob($templates_dir . '*.json');

    if (empty($templates)) {
        throw new Exception("Aucun template prédéfini trouvé dans $templates_dir");
    }

    echo "Templates trouvés: " . count($templates) . "\n\n";

    foreach ($templates as $template_file) {
        $filename = basename($template_file, '.json');
        echo "Traitement de $filename...\n";

        // Charger le JSON du template
        $template_json = file_get_contents($template_file);
        if (!$template_json) {
            echo "  ❌ Erreur: impossible de lire $template_file\n";
            continue;
        }

        $template_data = json_decode($template_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "  ❌ Erreur JSON: " . json_last_error_msg() . "\n";
            continue;
        }

        // Vérifier si c'est un template valide
        if (!isset($template_data['canvasWidth']) || !isset($template_data['canvasHeight']) || !isset($template_data['elements'])) {
            echo "  ❌ Template invalide: champs requis manquants\n";
            continue;
        }

        // Paramètres pour génération vignette (qualité basse pour rapidité)
        $params = [
            'template_data' => $template_data,
            'context' => 'editor',
            'quality' => 75, // Qualité réduite pour vignette
            'format' => 'png',
            'order_id' => null
        ];

        // Générer la vignette
        try {
            $result = $preview_api->generate_with_cache($params);

            if ($result && isset($result['image_url'])) {
                // Mettre à jour le champ previewImage dans le JSON
                $template_data['previewImage'] = $result['image_url'];

                // Sauvegarder le JSON mis à jour
                $updated_json = json_encode($template_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                if (file_put_contents($template_file, $updated_json)) {
                    echo "  ✅ Vignette générée: " . $result['image_url'] . "\n";
                } else {
                    echo "  ❌ Erreur: impossible de sauvegarder $template_file\n";
                }
            } else {
                echo "  ❌ Erreur: génération échouée\n";
            }

        } catch (Exception $e) {
            echo "  ❌ Exception: " . $e->getMessage() . "\n";
        }

        echo "\n";
    }

    echo "=== Génération terminée ===\n";

} catch (Exception $e) {
    echo "Erreur globale: " . $e->getMessage() . "\n";
    exit(1);
}
?>

echo "=== Génération des vignettes de prévisualisation pour templates prédéfinis ===\n\n";

try {
    // Charger PreviewImageAPI
    $preview_api = new \WP_PDF_Builder_Pro\Api\PreviewImageAPI();

    // Dossier des templates prédéfinis
    $templates_dir = plugin_dir_path(__FILE__) . '../templates/predefined/';
    $templates = glob($templates_dir . '*.json');

    if (empty($templates)) {
        throw new Exception("Aucun template prédéfini trouvé dans $templates_dir");
    }

    echo "Templates trouvés: " . count($templates) . "\n\n";

    foreach ($templates as $template_file) {
        $filename = basename($template_file, '.json');
        echo "Traitement de $filename...\n";

        // Charger le JSON du template
        $template_json = file_get_contents($template_file);
        if (!$template_json) {
            echo "  ❌ Erreur: impossible de lire $template_file\n";
            continue;
        }

        $template_data = json_decode($template_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "  ❌ Erreur JSON: " . json_last_error_msg() . "\n";
            continue;
        }

        // Vérifier si c'est un template valide
        if (!isset($template_data['canvasWidth']) || !isset($template_data['canvasHeight']) || !isset($template_data['elements'])) {
            echo "  ❌ Template invalide: champs requis manquants\n";
            continue;
        }

        // Paramètres pour génération vignette (qualité basse pour rapidité)
        $params = [
            'template_data' => $template_data,
            'context' => 'editor',
            'quality' => 75, // Qualité réduite pour vignette
            'format' => 'png',
            'order_id' => null
        ];

        // Générer la vignette
        try {
            $result = $preview_api->generate_with_cache($params);

            if ($result && isset($result['image_url'])) {
                // Mettre à jour le champ previewImage dans le JSON
                $template_data['previewImage'] = $result['image_url'];

                // Sauvegarder le JSON mis à jour
                $updated_json = json_encode($template_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                if (file_put_contents($template_file, $updated_json)) {
                    echo "  ✅ Vignette générée: " . $result['image_url'] . "\n";
                } else {
                    echo "  ❌ Erreur: impossible de sauvegarder $template_file\n";
                }
            } else {
                echo "  ❌ Erreur: génération échouée\n";
            }

        } catch (Exception $e) {
            echo "  ❌ Exception: " . $e->getMessage() . "\n";
        }

        echo "\n";
    }

    echo "=== Génération terminée ===\n";

} catch (Exception $e) {
    echo "Erreur globale: " . $e->getMessage() . "\n";
    exit(1);
}
?>