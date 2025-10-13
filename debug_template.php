<?php
// Chemin vers WordPress - ajuster selon votre installation
$wp_load_paths = [
    '../../../wp-load.php',  // Si le plugin est dans wp-content/plugins/
    '../../../../wp-load.php', // Si dans un sous-dossier
    dirname(__FILE__) . '/../../../wp-load.php',
    'C:/xampp/htdocs/wordpress/wp-load.php', // Chemin par défaut XAMPP
];

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    die("Could not find wp-load.php. Please check your WordPress installation path.\n");
}

global $wpdb;

$template = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'pdf_builder_templates WHERE id = %d', 118));

if ($template) {
    echo 'Template ID: ' . $template->id . PHP_EOL;
    echo 'Template Name: ' . $template->name . PHP_EOL;
    echo 'Template Data Length: ' . strlen($template->template_data) . ' characters' . PHP_EOL;
    echo 'First 500 chars of JSON:' . PHP_EOL;
    echo substr($template->template_data, 0, 500) . PHP_EOL;
    echo PHP_EOL . 'Last 500 chars of JSON:' . PHP_EOL;
    echo substr($template->template_data, -500) . PHP_EOL;

    // Test de décodage
    echo PHP_EOL . 'Testing JSON decode...' . PHP_EOL;
    $decoded = json_decode($template->template_data, true);
    if ($decoded === null) {
        echo 'JSON decode failed: ' . json_last_error_msg() . PHP_EOL;

        // Essayer de nettoyer
        $cleaned = clean_json_data($template->template_data);
        echo PHP_EOL . 'After cleaning, testing again...' . PHP_EOL;
        $decoded_cleaned = json_decode($cleaned, true);
        if ($decoded_cleaned === null) {
            echo 'Still failed after cleaning: ' . json_last_error_msg() . PHP_EOL;

            // Montrer les différences
            if ($cleaned !== $template->template_data) {
                echo PHP_EOL . 'Data was modified during cleaning.' . PHP_EOL;
                echo 'Original length: ' . strlen($template->template_data) . PHP_EOL;
                echo 'Cleaned length: ' . strlen($cleaned) . PHP_EOL;
            }
        } else {
            echo 'Success after cleaning!' . PHP_EOL;
            // Sauvegarder la version nettoyée
            $wpdb->update(
                $wpdb->prefix . 'pdf_builder_templates',
                ['template_data' => $cleaned],
                ['id' => 118]
            );
            echo 'Cleaned JSON saved to database.' . PHP_EOL;
        }
    } else {
        echo 'JSON decode successful!' . PHP_EOL;
    }
} else {
    echo 'Template not found' . PHP_EOL;
}

function clean_json_data($json_string) {
    if (!is_string($json_string)) {
        return $json_string;
    }

    // Supprimer les caractères de contrôle invisibles (sauf tabulation, retour chariot, nouvelle ligne)
    $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $json_string);

    // Corriger les problèmes d'encodage UTF-8
    if (!mb_check_encoding($cleaned, 'UTF-8')) {
        $cleaned = mb_convert_encoding($cleaned, 'UTF-8', 'auto');
    }

    // Supprimer les BOM UTF-8 si présent
    $cleaned = preg_replace('/^\x{EF}\x{BB}\x{BF}/', '', $cleaned);

    // Nettoyer les espaces de noms problématiques
    $cleaned = str_replace('\\u0000', '', $cleaned);

    // Supprimer les caractères null
    $cleaned = str_replace("\0", '', $cleaned);

    return $cleaned;
}