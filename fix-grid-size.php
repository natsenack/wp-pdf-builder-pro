<?php
/**
 * Script pour corriger la taille de grille qui dépasse la limite
 */

// Inclure WordPress
require_once('plugin/bootstrap.php');

// Vérifier la valeur actuelle
$current_value = get_option('pdf_builder_canvas_grid_size', 20);
echo "Valeur actuelle de pdf_builder_canvas_grid_size: $current_value\n";

// Si la valeur dépasse 100, la corriger
if ($current_value > 100) {
    update_option('pdf_builder_canvas_grid_size', 100);
    echo "Valeur corrigée à 100\n";
} else {
    echo "La valeur est déjà dans les limites\n";
}

// Vérifier après correction
$new_value = get_option('pdf_builder_canvas_grid_size', 20);
echo "Nouvelle valeur: $new_value\n";

echo "Correction terminée.\n";