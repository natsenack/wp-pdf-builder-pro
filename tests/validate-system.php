<?php
/**
 * Script de validation pré-test PDF Builder Pro
 *
 * Vérifie l'état du système avant exécution des tests
 *
 * @package PDF_Builder_Pro
 * @version 1.0
 * @since 5.6
 */

class PDF_Builder_Test_Validator {

    private $errors = [];
    private $warnings = [];
    private $checks = 0;
    private $passed = 0;

    private function check($condition, $message, $error = true) {
        $this->checks++;
        if ($condition) {
            $this->passed++;
            echo "✅ $message\n";
            return true;
        } else {
            if ($error) {
                $this->errors[] = $message;
                echo "❌ $message\n";
            } else {
                $this->warnings[] = $message;
                echo "⚠️  $message\n";
            }
            return false;
        }
    }

    private function section($title) {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "🔍 $title\n";
        echo str_repeat("=", 60) . "\n";
    }

    public function validate_file_structure() {
        $this->section("VALIDATION STRUCTURE FICHIERS");

        // Vérifier les fichiers critiques
        $critical_files = [
            'src/Managers/PDF_Builder_Variable_Mapper.php',
            'tests/unit/PDF_Builder_Variable_Mapper_Standalone_Test.php',
            'bootstrap.php',
            'composer.json'
        ];

        foreach ($critical_files as $file) {
            $this->check(file_exists($file), "Fichier $file existe");
        }

        // Vérifier les répertoires
        $directories = [
            'src',
            'tests',
            'tests/unit',
            'assets',
            'lib'
        ];

        foreach ($directories as $dir) {
            $this->check(is_dir($dir), "Répertoire $dir existe");
        }
    }

    public function validate_php_syntax() {
        $this->section("VALIDATION SYNTAXE PHP");

        $php_files = [
            'src/Managers/PDF_Builder_Variable_Mapper.php',
            'tests/unit/PDF_Builder_Variable_Mapper_Standalone_Test.php',
            'bootstrap.php'
        ];

        foreach ($php_files as $file) {
            if (file_exists($file)) {
                $output = shell_exec("php -l \"$file\" 2>&1");
                $syntax_ok = strpos($output, 'No syntax errors detected') !== false;
                $this->check($syntax_ok, "Syntaxe PHP correcte pour $file");
            }
        }
    }

    public function validate_dependencies() {
        $this->section("VALIDATION DÉPENDANCES");

        // Vérifier PHP version
        $php_version = PHP_VERSION;
        $version_ok = version_compare($php_version, '7.4', '>=');
        $this->check($version_ok, "Version PHP >= 7.4 (actuelle: $php_version)", false);

        // Vérifier extensions PHP
        $required_extensions = ['json'];
        $optional_extensions = ['mbstring'];

        foreach ($required_extensions as $ext) {
            $this->check(extension_loaded($ext), "Extension PHP $ext chargée");
        }

        foreach ($optional_extensions as $ext) {
            $this->check(extension_loaded($ext), "Extension PHP $ext chargée (optionnelle)", false);
        }

        // Vérifier TCPDF
        $this->check(file_exists('lib/tcpdf/tcpdf.php'), "Bibliothèque TCPDF disponible");
    }

    public function validate_test_environment() {
        $this->section("VALIDATION ENVIRONNEMENT DE TEST");

        // Inclure le fichier de test pour charger les mocks
        $test_file = 'tests/unit/PDF_Builder_Variable_Mapper_Standalone_Test.php';
        if (file_exists($test_file)) {
            // Ne pas exécuter le test, juste charger les définitions
            $content = file_get_contents($test_file);

            // Vérifier que les mocks sont définis dans le fichier
            $mock_functions = [
                'get_option',
                'date_i18n',
                'wc_price',
                'wc_get_order_statuses'
            ];

            foreach ($mock_functions as $func) {
                $has_mock = strpos($content, "function $func(") !== false;
                $this->check($has_mock, "Mock $func défini dans le fichier de test");
            }

            // Vérifier classes mock
            $mock_classes = [
                'MockWCOrder',
                'MockOrderItem',
                'MockProduct'
            ];

            foreach ($mock_classes as $class) {
                $has_class = strpos($content, "class $class") !== false;
                $this->check($has_class, "Classe mock $class définie dans le test");
            }
        } else {
            $this->check(false, "Fichier de test introuvable");
        }
    }

    public function validate_variable_mapper() {
        $this->section("VALIDATION VARIABLE MAPPER");

        // Inclure les mocks nécessaires
        if (!defined('ABSPATH')) {
            define('ABSPATH', dirname(__DIR__) . '/');
        }

        // Définir les mocks de base
        $this->setup_basic_mocks();

        // Tester instanciation basique
        try {
            require_once 'src/Managers/PDF_Builder_Variable_Mapper.php';

            $mock_order = new MockWCOrder();
            $mapper = new \PDF_Builder\Managers\PDFBuilderVariableMapper($mock_order);

            $this->check($mapper instanceof \PDF_Builder\Managers\PDFBuilderVariableMapper,
                        "Instanciation VariableMapper réussie");

            // Tester que la classe a les méthodes attendues
            $this->check(method_exists($mapper, 'getAllVariables'),
                        "Méthode getAllVariables existe");

            // Test basique avec commande null (devrait retourner tableau avec variables company)
            $null_mapper = new \PDF_Builder\Managers\PDFBuilderVariableMapper(null);
            $null_vars = $null_mapper->getAllVariables();
            $this->check(is_array($null_vars), "Gestion commande null fonctionne");
            $this->check(array_key_exists('company_name', $null_vars), "Variables company disponibles même sans commande");

        } catch (Exception $e) {
            $this->check(false, "Erreur VariableMapper: " . $e->getMessage());
        }
    }

    private function setup_basic_mocks() {
        // Mocks minimaux pour l'instanciation
        if (!function_exists('get_option')) {
            function get_option($option, $default = '') {
                return $default;
            }
        }

        if (!function_exists('date_i18n')) {
            function date_i18n($format, $timestamp = null) {
                return date($format, $timestamp ?: time());
            }
        }
    }

    public function run_validation() {
        echo "🚀 VALIDATION PRÉ-TEST PDF BUILDER PRO\n";
        echo str_repeat("=", 60) . "\n\n";

        $this->validate_file_structure();
        $this->validate_php_syntax();
        $this->validate_dependencies();
        $this->validate_test_environment();
        $this->validate_variable_mapper();

        // Rapport final
        $this->section("RAPPORT FINAL");

        $success_rate = $this->checks > 0 ? round(($this->passed / $this->checks) * 100, 1) : 0;

        echo "📊 Statistiques:\n";
        echo "   Total vérifications: {$this->checks}\n";
        echo "   Réussies: {$this->passed}\n";
        echo "   Taux de succès: {$success_rate}%\n\n";

        if (!empty($this->errors)) {
            echo "❌ ERREURS CRITIQUES (" . count($this->errors) . "):\n";
            foreach ($this->errors as $error) {
                echo "   - $error\n";
            }
            echo "\n";
        }

        if (!empty($this->warnings)) {
            echo "⚠️  AVERTISSEMENTS (" . count($this->warnings) . "):\n";
            foreach ($this->warnings as $warning) {
                echo "   - $warning\n";
            }
            echo "\n";
        }

        if (empty($this->errors)) {
            echo "🎉 VALIDATION RÉUSSIE - Prêt pour les tests !\n";
            return true;
        } else {
            echo "❌ VALIDATION ÉCHOUÉE - Corriger les erreurs avant de tester\n";
            return false;
        }
    }
}

// Classes mock pour validation
class MockWCOrder {
    public function get_id() { return 123; }
    public function get_order_number() { return '#123'; }
    public function get_date_created() { return new DateTime('2025-10-20 10:30:00'); }
    public function get_date_modified() { return new DateTime('2025-10-20 11:00:00'); }
    public function get_status() { return 'completed'; }
    public function get_currency() { return 'EUR'; }
    public function get_total() { return '150.00'; }
    public function get_subtotal() { return '120.00'; }
    public function get_total_tax() { return '30.00'; }
    public function get_shipping_total() { return '10.00'; }
    public function get_discount_total() { return '0.00'; }
    public function get_formatted_billing_full_name() { return 'John Doe'; }
    public function get_billing_email() { return 'test@example.com'; }
    public function get_billing_first_name() { return 'John'; }
    public function get_billing_last_name() { return 'Doe'; }
    public function get_billing_phone() { return '+33123456789'; }
    public function get_customer_note() { return 'Test order note'; }
    public function get_items() {
        return [new MockOrderItem()];
    }
}

class MockOrderItem {
    public function get_name() { return 'Test Product'; }
    public function get_quantity() { return 2; }
    public function get_total() { return '100.00'; }
    public function get_product() {
        return new MockProduct();
    }
}

class MockProduct {
    public function get_price() { return '50.00'; }
    public function get_sku() { return 'TEST-SKU'; }
}

// Exécuter la validation
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? __FILE__)) {
    $validator = new PDF_Builder_Test_Validator();
    $success = $validator->run_validation();
    exit($success ? 0 : 1);
}