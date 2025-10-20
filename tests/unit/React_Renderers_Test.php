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
                'src/js/components/preview/renderers/TextRenderer.jsx',
                'src/js/components/preview/renderers/DynamicTextRenderer.jsx',
                'src/js/components/preview/renderers/TableRenderer.jsx',
                'src/js/components/preview/renderers/ImageRenderer.jsx'
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
                'src/js/components/preview/PreviewRenderer.jsx',
                'src/js/components/preview/PreviewModal.jsx',
                'src/js/components/preview/modes/CanvasMode.jsx',
                'src/js/components/preview/modes/MetaboxMode.jsx'
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
                'src/js/components/preview/data/SampleDataProvider.jsx',
                'src/js/components/preview/data/RealDataProvider.jsx'
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
            // Liste des types d'éléments supportés
            $supported_types = [
                'text', 'dynamic-text', 'conditional-text',
                'product_table', 'customer_info', 'company_info',
                'company_logo', 'order_number', 'document_type',
                'mentions', 'image', 'rectangle', 'line',
                'shape-circle', 'shape-triangle', 'shape-star',
                'divider', 'progress-bar', 'barcode', 'qrcode',
                'watermark'
            ];

            // Vérifier qu'il y a au moins un renderer pour chaque type
            $renderers_dir = __DIR__ . '/../../src/js/components/preview/renderers/';
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

            $missing_renderers = [];
            foreach ($supported_types as $type) {
                $expected_renderer = $type . 'renderer';
                if (!in_array(strtolower($expected_renderer), array_map('strtolower', $existing_renderers))) {
                    // Vérifier les noms alternatifs
                    $alternatives = [
                        'text' => ['textrenderer'],
                        'dynamic-text' => ['dynamictextrenderer'],
                        'product_table' => ['tablerenderer'],
                        'customer_info' => ['customerinforenderer'],
                        'company_info' => ['companyinforenderer'],
                        'order_number' => ['ordernumberrenderer'],
                        'document_type' => ['documenttyperenderer'],
                        'company_logo' => ['companylogorenderer'],
                        'image' => ['imagerenderer'],
                        'rectangle' => ['rectanglerenderer'],
                        'line' => ['linerenderer'],
                        'shape-circle' => ['shaperenderer'],
                        'shape-triangle' => ['shaperenderer'],
                        'shape-star' => ['shaperenderer'],
                        'divider' => ['dividerenderer'],
                        'progress-bar' => ['progressbarrenderer'],
                        'barcode' => ['barcoderenderer'],
                        'qrcode' => ['qrcoderenderer'],
                        'watermark' => ['watermarkrenderer'],
                        'conditional-text' => ['conditionaltextrenderer'],
                        'mentions' => ['mentionsrenderer']
                    ];

                    $found = false;
                    if (isset($alternatives[$type])) {
                        foreach ($alternatives[$type] as $alt) {
                            if (in_array(strtolower($alt), array_map('strtolower', $existing_renderers))) {
                                $found = true;
                                break;
                            }
                        }
                    }

                    if (!$found) {
                        $missing_renderers[] = $type;
                    }
                }
            }

            if (empty($missing_renderers)) {
                $this->results[] = "✅ PASS: Tous les types d'éléments ont un renderer correspondant";
                return true;
            } else {
                $this->results[] = "❌ FAIL: Renderers manquants pour: " . implode(', ', $missing_renderers);
                return false;
            }
        });
    }

    public function test_build_configuration() {
        return $this->run_test('test_build_configuration', function() {
            // Vérifier que la configuration de build existe
            $build_files = [
                'webpack.config.js',
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