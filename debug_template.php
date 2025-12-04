<?php
// Test pour vérifier la structure des données du template
define('ABSPATH', 'C:\\xampp\\htdocs\\wordpress\\');
define('WPINC', 'wp-includes');
define('WP_DEBUG', true);

// Simuler les constantes du plugin
define('PDF_BUILDER_PLUGIN_FILE', __FILE__);
define('PDF_BUILDER_PLUGIN_DIR', dirname(__FILE__) . '/plugin/');

// Charger les classes nécessaires
require_once 'plugin/src/Admin/Processors/TemplateProcessor.php';
require_once 'plugin/src/Admin/PDF_Builder_Admin.php';

// Simuler une connexion à la base de données
global $wpdb;
$wpdb = new stdClass();
$wpdb->prefix = 'wp_';

// Simuler la table des templates avec des données réalistes
$wpdb->get_row = function($query, $output = OBJECT) {
    // Simuler un template avec un nom dans la base de données
    return [
        'id' => 1,
        'name' => 'Facture Standard', // Nom dans la base de données
        'template_data' => '{"canvas":{"width":595,"height":842},"elements":[]}' // JSON sans nom
    ];
};

$wpdb->get_var = function($query) {
    return 'wp_pdf_builder_templates';
};

try {
    $admin = new PDF_Builder_Admin();
    $processor = $admin->getTemplateProcessor();
    $template_data = $processor->loadTemplateRobust(1);

    echo "=== STRUCTURE DES DONNÉES RETOURNÉES ===\n";
    echo "Type: " . gettype($template_data) . "\n";
    if (is_array($template_data)) {
        echo "Clés disponibles: " . implode(', ', array_keys($template_data)) . "\n";
        echo "Nom trouvé: '" . ($template_data['name'] ?? 'NON TROUVÉ') . "'\n";
        echo "\nDonnées complètes:\n";
        print_r($template_data);
    } else {
        echo "Données non-array: " . var_export($template_data, true) . "\n";
    }

} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>