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
        // Debug: Afficher que la validation a commencé
        echo '<div class="notice notice-info"><p>🔄 Validation démarrée... Veuillez patienter (30 secondes environ).</p></div>';
        echo '<div id="validation-progress" style="background: #f1f1f1; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3>⏳ Progression de la validation</h3>
            <div id="progress-bar" style="background: #ddd; height: 20px; border-radius: 10px; overflow: hidden;">
                <div id="progress-fill" style="background: #3498db; height: 100%; width: 0%; transition: width 0.3s;"></div>
            </div>
            <p id="progress-text">Initialisation...</p>
        </div>';

        // Forcer l'affichage immédiat
        if (ob_get_level()) {
            ob_flush();
        }
        flush();

        // Inclure et exécuter le validateur
        echo '<script>document.getElementById("progress-text").innerHTML = "Chargement du validateur...";</script>';
        if (ob_get_level()) {
            ob_flush();
        }
        flush();

        require_once plugin_dir_path(__FILE__) . 'server-validator.php';

        // Forcer l'exécution
        if (!defined('RUN_PDF_BUILDER_VALIDATION')) {
            define('RUN_PDF_BUILDER_VALIDATION', true);
        }

        echo '<script>document.getElementById("progress-text").innerHTML = "Initialisation de la classe validateur...";</script>';
        if (ob_get_level()) {
            ob_flush();
        }
        flush();

        $validator = new PDF_Builder_Server_Validator();

        echo '<script>document.getElementById("progress-text").innerHTML = "Lancement des tests...";</script>';
        echo '<script>document.getElementById("progress-fill").style.width = "25%";</script>';
        if (ob_get_level()) {
            ob_flush();
        }
        flush();

        $validator->run_all_tests();

        echo '<script>document.getElementById("progress-text").innerHTML = "Tests terminés, génération du rapport...";</script>';
        echo '<script>document.getElementById("progress-fill").style.width = "100%";</script>';
        if (ob_get_level()) {
            ob_flush();
        }
        flush();

        // Récupérer les erreurs et warnings pour les console.log
        $errors = $validator->get_errors();
        $warnings = $validator->get_warnings();

        // Ajouter les console.log JavaScript pour les erreurs
        if (!empty($errors) || !empty($warnings)) {
            echo '<script>';
            if (!empty($errors)) {
                echo 'console.error("🚨 ERREURS DE VALIDATION PDF BUILDER PRO:", ' . json_encode($errors) . ');';
            }
            if (!empty($warnings)) {
                echo 'console.warn("⚠️ AVERTISSEMENTS DE VALIDATION PDF BUILDER PRO:", ' . json_encode($warnings) . ');';
            }
            echo '</script>';
        }

        echo '<script>document.getElementById("progress-text").innerHTML = "Validation terminée !";</script>';
        if (ob_get_level()) {
            ob_flush();
        }
        flush();

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

        <form method="post" action="" id="validation-form">
            <?php wp_nonce_field('pdf_builder_validation'); ?>
            <p>
                <input type="submit" name="run_validation" class="button button-primary button-hero"
                       value="🚀 Lancer la Validation Complète"
                       onclick="startValidation(this);">
            </p>
        </form>

        <script>
        function startValidation(button) {
            button.value = '🔄 Validation en cours...';
            button.disabled = true;

            // Empêcher la soumission multiple
            document.getElementById('validation-form').onsubmit = function(e) {
                e.preventDefault();
                return false;
            };

            // Timeout de sécurité (60 secondes)
            setTimeout(function() {
                if (button.disabled) {
                    alert('⚠️ La validation prend plus de temps que prévu. Vérifiez la console pour les erreurs.');
                }
            }, 60000);

            // Soumettre le formulaire
            document.getElementById('validation-form').submit();
        }
        </script>

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