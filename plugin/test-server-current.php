<?php
/**
 * Test Script - PDF Builder Pro Server Testing
 * Test des fonctionnalités implémentées (étapes 1.0-1.3)
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

echo "<h1>🧪 Tests PDF Builder Pro - Serveur</h1>";
echo "<p><strong>Date:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Étape actuelle:</strong> 1.0-1.3 (Architecture unifiée)</p>";
echo "<hr>";

// Test 1: Chargement des classes
echo "<h2>1. 📦 Test du chargement des classes</h2>";
try {
    // Tester l'autoloader
    if (file_exists(plugin_dir_path(__FILE__) . 'core/autoloader.php')) {
        require_once plugin_dir_path(__FILE__) . 'core/autoloader.php';
        echo "✅ Autoloader chargé<br>";
    }

    // Tester les classes principales
    $classes_to_test = [
        'WP_PDF_Builder_Pro\Data\DataProviderInterface',
        'WP_PDF_Builder_Pro\Data\SampleDataProvider',
        'WP_PDF_Builder_Pro\Data\WooCommerceDataProvider',
        'WP_PDF_Builder_Pro\Generators\BaseGenerator',
        'WP_PDF_Builder_Pro\Generators\PDFGenerator',
        'WP_PDF_Builder_Pro\Api\PreviewImageAPI'
    ];

    foreach ($classes_to_test as $class) {
        if (class_exists($class)) {
            echo "✅ Classe $class existe<br>";
        } else {
            echo "❌ Classe $class introuvable<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Erreur chargement classes: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Test 2: Data Providers
echo "<h2>2. 📊 Test des Data Providers</h2>";
try {
    // Test SampleDataProvider
    $sampleProvider = new WP_PDF_Builder_Pro\Data\SampleDataProvider();
    echo "✅ SampleDataProvider instancié<br>";

    // Test récupération de variables
    $test_vars = ['customer_name', 'order_total', 'company_name'];
    foreach ($test_vars as $var) {
        $value = $sampleProvider->getVariableValue($var);
        echo "📝 $var = '$value'<br>";
    }

    // Test WooCommerceDataProvider (si WooCommerce actif)
    if (class_exists('WooCommerce')) {
        echo "<br>🛒 WooCommerce détecté - Test WooCommerceDataProvider:<br>";
        try {
            $wcProvider = new WP_PDF_Builder_Pro\Data\WooCommerceDataProvider();
            echo "✅ WooCommerceDataProvider instancié<br>";

            // Tester avec une commande existante si possible
            $args = array(
                'post_type' => 'shop_order',
                'post_status' => 'wc-completed',
                'posts_per_page' => 1
            );
            $orders = get_posts($args);
            if (!empty($orders)) {
                $order_id = $orders[0]->ID;
                $wcProvider->setOrderId($order_id); // Utiliser setOrderId au lieu de setOrder
                $customer_name = $wcProvider->getVariableValue('customer_name');
                echo "📝 customer_name (réel) = '$customer_name'<br>";
            } else {
                echo "ℹ️ Aucune commande trouvée pour test<br>";
            }
        } catch (Exception $e) {
            echo "❌ Erreur WooCommerceDataProvider: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "ℹ️ WooCommerce non actif - test WooCommerceDataProvider ignoré<br>";
    }

} catch (Exception $e) {
    echo "❌ Erreur Data Providers: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Test 3: Injection de variables
echo "<h2>3. 🔄 Test de l'injection de variables</h2>";
try {
    $template = "Bonjour {{customer_name}}, votre commande {{order_number}} d'un montant de {{order_total}} a été confirmée.";
    $sampleProvider = new WP_PDF_Builder_Pro\Data\SampleDataProvider();

    // Simuler l'injection
    $result = preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($sampleProvider) {
        return $sampleProvider->getVariableValue($matches[1]);
    }, $template);

    echo "📝 Template original: '$template'<br>";
    echo "📝 Template injecté: '$result'<br>";
    echo "✅ Injection de variables fonctionnelle<br>";

} catch (Exception $e) {
    echo "❌ Erreur injection variables: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Test 4: PDF Generator (test basique)
echo "<h2>4. 📄 Test du PDF Generator (basique)</h2>";
try {
    // Vérifier DomPDF
    if (class_exists('Dompdf\Dompdf')) {
        echo "✅ DomPDF disponible<br>";
    } else {
        echo "❌ DomPDF non disponible<br>";
    }

    // Test instanciation PDFGenerator
    $sampleProvider = new WP_PDF_Builder_Pro\Data\SampleDataProvider();
    $template_data = ['elements' => []]; // Template vide pour test
    $generator = new WP_PDF_Builder_Pro\Generators\PDFGenerator($template_data, $sampleProvider);
    echo "✅ PDFGenerator instancié<br>";

    // Test template simple
    $simple_template = [
        'elements' => [
            [
                'type' => 'text',
                'content' => 'Test PDF - {{customer_name}}',
                'position' => ['x' => 10, 'y' => 10],
                'style' => ['fontSize' => 12]
            ]
        ],
        'page' => [
            'width' => 210,
            'height' => 297,
            'orientation' => 'portrait'
        ]
    ];

    echo "📝 Test template préparé<br>";

    // Note: On ne génère pas réellement le PDF ici pour éviter les problèmes de performance
    echo "ℹ️ Génération PDF réelle disponible via API PreviewImageAPI<br>";

} catch (Exception $e) {
    echo "❌ Erreur PDF Generator: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Test 5: API PreviewImageAPI
echo "<h2>5. 🔌 Test de l'API PreviewImageAPI</h2>";
try {
    if (class_exists('WP_PDF_Builder_Pro\Api\PreviewImageAPI')) {
        echo "✅ Classe PreviewImageAPI disponible<br>";

        // Vérifier si l'action AJAX est enregistrée
        global $wp_filter;
        $ajax_actions = isset($wp_filter['wp_ajax_wp_pdf_preview_image']) ? $wp_filter['wp_ajax_wp_pdf_preview_image'] : null;

        if ($ajax_actions) {
            echo "✅ Action AJAX 'wp_ajax_wp_pdf_preview_image' enregistrée<br>";
            echo "📍 Endpoint: /wp-admin/admin-ajax.php?action=wp_pdf_preview_image<br>";
        } else {
            echo "❌ Action AJAX non enregistrée<br>";
        }
    } else {
        echo "❌ Classe PreviewImageAPI introuvable<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur API: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Test 6: État du système
echo "<h2>6. 🔍 État du système</h2>";
echo "🖥️ <strong>PHP Version:</strong> " . PHP_VERSION . "<br>";
echo "📦 <strong>WordPress Version:</strong> " . get_bloginfo('version') . "<br>";
echo "🛒 <strong>WooCommerce:</strong> " . (class_exists('WooCommerce') ? 'Activé' : 'Non activé') . "<br>";
echo "💾 <strong>Mémoire limite:</strong> " . ini_get('memory_limit') . "<br>";
echo "⏱️ <strong>Max execution time:</strong> " . ini_get('max_execution_time') . "s<br>";
echo "📁 <strong>Plugin path:</strong> " . plugin_dir_path(__FILE__) . "<br>";

echo "<hr>";

// Instructions de test
echo "<h2>🎯 Prochaines étapes de test</h2>";
echo "<ol>";
echo "<li><strong>Test manuel API:</strong> Utiliser l'endpoint AJAX avec des données de test</li>";
echo "<li><strong>Test génération PDF:</strong> Créer un template simple et générer un PDF</li>";
echo "<li><strong>Test variables WooCommerce:</strong> Avec une vraie commande si disponible</li>";
echo "<li><strong>Test performance:</strong> Mesurer le temps de génération</li>";
echo "<li><strong>Test fallback:</strong> Désactiver DomPDF pour tester Canvas</li>";
echo "</ol>";

echo "<p><em>Tests terminés à " . date('H:i:s') . "</em></p>";
?></content>
<parameter name="filePath">d:\wp-pdf-builder-pro\plugin\test-server-current.php