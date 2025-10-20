<?php
/**
 * Plugin WordPress pour créer des éléments de test PDF Builder
 * À utiliser temporairement pour déboguer l'aperçu
 */

if (!defined('ABSPATH')) {
    die('Accès direct non autorisé');
}

// Hook pour ajouter une page d'admin
add_action('admin_menu', 'pdf_builder_test_setup_menu');

function pdf_builder_test_setup_menu() {
    add_submenu_page(
        'tools.php',
        'PDF Builder - Créer Éléments Test',
        'PDF Builder Test',
        'manage_options',
        'pdf-builder-test',
        'pdf_builder_test_page'
    );
}

function pdf_builder_test_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Accès refusé');
    }

    $message = '';
    $template_id = 1;

    // Traiter la création d'éléments
    if (isset($_POST['create_test_elements']) && wp_verify_nonce($_POST['pdf_builder_test_nonce'], 'create_test_elements')) {
        $template = get_post($template_id);

        if (!$template) {
            // Créer un template de test
            $template_id = wp_insert_post([
                'post_title' => 'Template de Test PDF Builder',
                'post_type' => 'pdf_template',
                'post_status' => 'publish',
                'post_content' => 'Template de test pour l\'aperçu PDF'
            ]);

            if (is_wp_error($template_id)) {
                $message = '<div class="error"><p>Erreur lors de la création du template: ' . $template_id->get_error_message() . '</p></div>';
            } else {
                $message = '<div class="updated"><p>Template créé avec ID: ' . $template_id . '</p></div>';
            }
        }

        if (!$message) {
            // Éléments de test
            $test_elements = [
                [
                    'id' => 'company_info_1',
                    'type' => 'company_info',
                    'x' => 20,
                    'y' => 20,
                    'width' => 170,
                    'height' => 60,
                    'properties' => [
                        'show_name' => true,
                        'show_address' => true,
                        'show_phone' => true,
                        'show_email' => true,
                        'font_size' => 12,
                        'font_family' => 'Arial'
                    ]
                ],
                [
                    'id' => 'order_number_1',
                    'type' => 'order_number',
                    'x' => 400,
                    'y' => 20,
                    'width' => 150,
                    'height' => 20,
                    'properties' => [
                        'prefix' => 'Commande N°',
                        'font_size' => 14,
                        'font_family' => 'Arial',
                        'bold' => true
                    ]
                ],
                [
                    'id' => 'customer_info_1',
                    'type' => 'customer_info',
                    'x' => 20,
                    'y' => 100,
                    'width' => 170,
                    'height' => 80,
                    'properties' => [
                        'show_name' => true,
                        'show_address' => true,
                        'show_phone' => true,
                        'show_email' => true,
                        'font_size' => 11,
                        'font_family' => 'Arial'
                    ]
                ],
                [
                    'id' => 'product_table_1',
                    'type' => 'product_table',
                    'x' => 20,
                    'y' => 200,
                    'width' => 550,
                    'height' => 200,
                    'properties' => [
                        'show_sku' => true,
                        'show_quantity' => true,
                        'show_price' => true,
                        'show_total' => true,
                        'font_size' => 10,
                        'font_family' => 'Arial',
                        'border' => true
                    ]
                ],
                [
                    'id' => 'dynamic_text_1',
                    'type' => 'dynamic-text',
                    'x' => 20,
                    'y' => 420,
                    'width' => 550,
                    'height' => 40,
                    'properties' => [
                        'text' => 'Merci pour votre commande !',
                        'font_size' => 12,
                        'font_family' => 'Arial',
                        'alignment' => 'center'
                    ]
                ]
            ];

            $result = update_post_meta($template_id, 'pdf_builder_elements', $test_elements);

            if ($result) {
                $message = '<div class="updated"><p>✅ Éléments de test créés avec succès !</p><ul>';
                $message .= '<li>Template ID: ' . $template_id . '</li>';
                $message .= '<li>Nombre d\'éléments: ' . count($test_elements) . '</li>';
                $message .= '<li>Types: ' . implode(', ', array_unique(array_column($test_elements, 'type'))) . '</li>';
                $message .= '</ul></div>';
            } else {
                $message = '<div class="error"><p>❌ Erreur lors de la sauvegarde des éléments</p></div>';
            }
        }
    }

    // Vérifier les éléments existants
    $existing_elements = get_post_meta($template_id, 'pdf_builder_elements', true);
    $element_count = is_array($existing_elements) ? count($existing_elements) : 0;

    ?>
    <div class="wrap">
        <h1>PDF Builder - Créer Éléments de Test</h1>

        <?php echo $message; ?>

        <div class="card">
            <h2>État actuel du Template ID <?php echo $template_id; ?></h2>
            <p><strong>Éléments existants:</strong> <?php echo $element_count; ?></p>

            <?php if ($element_count > 0): ?>
                <details>
                    <summary>Afficher les éléments existants</summary>
                    <pre><?php echo json_encode($existing_elements, JSON_PRETTY_PRINT); ?></pre>
                </details>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Créer des Éléments de Test</h2>
            <p>Cette action va créer 5 éléments de test basiques pour permettre de tester l'aperçu PDF :</p>
            <ul>
                <li>Informations société</li>
                <li>Numéro de commande</li>
                <li>Informations client</li>
                <li>Tableau des produits</li>
                <li>Texte dynamique</li>
            </ul>

            <form method="post">
                <?php wp_nonce_field('create_test_elements', 'pdf_builder_test_nonce'); ?>
                <p>
                    <input type="submit" name="create_test_elements" class="button button-primary" value="Créer les Éléments de Test">
                </p>
            </form>
        </div>

        <div class="card">
            <h2>Test de l'Aperçu</h2>
            <p>Après avoir créé les éléments, testez l'aperçu PDF dans une commande WooCommerce.</p>
            <p><strong>Commande de test recommandée:</strong> ID 9275 (celle utilisée dans vos tests)</p>
        </div>
    </div>

    <style>
        .card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .card h2 {
            margin-top: 0;
            color: #23282d;
        }
        pre {
            background: #f1f1f1;
            padding: 10px;
            border-radius: 4px;
            overflow: auto;
            max-height: 400px;
        }
    </style>
    <?php
}

// Activer le plugin temporairement
register_activation_hook(__FILE__, function() {
    // Rien à faire à l'activation
});

register_deactivation_hook(__FILE__, function() {
    // Nettoyer si nécessaire
});
?>