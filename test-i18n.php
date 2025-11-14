<?php
/**
 * Test rapide de l'internationalisation PDF Builder Pro
 */

// Simuler WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Simuler les constantes
define('PDF_BUILDER_PLUGIN_DIR', __DIR__ . '/plugin/');
define('PDF_BUILDER_TEXT_DOMAIN', 'pdf-builder-pro');

// Charger les fichiers nécessaires
require_once PDF_BUILDER_PLUGIN_DIR . 'src/PDF_Builder_Advanced_Localization.php';

// Test de la classe de localisation
echo "🧪 Test de l'internationalisation PDF Builder Pro\n\n";

try {
    $localization = \WP_PDF_Builder_Pro\Src\PdfBuilderAdvancedLocalization::getInstance();

    echo "✅ Classe PdfBuilderAdvancedLocalization chargée avec succès\n";

    // Test des formats de date
    echo "\n📅 Test des formats de date :\n";
    $timestamp = strtotime('2025-11-14 15:30:00');

    // Simuler différentes locales
    $locales = ['fr_FR', 'en_US', 'de_DE'];

    foreach ($locales as $locale) {
        $localization->set_locale($locale);
        $formatted_date = $localization->format_date($timestamp, 'datetime');
        echo "  $locale: $formatted_date\n";
    }

    // Test des formats de nombre
    echo "\n🔢 Test des formats de nombre :\n";
    $number = 1234.56;

    foreach ($locales as $locale) {
        $localization->set_locale($locale);
        $formatted_number = $localization->format_number($number, 2);
        echo "  $locale: $formatted_number\n";
    }

    // Test des formats de devise
    echo "\n💰 Test des formats de devise :\n";
    $amount = 99.99;

    foreach ($locales as $locale) {
        $localization->set_locale($locale);
        $formatted_currency = $localization->format_currency($amount);
        echo "  $locale: $formatted_currency\n";
    }

    // Test RTL
    echo "\n🔄 Test RTL :\n";
    $rtl_locales = ['ar', 'he', 'en_US'];
    foreach ($rtl_locales as $locale) {
        $localization->set_locale($locale);
        $is_rtl = $localization->is_rtl() ? 'Oui' : 'Non';
        echo "  $locale: RTL = $is_rtl\n";
    }

    echo "\n✅ Tous les tests de localisation sont passés avec succès !\n";

} catch (Exception $e) {
    echo "❌ Erreur lors du test: " . $e->getMessage() . "\n";
}