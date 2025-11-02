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

    // DEBUG BASIQUE - Premier point de contrôle
    echo '<div style="background: #ff0; padding: 10px; margin: 10px 0; border: 2px solid #000;">';
    echo '<strong>🚀 DEBUG: Fonction appelée à ' . date('H:i:s') . '</strong>';
    echo '<br>POST data: ' . (isset($_POST['run_validation']) ? 'run_validation=OUI' : 'run_validation=NON');
    echo '<br>REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD'];
    echo '</div>';

    echo '<div class="wrap">';
    echo '<h1>🧪 PDF Builder Pro - Validation Serveur</h1>';

    // DEBUG - Avant la vérification POST
    echo '<div style="background: #ffa; padding: 10px; margin: 10px 0; border: 1px solid #f00;">';
    echo '<strong>📋 DEBUG: Avant vérification POST</strong>';
    echo '<br>Time: ' . microtime(true);
    echo '</div>';

    if (isset($_POST['run_validation'])) {
        // LOG PHP - Validation déclenchée
        error_log('🚀 PDF BUILDER VALIDATOR: Validation déclenchée côté PHP');
        echo '<script>console.log("📥 Validation reçue côté serveur");</script>';

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

        error_log('📦 PDF BUILDER VALIDATOR: Chargement server-validator.php');
        require_once plugin_dir_path(__FILE__) . 'server-validator.php';

        echo '<script>document.getElementById("progress-text").innerHTML = "Initialisation du validateur...";</script>';
        if (ob_get_level()) {
            ob_flush();
        }
        flush();

        // Forcer l'exécution
        if (!defined('RUN_PDF_BUILDER_VALIDATION')) {
            define('RUN_PDF_BUILDER_VALIDATION', true);
        }

        error_log('🏗️ PDF BUILDER VALIDATOR: Création instance PDF_Builder_Server_Validator');
        echo '<script>document.getElementById("progress-text").innerHTML = "Initialisation de la classe validateur...";</script>';
        if (ob_get_level()) {
            ob_flush();
        }
        flush();

        $validator = new PDF_Builder_Server_Validator();

        error_log('▶️ PDF BUILDER VALIDATOR: Lancement run_all_tests()');
        echo '<script>document.getElementById("progress-text").innerHTML = "Lancement des tests...";</script>';
        echo '<script>document.getElementById("progress-fill").style.width = "25%";</script>';
        if (ob_get_level()) {
            ob_flush();
        }
        flush();

        // Capturer la sortie du validateur
        ob_start();
        $validator->run_all_tests();
        $validation_output = ob_get_clean();

        // Debug: Afficher ce qui a été capturé
        echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;">';
        echo '<strong>DEBUG - Sortie capturée:</strong><br>';
        echo '<pre>' . htmlspecialchars(substr($validation_output, 0, 500)) . '...</pre>';
        echo '</div>';

        echo '<script>document.getElementById("progress-text").innerHTML = "Tests terminés, génération du rapport...";</script>';
        echo '<script>document.getElementById("progress-fill").style.width = "100%";</script>';
        if (ob_get_level()) {
            ob_flush();
        }
        flush();

        // Récupérer les erreurs et warnings pour les console.log
        $errors = $validator->get_errors();
        $warnings = $validator->get_warnings();
        $results = $validator->get_results();

        // Debug: Afficher les données récupérées
        echo '<div style="background: #ffe0e0; padding: 10px; margin: 10px 0; border: 1px solid #f00;">';
        echo '<strong>DEBUG - Données récupérées:</strong><br>';
        echo 'Erreurs: ' . count($errors) . '<br>';
        echo 'Warnings: ' . count($warnings) . '<br>';
        echo 'Résultats success: ' . (isset($results['success']) ? count($results['success']) : 'N/A') . '<br>';
        echo 'Résultats total: ' . count($results) . '<br>';
        echo '<pre>' . htmlspecialchars(json_encode($results, JSON_PRETTY_PRINT)) . '</pre>';
        echo '</div>';

        // Afficher les résultats de validation dans la page WordPress
        echo '<div class="validation-results" style="margin-top: 30px; padding: 20px; background: white; border: 1px solid #ddd; border-radius: 5px;">';
        echo '<h2>📊 Résultats de la Validation</h2>';

        // Résumé
        $total_tests = count($results['success'] ?? []) + count($errors) + count($warnings);
        $success_count = count($results['success'] ?? []);
        $error_count = count($errors);
        $warning_count = count($warnings);
        $score = $total_tests > 0 ? round(($success_count / $total_tests) * 100, 1) : 0;

        echo '<div class="validation-summary" style="background: #ecf0f1; padding: 15px; border-radius: 5px; margin: 20px 0;">';
        echo '<h3>Résumé Exécution</h3>';
        echo '<p><strong>📊 Score Global:</strong> <span style="font-size: 24px; font-weight: bold; color: ' . ($error_count === 0 ? '#27ae60' : '#e74c3c') . ';">' . $score . '/100</span></p>';
        echo '<p><strong>✅ Succès:</strong> ' . $success_count . '</p>';
        echo '<p><strong>❌ Erreurs:</strong> ' . $error_count . '</p>';
        echo '<p><strong>⚠️ Avertissements:</strong> ' . $warning_count . '</p>';
        echo '<p><strong>📋 Tests Totaux:</strong> ' . $total_tests . '</p>';
        echo '</div>';

        // Erreurs
        if (!empty($errors)) {
            echo '<div class="validation-errors" style="background: #fee; border: 1px solid #e74c3c; padding: 15px; border-radius: 5px; margin: 20px 0;">';
            echo '<h3 style="color: #e74c3c;">❌ ERREURS CRITIQUES (' . count($errors) . ')</h3>';
            echo '<ul>';
            foreach ($errors as $error) {
                echo '<li>• ' . htmlspecialchars($error['message']) . ' <small>(' . date('H:i:s', $error['time']) . ')</small></li>';
            }
            echo '</ul>';
            echo '</div>';
        }

        // Avertissements
        if (!empty($warnings)) {
            echo '<div class="validation-warnings" style="background: #fff3cd; border: 1px solid #f39c12; padding: 15px; border-radius: 5px; margin: 20px 0;">';
            echo '<h3 style="color: #f39c12;">⚠️ AVERTISSEMENTS (' . count($warnings) . ')</h3>';
            echo '<ul>';
            foreach ($warnings as $warning) {
                echo '<li>• ' . htmlspecialchars($warning['message']) . ' <small>(' . date('H:i:s', $warning['time']) . ')</small></li>';
            }
            echo '</ul>';
            echo '</div>';
        }

        // Succès
        if (!empty($results['success'])) {
            echo '<div class="validation-success" style="background: #d4edda; border: 1px solid #27ae60; padding: 15px; border-radius: 5px; margin: 20px 0;">';
            echo '<h3 style="color: #27ae60;">✅ TESTS RÉUSSIS (' . count($results['success']) . ')</h3>';
            echo '<ul>';
            foreach ($results['success'] as $success) {
                echo '<li>• ' . htmlspecialchars($success['message']) . ' <small>(' . date('H:i:s', $success['time']) . ')</small></li>';
            }
            echo '</ul>';
            echo '</div>';
        }

        echo '</div>';

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
                       onclick="prepareValidation(this);">
            </p>
        </form>

        <script>
        console.log('🔧 PDF Builder Validator: Script chargé');

        function prepareValidation(button) {
            console.log('🚀 prepareValidation appelée', button);

            button.value = '🔄 Validation en cours...';
            button.disabled = true;
            console.log('✅ Bouton modifié:', button.value, button.disabled);

            // Timeout de sécurité (120 secondes)
            setTimeout(function() {
                console.log('⏰ Timeout de sécurité déclenché');
                if (button.disabled) {
                    console.warn('⚠️ Validation trop longue, alerte affichée');
                    alert('⚠️ La validation prend plus de temps que prévu (2 minutes). Vérifiez les logs du serveur pour les erreurs.');
                }
            }, 120000);

            console.log('📤 Soumission du formulaire...');
            // Laisser le formulaire se soumettre normalement
            return true;
        }

        // Vérifier que le formulaire existe
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('validation-form');
            console.log('📋 Formulaire trouvé:', form ? 'OUI' : 'NON', form);
        });
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