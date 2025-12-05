<?php
/**
 * Test du système de rôles PDF Builder
 * À exécuter dans l'admin WordPress
 */

// Inclure les helpers
require_once __DIR__ . '/resources/templates/admin/settings-helpers.php';

echo "<h1>Test du système de rôles PDF Builder</h1>";

// Test 1: Récupération des rôles actuels
echo "<h2>Test 1: Récupération des rôles actuels</h2>";
$current_roles = pdf_builder_get_allowed_roles();
echo "<p>Rôles autorisés actuels: <strong>" . implode(', ', $current_roles) . "</strong></p>";

// Test 2: Sauvegarde de nouveaux rôles
echo "<h2>Test 2: Sauvegarde de nouveaux rôles</h2>";
$test_roles = ['administrator', 'editor', 'author'];
$saved_roles = pdf_builder_save_allowed_roles($test_roles);
echo "<p>Rôles sauvegardés: <strong>" . implode(', ', $saved_roles) . "</strong></p>";

// Test 3: Vérification que les rôles ont été sauvegardés
echo "<h2>Test 3: Vérification de la sauvegarde</h2>";
$retrieved_roles = pdf_builder_get_allowed_roles();
echo "<p>Rôles récupérés après sauvegarde: <strong>" . implode(', ', $retrieved_roles) . "</strong></p>";

// Test 4: Vérification des données brutes en BDD
echo "<h2>Test 4: Données brutes en base de données</h2>";
$settings = get_option('pdf_builder_settings', []);
$raw_roles = $settings['pdf_builder_allowed_roles'] ?? 'NON TROUVÉ';
echo "<p>Données brutes pdf_builder_allowed_roles: <strong>" . (is_array($raw_roles) ? implode(', ', $raw_roles) : $raw_roles) . "</strong></p>";

// Test 5: Test AJAX (simulation)
echo "<h2>Test 5: Simulation de récupération AJAX</h2>";
echo "<p>Endpoint AJAX: <code>pdf_builder_get_allowed_roles</code></p>";
echo "<p>Cette fonction devrait retourner les mêmes données que ci-dessus.</p>";

// Test 6: Vérification des permissions
echo "<h2>Test 6: Vérification des permissions utilisateur</h2>";
$current_user = wp_get_current_user();
$user_roles = $current_user->roles;
echo "<p>Rôles de l'utilisateur actuel: <strong>" . implode(', ', $user_roles) . "</strong></p>";

$has_access = false;
foreach ($user_roles as $role) {
    if (in_array($role, $retrieved_roles)) {
        $has_access = true;
        break;
    }
}
echo "<p>Accès PDF Builder: <strong>" . ($has_access ? 'OUI' : 'NON') . "</strong></p>";

echo "<hr>";
echo "<p><strong>Résultat du test:</strong> " . (count($saved_roles) === count($retrieved_roles) && $saved_roles === $retrieved_roles ? '✅ SUCCÈS' : '❌ ÉCHEC') . "</p>";
?>