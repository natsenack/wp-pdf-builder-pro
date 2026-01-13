<?php
/**
 * Test script pour vérifier la sauvegarde des paramètres canvas
 */

// Test de sauvegarde des paramètres allowed
echo "<h2>Test de sauvegarde des paramètres Canvas</h2>";

// Test des paramètres allowed_dpis
$test_dpis = ['72', '96', '150'];
update_option('pdf_builder_canvas_allowed_dpis', $test_dpis);
$saved_dpis = get_option('pdf_builder_canvas_allowed_dpis', []);
echo "<p>DPI test: " . (json_encode($test_dpis) === json_encode($saved_dpis) ? "✅ RÉUSSI" : "❌ ÉCHEC") . "</p>";
echo "<p>Saved: " . json_encode($saved_dpis) . "</p>";

// Test des paramètres allowed_formats
$test_formats = ['A4', 'A3'];
update_option('pdf_builder_canvas_allowed_formats', $test_formats);
$saved_formats = get_option('pdf_builder_canvas_allowed_formats', []);
echo "<p>Formats test: " . (json_encode($test_formats) === json_encode($saved_formats) ? "✅ RÉUSSI" : "❌ ÉCHEC") . "</p>";
echo "<p>Saved: " . json_encode($saved_formats) . "</p>";

// Test des paramètres allowed_orientations
$test_orientations = ['portrait'];
update_option('pdf_builder_canvas_allowed_orientations', $test_orientations);
$saved_orientations = get_option('pdf_builder_canvas_allowed_orientations', []);
echo "<p>Orientations test: " . (json_encode($test_orientations) === json_encode($saved_orientations) ? "✅ RÉUSSI" : "❌ ÉCHEC") . "</p>";
echo "<p>Saved: " . json_encode($saved_orientations) . "</p>";

// Test d'un paramètre canvas normal
update_option('pdf_builder_canvas_test_param', 'test_value');
$saved_test = get_option('pdf_builder_canvas_test_param', '');
echo "<p>Paramètre normal test: " . ($saved_test === 'test_value' ? "✅ RÉUSSI" : "❌ ÉCHEC") . "</p>";
echo "<p>Saved: " . $saved_test . "</p>";

echo "<hr>";
echo "<h3>Paramètres actuellement sauvegardés:</h3>";
echo "<ul>";
echo "<li>allowed_dpis: " . json_encode(get_option('pdf_builder_canvas_allowed_dpis', [])) . "</li>";
echo "<li>allowed_formats: " . json_encode(get_option('pdf_builder_canvas_allowed_formats', [])) . "</li>";
echo "<li>allowed_orientations: " . json_encode(get_option('pdf_builder_canvas_allowed_orientations', [])) . "</li>";
echo "</ul>";
?>