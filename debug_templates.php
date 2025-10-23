<?php
// Script de débogage pour vérifier les templates en base
require_once('../../../wp-load.php');

global $wpdb;
$table = $wpdb->prefix . 'pdf_builder_templates';

echo "<h1>Débogage des templates PDF Builder</h1>";

// Vérifier si la table existe
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
echo "<p>Table '$table' existe: " . ($table_exists ? 'OUI' : 'NON') . "</p>";

if ($table_exists) {
    // Compter les templates
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    echo "<p>Nombre de templates: $count</p>";

    if ($count > 0) {
        // Lister tous les templates
        $templates = $wpdb->get_results("SELECT id, name, template_data FROM $table ORDER BY id DESC", ARRAY_A);

        echo "<h2>Templates trouvés:</h2>";
        foreach ($templates as $template) {
            echo "<h3>Template ID: {$template['id']} - Nom: {$template['name']}</h3>";

            $data = json_decode($template['template_data'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "<p>JSON valide</p>";

                if (isset($data['elements'])) {
                    $element_count = count($data['elements']);
                    echo "<p>Nombre d'éléments: $element_count</p>";

                    if ($element_count > 0) {
                        echo "<details><summary>Voir les éléments</summary><pre>";
                        print_r($data['elements']);
                        echo "</pre></details>";
                    }
                } else {
                    echo "<p style='color: red;'>Aucun champ 'elements' trouvé</p>";
                    echo "<details><summary>Voir la structure JSON</summary><pre>";
                    print_r($data);
                    echo "</pre></details>";
                }
            } else {
                echo "<p style='color: red;'>JSON invalide: " . json_last_error_msg() . "</p>";
                echo "<p>Données brutes: <code>" . htmlspecialchars(substr($template['template_data'], 0, 200)) . "...</code></p>";
            }

            echo "<hr>";
        }
    } else {
        echo "<p style='color: orange;'>Aucun template trouvé en base de données</p>";
    }
} else {
    echo "<p style='color: red;'>La table n'existe pas</p>";
}
?>