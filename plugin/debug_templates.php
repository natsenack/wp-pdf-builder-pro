<?php
/**
 * Script de débogage pour vérifier les templates et l'API
 * À placer sur le serveur et exécuter via URL
 */

// Charger WordPress de manière sécurisée
$wp_load_path = dirname(__FILE__) . '/../../../wp-load.php';
if (file_exists($wp_load_path)) {
    require_once $wp_load_path;
} else {
    die('Erreur: Impossible de charger WordPress. Vérifiez le chemin.');
}

// Headers pour éviter le cache
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!is_user_logged_in()) {
    wp_die('Vous devez être connecté pour accéder à cette page.');
}

echo '<h1>Débogage API Templates</h1>';
echo '<p>Dernière mise à jour: ' . date('Y-m-d H:i:s') . ' (v' . time() . ')</p>';

// Vérifier si la fonction existe
echo '<h2>1. Fonction AJAX</h2>';
if (function_exists('pdf_builder_ajax_get_template')) {
    echo '✅ Fonction pdf_builder_ajax_get_template existe<br>';
} else {
    echo '❌ Fonction pdf_builder_ajax_get_template n\'existe pas<br>';
}

// Vérifier les actions AJAX enregistrées
echo '<h2>2. Actions AJAX</h2>';
global $wp_filter;
$ajax_actions = isset($wp_filter['wp_ajax_pdf_builder_get_template']) ? $wp_filter['wp_ajax_pdf_builder_get_template'] : null;
if ($ajax_actions) {
    echo '✅ Action wp_ajax_pdf_builder_get_template enregistrée<br>';
} else {
    echo '❌ Action wp_ajax_pdf_builder_get_template non enregistrée<br>';
}

$ajax_nopriv_actions = isset($wp_filter['wp_ajax_nopriv_pdf_builder_get_template']) ? $wp_filter['wp_ajax_nopriv_pdf_builder_get_template'] : null;
if ($ajax_nopriv_actions) {
    echo '✅ Action wp_ajax_nopriv_pdf_builder_get_template enregistrée<br>';
} else {
    echo '❌ Action wp_ajax_nopriv_pdf_builder_get_template non enregistrée<br>';
}

// Vérifier la table des templates
echo '<h2>3. Table des templates</h2>';
global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

if ($wpdb->get_var("SHOW TABLES LIKE '$table_templates'") == $table_templates) {
    echo '✅ Table ' . $table_templates . ' existe<br>';

    // Compter les templates
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_templates");
    echo '📊 Nombre de templates: ' . $count . '<br>';

    if ($count > 0) {
        // Vérifier la structure de la table
        $columns = $wpdb->get_results("DESCRIBE $table_templates", ARRAY_A);
        echo '<h3>Structure de la table:</h3>';
        echo '<table border="1" style="border-collapse: collapse;">';
        echo '<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>';
        foreach ($columns as $column) {
            echo '<tr>';
            echo '<td>' . $column['Field'] . '</td>';
            echo '<td>' . $column['Type'] . '</td>';
            echo '<td>' . $column['Null'] . '</td>';
            echo '<td>' . $column['Key'] . '</td>';
            echo '<td>' . $column['Default'] . '</td>';
            echo '<td>' . $column['Extra'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';

        // Vérifier si template_data est LONGTEXT
        $template_data_column = null;
        foreach ($columns as $column) {
            if ($column['Field'] === 'template_data') {
                $template_data_column = $column;
                break;
            }
        }

        if ($template_data_column && strpos($template_data_column['Type'], 'longtext') === false) {
            echo '<p style="color: red;">⚠️ Le champ template_data est de type ' . $template_data_column['Type'] . '. Il devrait être LONGTEXT pour supporter des templates complexes.</p>';
            echo '<p><a href="?fix_table=1" style="background: #007cba; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;">Corriger la table (TEXT → LONGTEXT)</a></p>';
        } else {
            echo '<p style="color: green;">✅ Le champ template_data est correctement configuré en LONGTEXT.</p>';
        }

        // Lister les templates
        $templates = $wpdb->get_results("SELECT id, name, LENGTH(template_data) as data_length FROM $table_templates ORDER BY id", ARRAY_A);

        echo '<h3>Templates existants:</h3>';
        echo '<table border="1" style="border-collapse: collapse;">';
        echo '<tr><th>ID</th><th>Nom</th><th>Taille données (caractères)</th><th>Actions</th></tr>';

        foreach ($templates as $template) {
            echo '<tr>';
            echo '<td>' . $template['id'] . '</td>';
            echo '<td>' . esc_html($template['name']) . '</td>';
            echo '<td>' . $template['data_length'] . '</td>';
            echo '<td><a href="?test_template=' . $template['id'] . '">Tester API</a></td>';
            echo '</tr>';
        }
        echo '</table>';
    }
} else {
    echo '❌ Table ' . $table_templates . ' n\'existe pas<br>';
}

// Tester l'API si un template_id est spécifié
if (isset($_GET['test_template'])) {
    $template_id = intval($_GET['test_template']);

    echo '<h2>4. Test API pour template ID ' . $template_id . '</h2>';

    // Simuler l'appel AJAX
    $_GET['template_id'] = $template_id;
    $_GET['nonce'] = wp_create_nonce('pdf_builder_nonce');

    try {
        pdf_builder_ajax_get_template();
    } catch (Exception $e) {
        echo '❌ Erreur lors du test: ' . $e->getMessage() . '<br>';
    }
}

// Test rapide de l'API pour tous les templates
echo '<h2>5. Test rapide de l\'API pour tous les templates</h2>';
$templates = $wpdb->get_results("SELECT id, name FROM $table_templates ORDER BY id", ARRAY_A);

echo '<table border="1" style="border-collapse: collapse; margin-bottom: 20px;">';
echo '<tr><th>ID</th><th>Nom</th><th>Status API</th><th>Aperçu données</th></tr>';

foreach ($templates as $template) {
    echo '<tr>';
    echo '<td>' . $template['id'] . '</td>';
    echo '<td>' . esc_html($template['name']) . '</td>';

    // Tester l'API pour ce template
    $_GET['template_id'] = $template['id'];
    $_GET['nonce'] = wp_create_nonce('pdf_builder_nonce');

    ob_start();
    try {
        pdf_builder_ajax_get_template();
        $api_output = ob_get_clean();

        // Analyser la réponse
        $response = json_decode($api_output, true);
        if ($response && isset($response['success']) && $response['success']) {
            echo '<td style="color: green;">✅ OK</td>';
            // Aperçu des données
            if (isset($response['data']['elements'])) {
                $elements_count = is_array($response['data']['elements']) ? count($response['data']['elements']) : 'N/A';
                echo '<td>' . $elements_count . ' éléments</td>';
            } else {
                echo '<td>Pas d\'éléments</td>';
            }
        } else {
            $error_msg = isset($response['data']) ? $response['data'] : 'Erreur inconnue';
            echo '<td style="color: red;">❌ ' . esc_html($error_msg) . '</td>';
            echo '<td>-</td>';
        }
    } catch (Exception $e) {
        ob_end_clean();
        echo '<td style="color: red;">❌ Exception: ' . esc_html($e->getMessage()) . '</td>';
        echo '<td>-</td>';
    }

    echo '</tr>';
}
echo '</table>';

// Debug: Examiner les données brutes du template 1
if (isset($_GET['debug_raw'])) {
    $template_id = intval($_GET['debug_raw']);
    echo '<h2>Debug: Données brutes du template ' . $template_id . '</h2>';

    $raw_template = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
        ARRAY_A
    );

    if ($raw_template) {
        echo '<h3>Données template_data brutes:</h3>';
        echo '<pre style="background: #f5f5f5; padding: 10px; max-height: 400px; overflow: auto;">' . esc_html($raw_template['template_data']) . '</pre>';

        echo '<h3>Tentative de décodage JSON:</h3>';
        $decoded = json_decode($raw_template['template_data'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo '<p style="color: green;">✅ JSON valide</p>';
            echo '<h4>Structure décodée:</h4>';
            echo '<pre style="background: #f5f5f5; padding: 10px; max-height: 200px; overflow: auto;">' . esc_html(print_r($decoded, true)) . '</pre>';

            if (isset($decoded['elements'])) {
                echo '<h4>Champ elements:</h4>';
                if (is_string($decoded['elements'])) {
                    echo '<p>Type: String (longueur: ' . strlen($decoded['elements']) . ')</p>';
                    echo '<p>Premiers 500 caractères:</p>';
                    echo '<pre style="background: #f5f5f5; padding: 10px;">' . esc_html(substr($decoded['elements'], 0, 500)) . '...</pre>';

                    // Tester le décodage de elements
                    $elements_decoded = json_decode($decoded['elements'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        echo '<p style="color: green;">✅ Elements décodés avec succès: ' . count($elements_decoded) . ' éléments</p>';
                    } else {
                        echo '<p style="color: red;">❌ Erreur décodage elements: ' . json_last_error_msg() . '</p>';
                    }
                } else {
                    echo '<p>Type: ' . gettype($decoded['elements']) . '</p>';
                    echo '<pre style="background: #f5f5f5; padding: 10px;">' . esc_html(print_r($decoded['elements'], true)) . '</pre>';
                }
            } else {
                echo '<p style="color: red;">❌ Champ elements manquant</p>';
            }
        } else {
            echo '<p style="color: red;">❌ JSON invalide: ' . json_last_error_msg() . '</p>';
        }
    } else {
        echo '<p style="color: red;">Template non trouvé</p>';
    }
}

// Corriger la table si demandé
if (isset($_GET['fix_table'])) {
    echo '<h2>Correction de la table</h2>';

    $result = $wpdb->query("ALTER TABLE $table_templates MODIFY COLUMN template_data LONGTEXT");

    if ($result !== false) {
        echo '✅ Table corrigée avec succès. Le champ template_data est maintenant LONGTEXT.<br>';
        echo '<p><a href="' . remove_query_arg('fix_table') . '">Actualiser la page</a></p>';
    } else {
        echo '❌ Erreur lors de la correction de la table: ' . $wpdb->last_error . '<br>';
    }
}

echo '<hr>';
echo '<h2>Debug: Données brutes du template 1</h2>';

$raw_template = $wpdb->get_row(
    $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", 1),
    ARRAY_A
);

if ($raw_template) {
    echo '<h3>Données template_data brutes (premiers 1000 caractères):</h3>';
    echo '<pre style="background: #f5f5f5; padding: 10px; max-height: 300px; overflow: auto; font-size: 12px;">' . esc_html(substr($raw_template['template_data'], 0, 1000)) . '...</pre>';

    echo '<h3>Tentative de décodage JSON:</h3>';
    $decoded = json_decode($raw_template['template_data'], true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo '<p style="color: green;">✅ JSON valide</p>';

        if (isset($decoded['elements'])) {
            echo '<h4>Champ elements:</h4>';
            if (is_string($decoded['elements'])) {
                echo '<p>Type: String (longueur: ' . strlen($decoded['elements']) . ')</p>';
                echo '<p>Premiers 200 caractères:</p>';
                echo '<pre style="background: #f5f5f5; padding: 10px; font-size: 11px;">' . esc_html(substr($decoded['elements'], 0, 200)) . '...</pre>';

                // Tester le décodage de elements
                $elements_decoded = json_decode($decoded['elements'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    echo '<p style="color: green;">✅ Elements décodés avec succès: ' . count($elements_decoded) . ' éléments</p>';
                    echo '<h5>Premier élément:</h5>';
                    echo '<pre style="background: #f5f5f5; padding: 10px; font-size: 11px;">' . esc_html(print_r($elements_decoded[0], true)) . '</pre>';
                } else {
                    echo '<p style="color: red;">❌ Erreur décodage elements: ' . json_last_error_msg() . '</p>';
                    echo '<p>Caractère problématique autour de la position ' . json_last_error() . '</p>';
                }
            } else {
                echo '<p>Type: ' . gettype($decoded['elements']) . '</p>';
                echo '<pre style="background: #f5f5f5; padding: 10px;">' . esc_html(print_r($decoded['elements'], true)) . '</pre>';
            }
        } else {
            echo '<p style="color: red;">❌ Champ elements manquant</p>';
        }
    } else {
        echo '<p style="color: red;">❌ JSON invalide: ' . json_last_error_msg() . '</p>';
        echo '<p>Position approximative de l\'erreur: analyser les données brutes</p>';
    }
} else {
    echo '<p style="color: red;">Template 1 non trouvé</p>';
}

echo '<hr>';
echo '<p><a href="' . admin_url('admin.php?page=pdf-builder-templates') . '">Retour à la liste des templates</a></p>';
?>