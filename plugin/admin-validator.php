<?php
/**
 * PDF Builder Pro - Page Admin de Validation
 * Interface WordPress pour lancer la validation serveur
 */

// Sécurité WordPress
if (!defined('ABSPATH')) {
    exit;
}

// Ajouter une page d'admin pour la validation
add_action('admin_menu', 'pdf_builder_add_validator_page');

function pdf_builder_add_validator_page() {
    add_submenu_page(
        'tools.php',
        'PDF Builder Validator',
        'PDF Builder Validator',
        'manage_options',
        'pdf-builder-validator',
        'pdf_builder_validator_page'
    );
}

function pdf_builder_validator_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas les permissions suffisantes.'));
    }

    echo '<div class="wrap">';
    echo '<h1>🧪 PDF Builder Pro - Validation Serveur</h1>';

    if (isset($_POST['run_validation'])) {
        echo '<div class="notice notice-info"><p>🔄 Validation en cours... Veuillez patienter.</p></div>';

        // Inclure et exécuter le validateur
        require_once plugin_dir_path(__FILE__) . 'server-validator.php';

        // Forcer l'exécution
        if (!defined('RUN_PDF_BUILDER_VALIDATION')) {
            define('RUN_PDF_BUILDER_VALIDATION', true);
        }

        $validator = new PDF_Builder_Server_Validator();
        $validator->run_all_tests();

        exit;
    }

    ?>
    <div class="card">
        <h2>Validation Complète du Plugin</h2>
        <p>Cette validation teste toutes les fonctionnalités de PDF Builder Pro sur ce serveur.</p>

        <div class="validation-info">
            <h3>📋 Tests Effectués</h3>
            <ul style="list-style: disc; margin-left: 20px;">
                <li>Configuration WordPress et PHP</li>
                <li>Activation et chargement du plugin</li>
                <li>Classes et autoloader</li>
                <li>Base de données et tables</li>
                <li>Assets JavaScript/CSS</li>
                <li>APIs et génération PDF</li>
                <li>Intégration WooCommerce</li>
                <li>Performance et sécurité</li>
            </ul>
        </div>

        <div class="validation-warning" style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;">
            <h3>⚠️ Important</h3>
            <p><strong>Temps d'exécution :</strong> ~30 secondes</p>
            <p><strong>Ne pas fermer cette page</strong> pendant la validation</p>
            <p><strong>Score cible :</strong> 100/100 pour production</p>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field('pdf_builder_validation'); ?>
            <p>
                <input type="submit" name="run_validation" class="button button-primary button-hero"
                       value="🚀 Lancer la Validation Complète"
                       onclick="this.value='🔄 Validation en cours...'; this.disabled=true;">
            </p>
        </form>

        <div class="validation-links" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <h3>🔗 Liens Utiles</h3>
            <ul>
                <li><a href="<?php echo plugins_url('server-validator.php', __FILE__); ?>?force_direct=1" target="_blank">Accès direct au validateur</a></li>
                <li><a href="<?php echo plugins_url('SERVER-VALIDATION-GUIDE.md', __FILE__); ?>" target="_blank">Guide de validation complet</a></li>
                <li><a href="https://github.com/natsenack/wp-pdf-builder-pro" target="_blank">Repository GitHub</a></li>
            </ul>
        </div>
    </div>

    <style>
        .card { background: white; border: 1px solid #ddd; border-radius: 5px; padding: 20px; margin: 20px 0; }
        .validation-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .validation-warning { background: #fff3cd !important; border: 1px solid #ffeaa7 !important; }
        .button-hero { font-size: 16px !important; padding: 15px 30px !important; }
    </style>
    <?php

    echo '</div>';
}

// Ajouter un shortcode pour utiliser dans les pages
add_shortcode('pdf_builder_validator', 'pdf_builder_validator_shortcode');

function pdf_builder_validator_shortcode($atts) {
    if (!current_user_can('manage_options')) {
        return '<p>Permissions insuffisantes pour accéder à cet outil.</p>';
    }

    ob_start();
    ?>
    <div class="pdf-builder-validator-shortcode">
        <h3>🧪 Validation PDF Builder Pro</h3>
        <p><a href="<?php echo admin_url('tools.php?page=pdf-builder-validator'); ?>" class="button button-primary">Accéder à l'outil de validation</a></p>
        <p><small>Cet outil valide que PDF Builder Pro fonctionne correctement sur ce serveur.</small></p>
    </div>
    <?php
    return ob_get_clean();
}