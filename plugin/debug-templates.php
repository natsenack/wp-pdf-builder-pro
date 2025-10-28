<?php
/**
 * Script de débogage pour vérifier les templates et l'API
 * À placer sur le serveur et exécuter via URL
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
    require_once ABSPATH . 'wp-load.php';
}

if (!is_user_logged_in()) {
    wp_die('Vous devez être connecté pour accéder à cette page.');
}

echo '<h1>Débogage API Templates</h1>';

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

echo '<hr>';
echo '<p><a href="' . admin_url('admin.php?page=pdf-builder-templates') . '">Retour à la liste des templates</a></p>';
?>