<?php
/**
 * Test rapide du décodage des données du template 1
 */

// Charger WordPress
$wp_load_path = dirname(__FILE__) . '/../../../wp-load.php';
if (file_exists($wp_load_path)) {
    require_once $wp_load_path;
} else {
    die('Erreur: Impossible de charger WordPress.');
}

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: text/html; charset=utf-8');

echo '<h1>Test rapide - Décodage Template 1</h1>';

global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

$template = $wpdb->get_row(
    $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", 1),
    ARRAY_A
);

if (!$template) {
    echo '<p style="color: red;">Template 1 non trouvé</p>';
    exit;
}

echo '<h2>1. Données brutes (premiers 500 caractères)</h2>';
echo '<pre style="background: #f0f0f0; padding: 10px; font-size: 12px;">' . esc_html(substr($template['template_data'], 0, 500)) . '...</pre>';

echo '<h2>2. Décodage JSON du template_data</h2>';
$template_data = json_decode($template['template_data'], true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo '<p style="color: red;">❌ Erreur JSON: ' . json_last_error_msg() . '</p>';
    echo '<p>Erreur à la position approximative: ' . strpos($template['template_data'], substr($template['template_data'], json_last_error(), 10)) . '</p>';
    exit;
}

echo '<p style="color: green;">✅ JSON décodé avec succès</p>';

echo '<h2>3. Analyse du champ elements</h2>';
if (!isset($template_data['elements'])) {
    echo '<p style="color: red;">❌ Champ elements manquant</p>';
    exit;
}

$elements = $template_data['elements'];
echo '<p>Type: ' . gettype($elements) . '</p>';

if (is_string($elements)) {
    echo '<p>Longueur: ' . strlen($elements) . ' caractères</p>';
    echo '<h3>Contenu (premiers 300 caractères):</h3>';
    echo '<pre style="background: #f0f0f0; padding: 10px; font-size: 11px;">' . esc_html(substr($elements, 0, 300)) . '...</pre>';

    echo '<h2>4. Test décodage elements</h2>';
    $decoded_elements = json_decode($elements, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        echo '<p style="color: green;">✅ Elements décodés: ' . count($decoded_elements) . ' éléments</p>';
        echo '<h3>Premier élément:</h3>';
        echo '<pre style="background: #f0f0f0; padding: 10px;">' . esc_html(print_r($decoded_elements[0], true)) . '</pre>';
    } else {
        echo '<p style="color: red;">❌ Erreur décodage: ' . json_last_error_msg() . '</p>';

        // Chercher le caractère problématique
        $error_pos = json_last_error();
        if ($error_pos > 0 && $error_pos < strlen($elements)) {
            $context_start = max(0, $error_pos - 50);
            $context_end = min(strlen($elements), $error_pos + 50);
            echo '<h3>Contexte autour de l\'erreur (position ' . $error_pos . '):</h3>';
            echo '<pre style="background: #ffe0e0; padding: 10px; font-size: 11px;">...' . esc_html(substr($elements, $context_start, $context_end - $context_start)) . '...</pre>';
        }
    }
} else {
    echo '<p>Elements est déjà un ' . gettype($elements) . '</p>';
    echo '<pre style="background: #f0f0f0; padding: 10px;">' . esc_html(print_r($elements, true)) . '</pre>';
}

echo '<hr>';
echo '<p><a href="debug_templates.php">Retour au debug complet</a></p>';
?>