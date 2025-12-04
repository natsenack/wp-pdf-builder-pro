echo '=== DONNÉES COMPLÈTES DE LA TABLE TEMPLATES ===\n';
require_once 'plugin/bootstrap.php';
try {
    global $wpdb;
    $table = $wpdb->prefix . 'pdf_builder_templates';

    // Vérifier si la table existe
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
    if (!$table_exists) {
        echo "Table $table n'existe pas\n";
        exit;
    }

    // Récupérer tous les templates avec toutes les colonnes
    $templates = $wpdb->get_results("SELECT id, name, type, template_data FROM $table ORDER BY id LIMIT 10", ARRAY_A);

    if (empty($templates)) {
        echo "Aucun template trouvé en base\n";
        exit;
    }

    foreach ($templates as $template) {
        echo "=== TEMPLATE ID: {$template['id']} ===\n";
        echo "  id: {$template['id']}\n";
        echo "  name: '" . ($template['name'] ?? 'NULL') . "'\n";
        echo "  type: '" . ($template['type'] ?? 'NULL') . "'\n";
        echo "  template_data (longueur): " . strlen($template['template_data'] ?? '') . " caractères\n";

        // Décoder le JSON pour voir le contenu
        $json_data = json_decode($template['template_data'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "  JSON valide: OUI\n";
            if (isset($json_data['name'])) {
                echo "  name dans JSON: '" . $json_data['name'] . "'\n";
            } else {
                echo "  name dans JSON: NON PRÉSENT\n";
            }
            echo "  Clés JSON: " . implode(', ', array_keys($json_data)) . "\n";
        } else {
            echo "  JSON valide: NON - " . json_last_error_msg() . "\n";
        }
        echo "\n";
    }

} catch (Exception $e) {
    echo 'Erreur: ' . $e->getMessage() . '\n';
}