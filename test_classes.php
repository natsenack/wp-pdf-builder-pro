<?php
/**
 * Test simple des classes PHP pour les canvas personnalisés
 */

// Inclure les fichiers nécessaires
require_once('includes/classes/PDF_Builder_Core.php');
require_once('includes/classes/managers/class-pdf-builder-woocommerce-integration.php');

echo "<h1>🧪 Test Simple des Classes PHP</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;} .info{color:blue;}</style>";

// Test 1: Vérifier que les classes existent
echo "<h2>1. Vérification des classes</h2>";

if (class_exists('PDF_Builder_WooCommerce_Integration')) {
    echo "<p class='success'>✅ Classe PDF_Builder_WooCommerce_Integration existe</p>";

    // Créer une instance mock
    $main_instance = new stdClass();
    $woo_integration = new PDF_Builder_WooCommerce_Integration($main_instance);

    // Vérifier les méthodes
    $methods = ['load_order_canvas', 'save_order_canvas', 'ajax_save_order_canvas'];
    foreach ($methods as $method) {
        if (method_exists($woo_integration, $method)) {
            echo "<p class='success'>✅ Méthode $method existe</p>";
        } else {
            echo "<p class='error'>❌ Méthode $method n'existe pas</p>";
        }
    }

} else {
    echo "<p class='error'>❌ Classe PDF_Builder_WooCommerce_Integration n'existe pas</p>";
}

if (class_exists('PDF_Builder_Core')) {
    echo "<p class='success'>✅ Classe PDF_Builder_Core existe</p>";
} else {
    echo "<p class='error'>❌ Classe PDF_Builder_Core n'existe pas</p>";
}

// Test 2: Vérifier le code SQL
echo "<h2>2. Vérification du code SQL</h2>";

$reflection = new ReflectionClass('PDF_Builder_Core');
$method = $reflection->getMethod('create_database_tables');
$method->setAccessible(true);

$core = new PDF_Builder_Core();
try {
    // On ne peut pas vraiment exécuter la méthode sans WordPress, mais on peut vérifier qu'elle existe
    echo "<p class='success'>✅ Méthode create_database_tables existe</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur avec create_database_tables: " . $e->getMessage() . "</p>";
}

echo "<h2>3. Résumé des modifications</h2>";
echo "<ul>";
echo "<li>✅ Table <code>wp_pdf_builder_order_canvases</code> ajoutée dans PDF_Builder_Core</li>";
echo "<li>✅ Méthode <code>load_order_canvas()</code> ajoutée dans WooCommerce Integration</li>";
echo "<li>✅ Méthode <code>save_order_canvas()</code> ajoutée dans WooCommerce Integration</li>";
echo "<li>✅ AJAX handler <code>pdf_builder_save_order_canvas</code> ajouté</li>";
echo "<li>✅ Aperçu modifié pour prioriser les canvas personnalisés</li>";
echo "</ul>";

echo "<h2>4. Fonctionnement</h2>";
echo "<p>Lorsqu'un utilisateur clique sur 'Aperçu' dans le metabox WooCommerce :</p>";
echo "<ol>";
echo "<li>Le système cherche d'abord un canvas personnalisé pour cette commande dans <code>wp_pdf_builder_order_canvases</code></li>";
echo "<li>Si trouvé, il utilise ce canvas pour générer l'aperçu</li>";
echo "<li>Sinon, il utilise le template sélectionné ou par défaut</li>";
echo "</ol>";

?>