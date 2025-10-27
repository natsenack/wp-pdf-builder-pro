<?php
/**
 * Script de diagnostic pour analyser les doublons d'éléments dans les templates
 */

// Inclure WordPress
require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Accès non autorisé');
}

echo "<h1>Diagnostic des doublons d'éléments dans les templates</h1>";

// Connexion à la base de données
global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

// Récupérer tous les templates
$templates = $wpdb->get_results("SELECT id, name, template_data FROM $table_templates", ARRAY_A);

echo "<p>Analyse de " . count($templates) . " templates...</p>";

$duplicates_found = false;

foreach ($templates as $template) {
    $template_data = json_decode($template['template_data'], true);

    if (!$template_data || !isset($template_data['elements'])) {
        continue;
    }

    // Compter les éléments par type
    $element_counts = [];
    foreach ($template_data['elements'] as $element) {
        $type = $element['type'] ?? 'unknown';
        if (!isset($element_counts[$type])) {
            $element_counts[$type] = 0;
        }
        $element_counts[$type]++;
    }

    // Vérifier les doublons
    foreach ($element_counts as $type => $count) {
        if ($count > 1) {
            echo "<h3>Template: {$template['name']} (ID: {$template['id']})</h3>";
            echo "<p><strong>Doublon détecté:</strong> {$count} éléments de type '{$type}'</p>";

            // Lister les détails des éléments dupliqués
            $duplicates = array_filter($template_data['elements'], function($el) use ($type) {
                return ($el['type'] ?? 'unknown') === $type;
            });

            echo "<ul>";
            foreach ($duplicates as $index => $element) {
                $id = $element['id'] ?? 'N/A';
                $x = $element['x'] ?? 'N/A';
                $y = $element['y'] ?? 'N/A';
                echo "<li>Élément #{$index}: ID={$id}, x={$x}, y={$y}</li>";
            }
            echo "</ul>";

            $duplicates_found = true;
        }
    }
}

if (!$duplicates_found) {
    echo "<p style='color: green;'>✅ Aucun doublon d'éléments détecté dans les templates.</p>";
} else {
    echo "<p style='color: red;'>❌ Des doublons ont été détectés. Il faut les corriger.</p>";
}

echo "<hr>";
echo "<h2>Actions recommandées:</h2>";
echo "<ul>";
echo "<li>Vérifier la logique d'ajout d'éléments dans le canvas</li>";
echo "<li>Nettoyer les templates corrompus</li>";
echo "<li>Ajouter des vérifications de dédoublonnement</li>";
echo "</ul>";
?>