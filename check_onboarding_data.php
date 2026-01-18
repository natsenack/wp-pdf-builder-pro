<?php
require_once('C:/wamp64/www/wp-config.php');
global $wpdb;

$table_name = $wpdb->prefix . 'pdf_builder_settings';
$onboarding_data = $wpdb->get_row("SELECT option_value FROM $table_name WHERE option_name = 'pdf_builder_onboarding'");

if ($onboarding_data) {
    echo "Données onboarding trouvées dans wp_pdf_builder_settings:\n";
    $data = unserialize($onboarding_data->option_value);
    echo "Clés des données: " . implode(', ', array_keys($data)) . "\n";
    echo "État complété: " . ($data['completed'] ? 'Oui' : 'Non') . "\n";
    echo "Étape actuelle: " . $data['current_step'] . "\n";
    echo "Étapes complétées: " . implode(', ', $data['steps_completed']) . "\n";
} else {
    echo "Aucune donnée onboarding trouvée dans la table personnalisée.\n";
}

// Vérifier aussi dans wp_options au cas où
$legacy_data = get_option('pdf_builder_onboarding');
if ($legacy_data) {
    echo "\nDonnées trouvées aussi dans wp_options (legacy):\n";
    echo "Clés: " . implode(', ', array_keys($legacy_data)) . "\n";
} else {
    echo "\nAucune donnée legacy dans wp_options.\n";
}
?>