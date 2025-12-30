<?php
/**
 * Script pour corriger la taille de grille qui dépasse la limite
 * À exécuter via URL: /wp-content/plugins/wp-pdf-builder-pro/fix-grid-size.php
 */

// Sécurité - vérifier que WordPress est chargé
if (!defined('ABSPATH')) {
    // Essayer de charger WordPress
    $wp_load_path = '../../../wp-load.php';
    if (file_exists($wp_load_path)) {
        require_once($wp_load_path);
    } else {
        die('Impossible de charger WordPress');
    }
}

// Vérifier la valeur actuelle
$current_value = get_option('pdf_builder_canvas_grid_size', 20);
echo "<h2>Correction de la taille de grille</h2>";
echo "<p>Valeur actuelle de pdf_builder_canvas_grid_size: <strong>$current_value</strong></p>";

// Si la valeur dépasse 100, la corriger
if ($current_value > 100) {
    update_option('pdf_builder_canvas_grid_size', 100);
    echo "<p style='color: green;'>✅ Valeur corrigée à 100</p>";
} else {
    echo "<p style='color: blue;'>ℹ️ La valeur est déjà dans les limites</p>";
}

// Vérifier après correction
$new_value = get_option('pdf_builder_canvas_grid_size', 20);
echo "<p>Nouvelle valeur: <strong>$new_value</strong></p>";

echo "<hr>";
echo "<p><a href='../admin.php?page=pdf-builder-settings'>Retour aux paramètres</a></p>";
echo "<p>Correction terminée.</p>";