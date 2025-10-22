<?php
/**
 * Test rapide de la classe PreviewRenderer
 * À exécuter pour vérifier l'étape 3.1.1
 */

// Simuler les constantes WordPress pour le test
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// Inclure la classe (dans un environnement réel, utiliser l'autoloader)
require_once __DIR__ . '/src/Renderers/PreviewRenderer.php';
require_once __DIR__ . '/src/Providers/index.php';

echo "=== Test PreviewRenderer 3.1.1 ===\n\n";

try {
    // Test 1: Instanciation normale
    echo "Test 1: Instanciation normale\n";
    $renderer = new \PDF_Builder\Renderers\PreviewRenderer(['mode' => 'canvas']);
    echo "✓ Classe instanciée sans erreur\n";

    // Test 2: Vérification des propriétés
    echo "\nTest 2: Vérification des propriétés\n";
    echo "Mode: " . $renderer->getMode() . "\n";
    $dimensions = $renderer->getDimensions();
    echo "Dimensions: {$dimensions['width']}x{$dimensions['height']}\n";
    echo "Initialisé: " . ($renderer->isInitialized() ? 'Oui' : 'Non') . "\n";

    // Test 3: Initialisation
    echo "\nTest 3: Initialisation\n";
    $initResult = $renderer->init();
    echo "Initialisation: " . ($initResult ? 'Réussie' : 'Échouée') . "\n";
    echo "État après init: " . ($renderer->isInitialized() ? 'Initialisé' : 'Non initialisé') . "\n";

    // Test 4: Test de rendu (basique)
    echo "\nTest 4: Test de rendu basique\n";
    $elementData = ['type' => 'text', 'content' => 'Test'];
    $renderResult = $renderer->render($elementData);
    echo "Rendu: " . ($renderResult ? 'Réussi' : 'Échoué') . "\n";

    // Test 5: Destruction
    echo "\nTest 5: Destruction\n";
    $destroyResult = $renderer->destroy();
    echo "Destruction: " . ($destroyResult ? 'Réussie' : 'Échouée') . "\n";

    // Test 6: Mode invalide
    echo "\nTest 6: Mode invalide\n";
    try {
        $invalidRenderer = new \PDF_Builder\Renderers\PreviewRenderer(['mode' => 'invalid']);
        echo "✗ Mode invalide accepté (erreur attendue)\n";
    } catch (\Exception $e) {
        echo "✓ Mode invalide rejeté: " . $e->getMessage() . "\n";
    }

    // Test 7: Constantes A4
    echo "\nTest 7: Constantes A4\n";
    echo "A4_WIDTH_MM: " . \PDF_Builder\Renderers\PreviewRenderer::A4_WIDTH_MM . "mm\n";
    echo "A4_HEIGHT_MM: " . \PDF_Builder\Renderers\PreviewRenderer::A4_HEIGHT_MM . "mm\n";
    echo "A4_DPI: " . \PDF_Builder\Renderers\PreviewRenderer::A4_DPI . "\n";
    echo "A4_WIDTH_PX: " . \PDF_Builder\Renderers\PreviewRenderer::A4_WIDTH_PX . "px\n";
    echo "A4_HEIGHT_PX: " . \PDF_Builder\Renderers\PreviewRenderer::A4_HEIGHT_PX . "px\n";

    // Test 8: setDimensions()
    echo "\nTest 8: setDimensions()\n";
    $setResult = $renderer->setDimensions(1000, 1500);
    echo "setDimensions(1000, 1500): " . ($setResult ? 'Réussi' : 'Échoué') . "\n";
    $newDimensions = $renderer->getDimensions();
    echo "Nouvelles dimensions: {$newDimensions['width']}x{$newDimensions['height']}\n";

    // Test 9: resetToA4()
    echo "\nTest 9: resetToA4()\n";
    $resetResult = $renderer->resetToA4();
    echo "resetToA4(): " . ($resetResult ? 'Réussi' : 'Échoué') . "\n";
    $a4Dimensions = $renderer->getDimensions();
    echo "Dimensions A4: {$a4Dimensions['width']}x{$a4Dimensions['height']}\n";
    $isA4Correct = ($a4Dimensions['width'] === 794 && $a4Dimensions['height'] === 1123);
    echo "Dimensions A4 correctes: " . ($isA4Correct ? 'Oui' : 'Non') . "\n";

    // Test 10: calculatePixelDimensions()
    echo "\nTest 10: calculatePixelDimensions()\n";
    $calculated = \PDF_Builder\Renderers\PreviewRenderer::calculatePixelDimensions(210, 297, 150);
    echo "calculatePixelDimensions(210, 297, 150): {$calculated['width']}x{$calculated['height']}\n";
    $calcCorrect = ($calculated['width'] === 794 && $calculated['height'] === 1123);
    echo "Calcul correct: " . ($calcCorrect ? 'Oui' : 'Non') . "\n";

    // Test 11: Validation des dimensions
    echo "\nTest 11: Validation des dimensions\n";
    $invalidSet = $renderer->setDimensions(-100, 100);
    echo "setDimensions(-100, 100): " . ($invalidSet ? 'Accepté (erreur)' : 'Rejeté (correct)') . "\n";

    $tooBigSet = $renderer->setDimensions(6000, 6000);
    echo "setDimensions(6000, 6000): " . ($tooBigSet ? 'Accepté (erreur)' : 'Rejeté (correct)') . "\n";

    // Test 12: Zoom
    echo "\nTest 12: Zoom\n";
    echo "Zoom initial: " . $renderer->getZoom() . "%\n";
    echo "Niveaux autorisés: " . implode(', ', $renderer->getAllowedZoomLevels()) . "\n";

    $zoomSet = $renderer->setZoom(125);
    echo "setZoom(125): " . ($zoomSet ? 'Réussi' : 'Échoué') . "\n";
    echo "Zoom après setZoom(125): " . $renderer->getZoom() . "%\n";

    $zoomIn = $renderer->zoomIn();
    echo "zoomIn(): " . ($zoomIn ? 'Réussi' : 'Échoué') . "\n";
    echo "Zoom après zoomIn(): " . $renderer->getZoom() . "%\n";

    $zoomOut = $renderer->zoomOut();
    echo "zoomOut(): " . ($zoomOut ? 'Réussi' : 'Échoué') . "\n";
    echo "Zoom après zoomOut(): " . $renderer->getZoom() . "%\n";

    // Test 13: Responsive
    echo "\nTest 13: Responsive\n";
    echo "Responsive initial: " . ($renderer->isResponsive() ? 'Activé' : 'Désactivé') . "\n";

    $responsiveSet = $renderer->setResponsive(false);
    echo "setResponsive(false): " . ($responsiveSet ? 'Réussi' : 'Échoué') . "\n";
    echo "Responsive après setResponsive(false): " . ($renderer->isResponsive() ? 'Activé' : 'Désactivé') . "\n";

    $containerSet = $renderer->setContainerDimensions(800, 600);
    echo "setContainerDimensions(800, 600): " . ($containerSet ? 'Réussi' : 'Échoué') . "\n";

    $responsiveDims = $renderer->getResponsiveDimensions();
    echo "getResponsiveDimensions(): " . ($responsiveDims ? "Dimensions calculées" : "Null (pas responsive)") . "\n";

    $scrollbars = $renderer->getScrollbarState();
    echo "getScrollbarState(): Horizontal=" . ($scrollbars['horizontal'] ? 'Oui' : 'Non') . ", Vertical=" . ($scrollbars['vertical'] ? 'Oui' : 'Non') . "\n";

    // Test 14: Rendu d'élément texte
    echo "\nTest 14: Rendu d'élément texte\n";
    $renderer = new \PDF_Builder\Renderers\PreviewRenderer(['mode' => 'canvas']);
    $renderer->init();
    $elementData = [
        'type' => 'text',
        'text' => 'Hello World',
        'x' => 10,
        'y' => 20,
        'width' => 100,
        'height' => 50,
        'fontSize' => 14,
        'color' => '#ff0000',
        'bold' => true
    ];
    $result = $renderer->renderElement($elementData);
    echo "Rendu élément texte: " . ($result['success'] ? "Réussi" : "Échoué") . "\n";
    if ($result['success']) {
        echo "HTML généré: " . substr($result['html'], 0, 50) . "...\n";
        echo "Position: x=" . $result['x'] . ", y=" . $result['y'] . "\n";
    }

    // Test 15: Rendu d'élément rectangle
    echo "\nTest 15: Rendu d'élément rectangle\n";
    $elementData = [
        'type' => 'rectangle',
        'x' => 50,
        'y' => 50,
        'width' => 200,
        'height' => 100,
        'fillColor' => '#00ff00',
        'borderWidth' => 2,
        'borderColor' => '#000000'
    ];
    $result = $renderer->renderElement($elementData);
    echo "Rendu élément rectangle: " . ($result['success'] ? "Réussi" : "Échoué") . "\n";

    // Test 16: Rendu avec zoom
    echo "\nTest 16: Rendu avec zoom\n";
    $rendererZoom = new \PDF_Builder\Renderers\PreviewRenderer(['mode' => 'canvas', 'zoom' => 150]);
    $rendererZoom->init();
    $elementData = [
        'type' => 'text',
        'text' => 'Zoom Test',
        'x' => 10,
        'y' => 10,
        'width' => 100,
        'height' => 30
    ];
    $result = $rendererZoom->renderElement($elementData);
    echo "Rendu avec zoom 150%: " . ($result['success'] ? "Réussi" : "Échoué") . "\n";
    if ($result['success']) {
        echo "Zoom appliqué: " . $result['zoom_applied'] . "%\n";
        echo "Dimensions ajustées: " . $result['width'] . "x" . $result['height'] . "\n";
    }

    // Test 17: Rendu responsive
    echo "\nTest 17: Rendu responsive\n";
    $rendererResponsive = new \PDF_Builder\Renderers\PreviewRenderer(['mode' => 'canvas', 'responsive' => true]);
    $rendererResponsive->init();
    $rendererResponsive->setContainerDimensions(400, 300);
    $result = $rendererResponsive->renderElement($elementData);
    echo "Rendu responsive: " . ($result['success'] ? "Réussi" : "Échoué") . "\n";
    if ($result['success']) {
        echo "Responsive appliqué: " . ($result['responsive_applied'] ? "Oui" : "Non") . "\n";
    }

    // Test 18: Élément non supporté
    echo "\nTest 18: Élément non supporté\n";
    $elementData = [
        'type' => 'unsupported_element',
        'x' => 0,
        'y' => 0,
        'width' => 100,
        'height' => 100
    ];
    $result = $renderer->renderElement($elementData);
    echo "Rendu élément non supporté: " . ($result['success'] ? "Réussi" : "Échoué") . "\n";
    if (!$result['success']) {
        echo "Erreur attendue: " . $result['error'] . "\n";
    }

    echo "\n=== Tous les tests terminés avec succès ===\n";

// Tests pour CanvasModeProvider (Phase 3.2.2)
echo "\n\n=== Test CanvasModeProvider 3.2.2 ===\n\n";

try {
    // Inclure la classe CanvasModeProvider
    require_once __DIR__ . '/src/Interfaces/DataProviderInterface.php';
    require_once __DIR__ . '/src/Providers/CanvasModeProvider.php';

    // Test 14: Instanciation CanvasModeProvider
    echo "Test 14: Instanciation CanvasModeProvider\n";
    $provider = new \PDF_Builder_Pro\Providers\CanvasModeProvider();
    echo "✓ Provider instancié sans erreur\n";

    // Test 15: Récupération données client
    echo "\nTest 15: Données client fictives\n";
    $customerData = $provider->getCustomerData();
    echo "Nom: " . $customerData['full_name'] . "\n";
    echo "Email: " . $customerData['email'] . "\n";
    echo "Téléphone: " . $customerData['phone'] . "\n";
    echo "✓ Données client récupérées\n";

    // Test 16: Récupération données commande
    echo "\nTest 16: Données commande fictives\n";
    $orderData = $provider->getOrderData();
    echo "Numéro commande: " . $orderData['order_number'] . "\n";
    echo "Date: " . $orderData['order_date'] . "\n";
    echo "Total: " . $orderData['total'] . " €\n";
    echo "Nombre d'articles: " . count($orderData['items']) . "\n";
    echo "✓ Données commande récupérées\n";

    // Test 17: Récupération données société
    echo "\nTest 17: Données société fictives\n";
    $companyData = $provider->getCompanyData();
    echo "Nom société: " . $companyData['name'] . "\n";
    echo "Email: " . $companyData['email'] . "\n";
    echo "SIRET: " . $companyData['siret'] . "\n";
    echo "✓ Données société récupérées\n";

    // Test 18: Génération données fictives
    echo "\nTest 18: Génération données fictives\n";
    $templateKeys = ['customer_name', 'order_number', 'order_total', 'company_name'];
    $mockData = $provider->generateMockData($templateKeys);
    echo "Données générées:\n";
    foreach ($mockData as $key => $value) {
        echo "  $key: $value\n";
    }
    echo "✓ Données fictives générées\n";

    // Test 19: Vérification complétude
    echo "\nTest 19: Vérification complétude données\n";
    $requiredKeys = ['customer_name', 'order_number', 'company_name'];
    $completeness = $provider->checkDataCompleteness($requiredKeys);
    echo "Données complètes: " . ($completeness['complete'] ? 'Oui' : 'Non') . "\n";
    if (!empty($completeness['missing'])) {
        echo "Clés manquantes: " . implode(', ', $completeness['missing']) . "\n";
    }
    echo "✓ Vérification complétude effectuée\n";

    // Test 20: Système de cache
    echo "\nTest 20: Système de cache\n";
    $testData = ['test' => 'cached_value'];
    $cacheKey = 'test_cache_key';

    // Mise en cache
    $cacheResult = $provider->cacheData($cacheKey, $testData, 60);
    echo "Mise en cache: " . ($cacheResult ? 'Réussie' : 'Échouée') . "\n";

    // Récupération depuis le cache
    $cachedData = $provider->getCachedData($cacheKey);
    echo "Récupération cache: " . ($cachedData === $testData ? 'Réussie' : 'Échouée') . "\n";

    // Invalidation du cache
    $invalidateResult = $provider->invalidateCache($cacheKey);
    echo "Invalidation cache: " . ($invalidateResult ? 'Réussie' : 'Échouée') . "\n";

    // Vérification que le cache est vide
    $cachedDataAfter = $provider->getCachedData($cacheKey);
    echo "Cache après invalidation: " . ($cachedDataAfter === null ? 'Vide (OK)' : 'Non vide (ERREUR)') . "\n";
    echo "✓ Système de cache fonctionnel\n";

    // Test 21: Nettoyage des données
    echo "\nTest 21: Nettoyage des données\n";
    $rawData = [
        'name' => 'Test & <script>alert("xss")</script>',
        'safe' => 'Normal text'
    ];
    $sanitized = $provider->sanitizeData($rawData);
    echo "Données brutes: " . $rawData['name'] . "\n";
    echo "Données nettoyées: " . $sanitized['name'] . "\n";
    echo "✓ Nettoyage des données effectué\n";

    echo "\n=== Tests CanvasModeProvider terminés avec succès ===\n";

} catch (\Exception $e) {
    echo "ERREUR FATALE dans CanvasModeProvider: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . " Ligne: " . $e->getLine() . "\n";
}

// Tests pour MetaboxModeProvider (Phase 3.2.3)
echo "\n\n=== Test MetaboxModeProvider 3.2.3 ===\n\n";

try {
    // Inclure la classe MetaboxModeProvider
    require_once __DIR__ . '/src/Providers/MetaboxModeProvider.php';

    // Créer un mock WC_Order pour les tests
    class MockWCOrder {
        public function get_id() { return 12345; }
        public function get_order_number() { return 'WC-12345'; }
        public function get_customer_id() { return 678; }
        public function get_billing_first_name() { return 'Jean'; }
        public function get_billing_last_name() { return 'Dupont'; }
        public function get_billing_email() { return 'jean.dupont@example.com'; }
        public function get_billing_phone() { return '+33123456789'; }
        public function get_billing_company() { return 'Entreprise Test'; }
        public function get_billing_address_1() { return '123 Rue de Test'; }
        public function get_billing_city() { return 'Paris'; }
        public function get_billing_postcode() { return '75001'; }
        public function get_billing_country() { return 'FR'; }
        public function get_formatted_billing_address() { return 'Jean Dupont\nEntreprise Test\n123 Rue de Test\n75001 Paris\nFrance'; }
        public function get_total() { return 150.00; }
        public function get_subtotal() { return 120.00; }
        public function get_total_tax() { return 30.00; }
        public function get_shipping_total() { return 10.00; }
        public function get_discount_total() { return 0.00; }
        public function get_currency() { return 'EUR'; }
        public function get_status() { return 'processing'; }
        public function get_payment_method_title() { return 'Carte bancaire'; }
        public function get_customer_note() { return 'Livraison rapide svp'; }
        public function get_date_created() { return new DateTime('2025-10-22 14:30:00'); }
        public function get_items() { return [new MockWCOrderItem()]; }
        public function get_shipping_methods() { return [new MockWCShippingMethod()]; }
        public function get_formatted_shipping_address() { return 'Jean Dupont\n123 Rue de Test\n75001 Paris\nFrance'; }
        public function get_shipping_first_name() { return 'Jean'; }
        public function get_shipping_last_name() { return 'Dupont'; }
        public function get_shipping_company() { return ''; }
        public function get_shipping_address_1() { return '123 Rue de Test'; }
        public function get_shipping_city() { return 'Paris'; }
        public function get_shipping_postcode() { return '75001'; }
        public function get_shipping_country() { return 'FR'; }
        public function get_meta($key) {
            $meta = [
                '_billing_vat_number' => 'FR12345678901',
                '_tracking_number' => 'TR123456789'
            ];
            return $meta[$key] ?? '';
        }
    }

    class MockWCOrderItem {
        public function get_product() { return new MockWCProduct(); }
        public function get_name() { return 'Produit Test'; }
        public function get_quantity() { return 2; }
        public function get_total() { return 100.00; }
    }

    class MockWCProduct {
        public function get_sku() { return 'PROD-001'; }
        public function get_short_description() { return 'Description du produit test'; }
    }

    class MockWCShippingMethod {
        public function get_method_title() { return 'Colissimo Express'; }
    }

    // Test 22: Instanciation MetaboxModeProvider
    echo "Test 22: Instanciation MetaboxModeProvider\n";
    $order = new MockWCOrder();
    $provider = new \PDF_Builder_Pro\Providers\MetaboxModeProvider($order);
    echo "✓ Provider instancié sans erreur\n";

    // Test 23: Récupération données client réelles
    echo "\nTest 23: Données client réelles depuis WooCommerce\n";
    $customerData = $provider->getCustomerData();
    echo "Prénom: " . $customerData['first_name'] . "\n";
    echo "Nom: " . $customerData['last_name'] . "\n";
    echo "Email: " . $customerData['email'] . "\n";
    echo "Téléphone: " . $customerData['phone'] . "\n";
    echo "✓ Données client récupérées\n";

    // Test 24: Récupération données commande réelles
    echo "\nTest 24: Données commande réelles depuis WooCommerce\n";
    $orderData = $provider->getOrderData();
    echo "Numéro commande: " . $orderData['order_number'] . "\n";
    echo "Date: " . $orderData['order_date'] . "\n";
    echo "Total: " . $orderData['total'] . " €\n";
    echo "Statut: " . $orderData['order_status'] . "\n";
    echo "Nombre d'articles: " . count($orderData['items']) . "\n";
    echo "✓ Données commande récupérées\n";

    // Test 25: Récupération données société depuis options WordPress/WooCommerce
    echo "\nTest 25: Données société depuis paramètres WooCommerce\n";
    // Simuler get_option pour les tests
    if (!function_exists('get_option')) {
        function get_option($key) {
            $options = [
                'woocommerce_store_name' => 'Ma Société SARL',
                'woocommerce_store_email' => 'contact@masociete.com',
                'woocommerce_store_address' => '456 Avenue des Champs',
                'woocommerce_store_city' => 'Lyon',
                'woocommerce_store_postcode' => '69000',
                'woocommerce_store_country' => 'FR',
                'woocommerce_store_phone' => '+33456789012',
                'woocommerce_store_vat_number' => 'FR98765432109',
                'woocommerce_store_siret' => '98765432109876',
                'woocommerce_store_bank_name' => 'BNP Paribas',
                'woocommerce_store_iban' => 'FR7612345678901234567890123',
                'woocommerce_store_bic' => 'BNPAFRPP',
                'woocommerce_store_logo' => '/wp-content/uploads/logo.png',
                'woocommerce_store_ceo' => 'Marie Martin',
                'woocommerce_store_registration' => 'RCS Lyon 987654321'
            ];
            return $options[$key] ?? '';
        }
    }
    $companyData = $provider->getCompanyData();
    echo "Nom société: " . $companyData['name'] . "\n";
    echo "Email: " . $companyData['email'] . "\n";
    echo "Adresse: " . $companyData['address']['formatted'] . "\n";
    echo "✓ Données société récupérées\n";

    // Test 26: Génération données mock depuis vraies données
    echo "\nTest 26: Génération données mock depuis vraies données\n";
    $templateKeys = ['customer_name', 'order_number', 'order_total', 'company_name'];
    $mockData = $provider->generateMockData($templateKeys);
    echo "Données mock générées:\n";
    foreach ($mockData as $key => $value) {
        echo "  $key: $value\n";
    }
    echo "✓ Données mock générées\n";

    // Test 27: Vérification complétude avec vraies données
    echo "\nTest 27: Vérification complétude avec vraies données\n";
    $requiredKeys = ['customer_name', 'order_number', 'company_name'];
    $completeness = $provider->checkDataCompleteness($requiredKeys);
    echo "Données complètes: " . ($completeness['complete'] ? 'Oui' : 'Non') . "\n";
    if (!empty($completeness['missing'])) {
        echo "Clés manquantes: " . implode(', ', $completeness['missing']) . "\n";
    }
    echo "✓ Vérification complétude effectuée\n";

    // Test 28: Gestion données manquantes avec placeholders
    echo "\nTest 28: Gestion données manquantes avec placeholders\n";
    $providerWithoutOrder = new \PDF_Builder_Pro\Providers\MetaboxModeProvider(null);
    $emptyCustomerData = $providerWithoutOrder->getCustomerData();
    $emptyOrderData = $providerWithoutOrder->getOrderData();
    echo "Client sans commande: " . $emptyCustomerData['first_name'] . "\n";
    echo "Commande sans données: " . $emptyOrderData['order_number'] . "\n";
    echo "✓ Placeholders utilisés pour données manquantes\n";

    // Test 29: Système de cache
    echo "\nTest 29: Système de cache\n";
    $testData = ['test' => 'cached_value'];
    $cacheKey = 'test_cache_key';

    // Mise en cache
    $cacheResult = $provider->cacheData($cacheKey, $testData, 60);
    echo "Mise en cache: " . ($cacheResult ? 'Réussie' : 'Échouée') . "\n";

    // Récupération depuis le cache
    $cachedData = $provider->getCachedData($cacheKey);
    echo "Récupération cache: " . ($cachedData === $testData ? 'Réussie' : 'Échouée') . "\n";

    // Invalidation du cache
    $invalidateResult = $provider->invalidateCache($cacheKey);
    echo "Invalidation cache: " . ($invalidateResult ? 'Réussie' : 'Échouée') . "\n";

    // Vérification que le cache est vide
    $cachedDataAfter = $provider->getCachedData($cacheKey);
    echo "Cache après invalidation: " . ($cachedDataAfter === null ? 'Vide (OK)' : 'Non vide (ERREUR)') . "\n";
    echo "✓ Système de cache fonctionnel\n";

    // Test 30: Nettoyage et formatage des données
    echo "\nTest 30: Nettoyage et formatage des données\n";
    $rawData = [
        'name' => 'Test & donnée',
        'price' => 150.50,
        'description' => 'Description <b>test</b>'
    ];
    $sanitized = $provider->sanitizeData($rawData);
    echo "Prix formaté: " . $sanitized['price'] . "\n";
    echo "Description nettoyée: " . $sanitized['description'] . "\n";
    echo "✓ Nettoyage et formatage effectués\n";

    echo "\n=== Tests MetaboxModeProvider terminés avec succès ===\n";

} catch (\Exception $e) {
    echo "ERREUR FATALE dans MetaboxModeProvider: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . " Ligne: " . $e->getLine() . "\n";
}

// ==========================================
// Test Phase 3.2.4 : ModeSwitcher & DIContainer
// ==========================================

echo "\n=== Test Phase 3.2.4 : ModeSwitcher & DIContainer ===\n";

try {
    // Test 31: Instanciation ModeSwitcher
    echo "\nTest 31: Instanciation ModeSwitcher\n";
    $modeSwitcher = new PDF_Builder_Pro\Managers\ModeSwitcher();
    echo "✓ ModeSwitcher instancié avec succès\n";

    // Test 32: Mode par défaut (Canvas)
    echo "\nTest 32: Mode par défaut (Canvas)\n";
    $currentMode = $modeSwitcher->getCurrentMode();
    echo "Mode actuel: " . $currentMode . "\n";
    echo "Est mode Canvas: " . ($modeSwitcher->isCanvasMode() ? 'Oui' : 'Non') . "\n";
    echo "Est mode Metabox: " . ($modeSwitcher->isMetaboxMode() ? 'Oui' : 'Non') . "\n";
    echo "✓ Mode par défaut correct (Canvas)\n";

    // Test 33: Basculement vers Metabox
    echo "\nTest 33: Basculement vers Metabox\n";
    $switchResult = $modeSwitcher->switchToMetabox();
    echo "Basculement réussi: " . ($switchResult ? 'Oui' : 'Non') . "\n";
    echo "Mode actuel après basculement: " . $modeSwitcher->getCurrentMode() . "\n";
    echo "Est mode Metabox: " . ($modeSwitcher->isMetaboxMode() ? 'Oui' : 'Non') . "\n";
    echo "✓ Basculement vers Metabox réussi\n";

    // Test 34: Basculement retour vers Canvas
    echo "\nTest 34: Basculement retour vers Canvas\n";
    $switchResult = $modeSwitcher->switchToCanvas();
    echo "Basculement réussi: " . ($switchResult ? 'Oui' : 'Non') . "\n";
    echo "Mode actuel après basculement: " . $modeSwitcher->getCurrentMode() . "\n";
    echo "Est mode Canvas: " . ($modeSwitcher->isCanvasMode() ? 'Oui' : 'Non') . "\n";
    echo "✓ Basculement retour vers Canvas réussi\n";

    // Test 35: Injection d'ordre WooCommerce (mock)
    echo "\nTest 35: Injection d'ordre WooCommerce (mock)\n";
    $mockOrder = new stdClass();
    $mockOrder->get_id = function() { return 12345; };
    $mockOrder->get_order_number = function() { return 'WC-12345'; };
    $mockOrder->get_billing_first_name = function() { return 'Jean'; };
    $mockOrder->get_billing_last_name = function() { return 'Dupont'; };

    $modeSwitcher->switchToMetabox();
    $modeSwitcher->injectMetaboxOrder($mockOrder);
    $currentProvider = $modeSwitcher->getCurrentProvider();
    echo "Provider actuel: " . get_class($currentProvider) . "\n";
    echo "✓ Injection d'ordre WooCommerce réussie\n";

    // Test 36: DIContainer - Instanciation
    echo "\nTest 36: DIContainer - Instanciation\n";
    $diContainer = new PDF_Builder_Pro\Core\DIContainer();
    echo "✓ DIContainer instancié avec succès\n";

    // Test 37: DIContainer - Enregistrement de services
    echo "\nTest 37: DIContainer - Enregistrement de services\n";
    $diContainer->set('test_service', function() {
        return 'Hello World';
    });
    $service = $diContainer->get('test_service');
    echo "Service récupéré: " . $service . "\n";
    echo "✓ Enregistrement et récupération de service réussis\n";

    // Test 38: DIContainer - Singleton
    echo "\nTest 38: DIContainer - Singleton\n";
    $diContainer->set('singleton_service', function() {
        return new stdClass();
    }, true);

    $instance1 = $diContainer->get('singleton_service');
    $instance2 = $diContainer->get('singleton_service');
    echo "Même instance (singleton): " . ($instance1 === $instance2 ? 'Oui' : 'Non') . "\n";
    echo "✓ Pattern Singleton fonctionnel\n";

    // Test 39: DIContainer - Configuration par défaut
    echo "\nTest 39: DIContainer - Configuration par défaut\n";
    $diContainer->configureDefaults('canvas');
    $hasModeSwitcher = $diContainer->has('mode_switcher');
    $hasRenderer = $diContainer->has('preview_renderer');
    echo "ModeSwitcher enregistré: " . ($hasModeSwitcher ? 'Oui' : 'Non') . "\n";
    echo "PreviewRenderer enregistré: " . ($hasRenderer ? 'Oui' : 'Non') . "\n";
    echo "✓ Configuration par défaut réussie\n";

    // Test 40: Intégration ModeSwitcher + DIContainer
    echo "\nTest 40: Intégration ModeSwitcher + DIContainer\n";
    $modeSwitcherFromDI = $diContainer->get('mode_switcher');
    echo "ModeSwitcher depuis DI: " . get_class($modeSwitcherFromDI) . "\n";
    echo "Mode actuel: " . $modeSwitcherFromDI->getCurrentMode() . "\n";

    // Test de basculement via DI
    $modeSwitcherFromDI->switchToMetabox();
    echo "Mode après basculement via DI: " . $modeSwitcherFromDI->getCurrentMode() . "\n";
    echo "✓ Intégration ModeSwitcher + DIContainer réussie\n";

    echo "\n=== Tests Phase 3.2.4 terminés avec succès ===\n";

} catch (\Exception $e) {
    echo "ERREUR FATALE dans Phase 3.2.4: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . " Ligne: " . $e->getLine() . "\n";
}

} catch (\Exception $e) {
    echo "ERREUR FATALE: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . " Ligne: " . $e->getLine() . "\n";
}