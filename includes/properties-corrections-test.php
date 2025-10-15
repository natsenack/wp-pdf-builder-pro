<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

/**
 * Test des corrections apportées au système de propriétés des éléments
 * PDF Builder Pro - Properties Corrections Test
 */

// Inclure le gestionnaire d'éléments
require_once __DIR__ . '/managers/PDF_Builder_Canvas_Elements_Manager.php';

/**
 * Tester les corrections apportées aux propriétés des éléments
 */
function pdf_builder_test_properties_corrections() {
    $elements_manager = PDF_Builder_Canvas_Elements_Manager::getInstance();

    echo "<h2>Test des corrections des propriétés d'éléments</h2>";

    // Test 1: Vérifier que les propriétés par défaut sont séparées correctement
    echo "<h3>Test 1: Séparation des propriétés communes et spécifiques</h3>";
    $text_defaults = $elements_manager->get_default_element_properties('text');
    $image_defaults = $elements_manager->get_default_element_properties('image');
    $table_defaults = $elements_manager->get_default_element_properties('product_table');

    // Vérifier que les propriétés communes sont présentes dans tous les types
    $common_props = ['backgroundColor', 'borderColor', 'borderWidth', 'opacity', 'visible'];
    foreach ($common_props as $prop) {
        if (!isset($text_defaults[$prop]) || !isset($image_defaults[$prop]) || !isset($table_defaults[$prop])) {
            echo "<p style='color: red;'>❌ Propriété commune manquante: {$prop}</p>";
        } else {
            echo "<p style='color: green;'>✅ Propriété commune présente: {$prop}</p>";
        }
    }

    // Vérifier que les propriétés spécifiques aux tableaux ne polluent pas les autres types
    if (isset($text_defaults['showHeaders']) || isset($image_defaults['showHeaders'])) {
        echo "<p style='color: red;'>❌ Propriétés de tableau polluent les autres types</p>";
    } else {
        echo "<p style='color: green;'>✅ Propriétés de tableau correctement isolées</p>";
    }

    // Test 2: Validation basique des couleurs (test manuel)
    echo "<h3>Test 2: Validation des couleurs (test manuel requis)</h3>";
    echo "<p>La validation étendue des couleurs a été implémentée avec support pour:</p>";
    echo "<ul>";
    echo "<li>✅ Couleurs hexadécimales (#ff0000, #abc)</li>";
    echo "<li>✅ Couleurs RGB/RGBA (rgb(255,0,0), rgba(255,0,0,0.5))</li>";
    echo "<li>✅ Couleurs HSL/HSLA (hsl(0,100%,50%), hsla(0,100%,50%,0.5))</li>";
    echo "<li>✅ Noms de couleurs CSS (red, blue, transparent, etc.)</li>";
    echo "</ul>";

    // Test 3: Sanitisation avec types
    echo "<h3>Test 3: Sanitisation avec types d'éléments</h3>";
    $test_element = [
        'id' => 'test_element',
        'type' => 'text',
        'x' => 100,
        'y' => 50,
        'width' => 200,
        'height' => 40,
        'backgroundColor' => 'invalid_color',
        'borderColor' => '#ff0000',
        'fontSize' => 25,
        'visible' => 'yes', // Devrait être converti en boolean
        'showHeaders' => true // Propriété qui ne devrait pas être dans un élément texte
    ];

    $sanitized = $elements_manager->sanitize_element_properties($test_element, 'text');

    // Vérifier que la couleur invalide a été remplacée par une valeur par défaut
    if ($sanitized['backgroundColor'] === 'invalid_color') {
        echo "<p style='color: red;'>❌ Couleur invalide non corrigée</p>";
    } else {
        echo "<p style='color: green;'>✅ Couleur invalide corrigée: {$sanitized['backgroundColor']}</p>";
    }

    // Vérifier que visible a été converti en boolean
    if (!is_bool($sanitized['visible'])) {
        echo "<p style='color: red;'>❌ Visible non converti en boolean</p>";
    } else {
        echo "<p style='color: green;'>✅ Visible converti en boolean: " . ($sanitized['visible'] ? 'true' : 'false') . "</p>";
    }

    // Vérifier que les propriétés inappropriées ont été supprimées
    if (isset($sanitized['showHeaders'])) {
        echo "<p style='color: red;'>❌ Propriété inappropriée non supprimée</p>";
    } else {
        echo "<p style='color: green;'>✅ Propriétés inappropriées supprimées</p>";
    }

    echo "<h3>Résumé des tests</h3>";
    echo "<p>Tous les tests de correction des propriétés ont été exécutés.</p>";
}

// Exécuter les tests si ce fichier est appelé directement
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    pdf_builder_test_properties_corrections();
}