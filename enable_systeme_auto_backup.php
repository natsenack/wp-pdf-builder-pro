<?php
// Script pour activer les sauvegardes automatiques dans l'onglet système
require_once('../../../wp-load.php');

$settings = get_option('pdf_builder_settings', []);
$settings['pdf_builder_systeme_auto_backup'] = '1';
$settings['pdf_builder_systeme_auto_backup_frequency'] = 'every_minute'; // Pour les tests

if (update_option('pdf_builder_settings', $settings)) {
    echo "✅ Sauvegardes automatiques activées dans l'onglet SYSTÈME !\n";
    echo "Paramètres sauvegardés :\n";
    echo "- pdf_builder_systeme_auto_backup: " . $settings['pdf_builder_systeme_auto_backup'] . "\n";
    echo "- pdf_builder_systeme_auto_backup_frequency: " . $settings['pdf_builder_systeme_auto_backup_frequency'] . "\n";
} else {
    echo "❌ Erreur lors de la sauvegarde des paramètres\n";
}

echo "\nVérification :\n";
$check_settings = get_option('pdf_builder_settings', []);
echo "- Auto backup: " . ($check_settings['pdf_builder_systeme_auto_backup'] ?? 'NON DÉFINI') . "\n";
echo "- Fréquence: " . ($check_settings['pdf_builder_systeme_auto_backup_frequency'] ?? 'NON DÉFINI') . "\n";
?>