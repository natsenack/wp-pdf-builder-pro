<?php
/**
 * Script de test pour vérifier la synchronisation des systèmes d'aperçu PDF
 */

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclure WordPress
$wp_load_paths = [
    '../../../wp-load.php',
    '../../../../wp-load.php',
    dirname(__FILE__) . '/../../../wp-load.php',
    'C:/xampp/htdocs/wordpress/wp-load.php',
];

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_loaded = true;
        echo "✅ WordPress loaded from: $path\n";
        break;
    }
}

if (!$wp_loaded) {
    die("❌ Could not find wp-load.php\n");
}

echo "<h1>🧪 Test de synchronisation des systèmes d'aperçu PDF</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

// Test 1: Vérifier que les classes existent
echo "<h2>1. Test des classes et méthodes</h2>";

$tests = [
    'PDF_Builder_Admin class' => class_exists('PDF_Builder_Admin'),
    'PDF_Builder_WooCommerce_Data_Provider class' => class_exists('PDF_Builder_WooCommerce_Data_Provider'),
];

foreach ($tests as $testName => $result) {
    echo "<p class='" . ($result ? 'success' : 'error') . "'>";
    echo ($result ? '✅' : '❌') . " <strong>$testName</strong>";
    echo "</p>";
}

// Test 2: Instancier PDF_Builder_Admin
echo "<h2>2. Test d'instanciation</h2>";

try {
    $admin = PDF_Builder_Admin::getInstance();
    echo "<p class='success'>✅ PDF_Builder_Admin instancié avec succès</p>";

    // Test des méthodes
    $methods = [
        'generate_unified_html' => method_exists($admin, 'generate_unified_html'),
        'generate_html_from_template_data' => method_exists($admin, 'generate_html_from_template_data'),
        'generate_order_html' => method_exists($admin, 'generate_order_html'),
        'generate_order_products_table' => method_exists($admin, 'generate_order_products_table'),
        'replace_order_variables' => method_exists($admin, 'replace_order_variables'),
    ];

    foreach ($methods as $methodName => $exists) {
        echo "<p class='" . ($exists ? 'success' : 'error') . "'>";
        echo ($exists ? '✅' : '❌') . " Méthode <code>$methodName</code> " . ($exists ? 'existe' : 'manquante');
        echo "</p>";
    }

} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur lors de l'instanciation: " . $e->getMessage() . "</p>";
}

// Test 3: Test des données de template
echo "<h2>3. Test des données de template</h2>";

$test_template = [
    'elements' => [
        [
            'type' => 'text',
            'x' => 50,
            'y' => 50,
            'width' => 200,
            'height' => 30,
            'content' => 'Test de texte statique',
            'style' => ['fontSize' => 14, 'color' => '#000000']
        ],
        [
            'type' => 'customer_name',
            'x' => 50,
            'y' => 100,
            'width' => 200,
            'height' => 30,
            'content' => 'Nom du client',
            'style' => ['fontSize' => 14, 'color' => '#000000']
        ]
    ]
];

try {
    // Test sans commande (aperçu éditeur)
    $html_without_order = $admin->generate_unified_html($test_template, null);
    echo "<p class='success'>✅ HTML généré sans commande (aperçu éditeur)</p>";
    echo "<p class='info'>Longueur HTML: " . strlen($html_without_order) . " caractères</p>";

    // Vérifier que le contenu statique est présent
    if (strpos($html_without_order, 'Test de texte statique') !== false) {
        echo "<p class='success'>✅ Contenu statique trouvé dans le HTML</p>";
    } else {
        echo "<p class='error'>❌ Contenu statique manquant dans le HTML</p>";
    }

    // Vérifier que les variables WooCommerce ne sont pas remplacées
    if (strpos($html_without_order, 'Nom du client') !== false) {
        echo "<p class='success'>✅ Variables WooCommerce non remplacées (aperçu éditeur)</p>";
    } else {
        echo "<p class='error'>❌ Variables WooCommerce remplacées incorrectement</p>";
    }

} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur génération HTML sans commande: " . $e->getMessage() . "</p>";
}

// Test 4: Test avec commande WooCommerce
echo "<h2>4. Test avec commande WooCommerce</h2>";

$order_id = 1; // À adapter selon vos données de test
$order = wc_get_order($order_id);

if ($order) {
    echo "<p class='success'>✅ Commande WooCommerce trouvée: #" . $order->get_order_number() . "</p>";

    try {
        // Test avec commande (aperçu commande)
        $html_with_order = $admin->generate_unified_html($test_template, $order);
        echo "<p class='success'>✅ HTML généré avec commande (aperçu commande)</p>";
        echo "<p class='info'>Longueur HTML: " . strlen($html_with_order) . " caractères</p>";

        // Vérifier que le contenu statique est présent
        if (strpos($html_with_order, 'Test de texte statique') !== false) {
            echo "<p class='success'>✅ Contenu statique trouvé dans le HTML avec commande</p>";
        } else {
            echo "<p class='error'>❌ Contenu statique manquant dans le HTML avec commande</p>";
        }

        // Vérifier que les variables WooCommerce sont remplacées
        $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        if (strpos($html_with_order, $customer_name) !== false) {
            echo "<p class='success'>✅ Variables WooCommerce remplacées (nom client: $customer_name)</p>";
        } else {
            echo "<p class='error'>❌ Variables WooCommerce non remplacées</p>";
        }

    } catch (Exception $e) {
        echo "<p class='error'>❌ Erreur génération HTML avec commande: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='error'>❌ Aucune commande WooCommerce trouvée avec ID $order_id</p>";
    echo "<p class='info'>Modifiez la variable \$order_id pour tester avec une commande existante</p>";
}

// Test 5: Comparaison des méthodes
echo "<h2>5. Test de cohérence des méthodes</h2>";

try {
    // Tester que generate_html_from_template_data utilise bien generate_unified_html
    $html_old_method = $admin->generate_html_from_template_data($test_template);
    $html_new_method = $admin->generate_unified_html($test_template, null);

    if ($html_old_method === $html_new_method) {
        echo "<p class='success'>✅ generate_html_from_template_data utilise correctement generate_unified_html</p>";
    } else {
        echo "<p class='error'>❌ Incohérence entre generate_html_from_template_data et generate_unified_html</p>";
    }

    if ($order) {
        // Tester que generate_order_html utilise bien generate_unified_html
        $html_order_old = $admin->generate_order_html($order, $test_template);
        $html_order_new = $admin->generate_unified_html($test_template, $order);

        if ($html_order_old === $html_order_new) {
            echo "<p class='success'>✅ generate_order_html utilise correctement generate_unified_html</p>";
        } else {
            echo "<p class='error'>❌ Incohérence entre generate_order_html et generate_unified_html</p>";
        }
    }

} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur lors du test de cohérence: " . $e->getMessage() . "</p>";
}

echo "<h2>6. Résumé des tests</h2>";
echo "<p><strong>Si tous les tests ci-dessus sont verts, la synchronisation est réussie !</strong></p>";
echo "<p>Les systèmes d'aperçu sont maintenant cohérents et utilisent la même logique unifiée.</p>";

?>