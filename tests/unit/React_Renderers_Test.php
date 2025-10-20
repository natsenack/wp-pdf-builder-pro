<?php
/**
 * Tests unitaires pour les renderers JavaScript/React
 * Tests de base pour vérifier la structure et les fonctionnalités
 */

class React_Renderers_Test {

    private $results = [];

    private function assert($condition, $message = '') {
        if ($condition) {
            $this->results[] = "✅ PASS: $message";
            return true;
        } else {
            $this->results[] = "❌ FAIL: $message";
            return false;
        }
    }

    private function run_test($test_name, $callback) {
        echo "\nExécution de $test_name...\n";
        try {
            $result = $callback();
            return $result;
        } catch (Exception $e) {
            $this->results[] = "❌ ERROR in $test_name: " . $e->getMessage();
            return false;
        }
    }

    public function test_renderer_structure() {
        return $this->run_test('test_renderer_structure', function() {
            // Vérifier que les fichiers de renderers existent
            $renderer_files = [
                'resources/js/components/preview-system/renderers/TextRenderer.jsx',
                'resources/js/components/preview-system/renderers/DynamicTextRenderer.jsx',
                'resources/js/components/preview-system/renderers/TableRenderer.jsx',
                'resources/js/components/preview-system/renderers/ImageRenderer.jsx'
            ];

            $all_exist = true;
            foreach ($renderer_files as $file) {
                $full_path = __DIR__ . '/../../' . $file;
                if (!file_exists($full_path)) {
                    $this->results[] = "❌ FAIL: Fichier manquant: $file";
                    $all_exist = false;
                }
            }

            if ($all_exist) {
                $this->results[] = "✅ PASS: Tous les fichiers de renderers existent";
            }

            return $all_exist;
        });
    }

    public function test_preview_components_structure() {
        return $this->run_test('test_preview_components_structure', function() {
            // Vérifier que les composants de prévisualisation existent
            $component_files = [
                'resources/js/components/preview-system/PreviewRenderer.jsx',
                'resources/js/components/preview-system/PreviewModal.jsx',
                'resources/js/components/preview-system/modes/CanvasMode.jsx',
                'resources/js/components/preview-system/modes/MetaboxMode.jsx'
            ];

            $all_exist = true;
            foreach ($component_files as $file) {
                $full_path = __DIR__ . '/../../' . $file;
                if (!file_exists($full_path)) {
                    $this->results[] = "❌ FAIL: Composant manquant: $file";
                    $all_exist = false;
                }
            }

            if ($all_exist) {
                $this->results[] = "✅ PASS: Tous les composants de prévisualisation existent";
            }

            return $all_exist;
        });
    }

    public function test_data_providers_structure() {
        return $this->run_test('test_data_providers_structure', function() {
            // Vérifier que les data providers existent
            $provider_files = [
                'resources/js/components/preview-system/data/SampleDataProvider.jsx',
                'resources/js/components/preview-system/data/RealDataProvider.jsx'
            ];

            $all_exist = true;
            foreach ($provider_files as $file) {
                $full_path = __DIR__ . '/../../' . $file;
                if (!file_exists($full_path)) {
                    $this->results[] = "❌ FAIL: Data provider manquant: $file";
                    $all_exist = false;
                }
            }

            if ($all_exist) {
                $this->results[] = "✅ PASS: Tous les data providers existent";
            }

            return $all_exist;
        });
    }

    public function test_element_types_support() {
        return $this->run_test('test_element_types_support', function() {
            // Liste des types d'éléments supportés (théoriques)
            $theoretical_types = [
                'text', 'dynamic-text', 'conditional-text',
                'product_table', 'customer_info', 'company_info',
                'company_logo', 'order_number', 'document_type',
                'mentions', 'image', 'rectangle', 'line',
                'shape-circle', 'shape-triangle', 'shape-star',
                'divider', 'progress-bar', 'barcode', 'qrcode',
                'watermark'
            ];

            // Récupérer les renderers existants
            $renderers_dir = __DIR__ . '/../../resources/js/components/preview-system/renderers/';
            $existing_renderers = [];

            if (is_dir($renderers_dir)) {
                $files = scandir($renderers_dir);
                foreach ($files as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'jsx') {
                        $renderer_name = str_replace('Renderer.jsx', '', $file);
                        $existing_renderers[] = strtolower($renderer_name);
                    }
                }
            }

            // Mapper les renderers existants vers les types qu'ils supportent
            $renderer_mappings = [
                'text' => ['text'],
                'dynamictext' => ['dynamic-text'],
                'table' => ['product_table'],
                'customerinfo' => ['customer_info'],
                'companyinfo' => ['company_info'],
                'ordernumber' => ['order_number'],
                'mentions' => ['mentions'],
                'image' => ['image'],
                'rectangle' => ['rectangle'],
                'progressbar' => ['progress-bar'],
                'barcode' => ['barcode'],
                'watermark' => ['watermark']
            ];

            $supported_types = [];
            $unsupported_types = [];

            foreach ($existing_renderers as $renderer) {
                if (isset($renderer_mappings[$renderer])) {
                    $supported_types = array_merge($supported_types, $renderer_mappings[$renderer]);
                } else {
                    $unsupported_types[] = $renderer;
                }
            }

            // Vérifier que tous les renderers existants sont utilisés
            $unused_renderers = array_diff($existing_renderers, array_keys($renderer_mappings));

            // Calculer les types manquants (théoriques moins supportés)
            $missing_types = array_diff($theoretical_types, $supported_types);

            // Afficher les informations
            $this->results[] = "DEBUG: Renderers existants: " . implode(', ', $existing_renderers);
            $this->results[] = "DEBUG: Types supportés: " . implode(', ', $supported_types);

            if (!empty($missing_types)) {
                $this->results[] = "INFO: Types théoriques sans renderer: " . implode(', ', $missing_types);
            }

            if (!empty($unused_renderers)) {
                $this->results[] = "WARNING: Renderers sans mapping: " . implode(', ', $unused_renderers);
                return false;
            }

            $this->results[] = "✅ PASS: Tous les renderers existants sont mappés à des types";
            return true;
        });
    }

    public function test_build_configuration() {
        return $this->run_test('test_build_configuration', function() {
            // Vérifier que la configuration de build existe
            $build_files = [
                'config/build/webpack.config.js',
                'package.json'
            ];

            $all_exist = true;
            foreach ($build_files as $file) {
                $full_path = __DIR__ . '/../../' . $file;
                if (!file_exists($full_path)) {
                    $this->results[] = "❌ FAIL: Fichier de build manquant: $file";
                    $all_exist = false;
                }
            }

            if ($all_exist) {
                $this->results[] = "✅ PASS: Configuration de build complète";
            }

            return $all_exist;
        });
    }

    public function run_all_tests() {
        echo "🧪 TESTS RENDERERS REACT\n";
        echo "========================\n";

        $tests = [
            'test_renderer_structure',
            'test_preview_components_structure',
            'test_data_providers_structure',
            'test_element_types_support',
            'test_build_configuration'
        ];

        $passed = 0;
        $total = count($tests);

        foreach ($tests as $test) {
            if ($this->{$test}()) {
                $passed++;
            }
        }

        // Afficher tous les résultats détaillés
        echo "\nDÉTAILS DES RÉSULTATS:\n";
        echo str_repeat("-", 40) . "\n";
        foreach ($this->results as $result) {
            echo $result . "\n";
        }

        echo "\n" . str_repeat("=", 40) . "\n";
        echo "RÉSULTATS: $passed/$total tests réussis\n";

        if ($passed === $total) {
            echo "🎉 TOUS LES TESTS REACT RÉUSSIS !\n";
        } else {
            echo "⚠️ Certains tests ont échoué\n";
        }

        return $passed === $total;
    }
}

// Exécuter les tests
$test = new React_Renderers_Test();
$test->run_all_tests();