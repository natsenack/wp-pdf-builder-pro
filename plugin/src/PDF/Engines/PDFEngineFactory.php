<?php
/**
 * Factory pour créer des moteurs de génération PDF
 * 
 * @package PDF_Builder_Pro
 * @subpackage PDF\Engines
 * @version 1.0.0
 */

namespace PDF_Builder\PDF\Engines;

class PDFEngineFactory {
    
    /**
     * Instance du moteur courant (singleton)
     */
    private static $engine_instance = null;
    
    /**
     * Crée ou retourne une instance de moteur PDF
     * 
     * @param string $engine_name Nom du moteur ('puppeteer', 'dompdf', 'auto')
     * @param array $config Configuration spécifique au moteur
     * @return PDFEngineInterface Instance du moteur
     * @throws \Exception Si le moteur demandé n'est pas disponible
     */
    public static function create($engine_name = null, $config = []) {
        // Utiliser le moteur configuré dans les options si non spécifié
        if ($engine_name === null) {
            $engine_name = pdf_builder_get_option('pdf_builder_engine', 'puppeteer');
        }
        
        // Mode auto : tester Puppeteer puis fallback sur DomPDF
        if ($engine_name === 'auto') {
            $puppeteer = new PuppeteerEngine($config);
            if ($puppeteer->is_available()) {
                self::log_engine_selection('Puppeteer', 'auto');
                return $puppeteer;
            } else {
                self::log_engine_selection('DomPDF', 'auto-fallback');
                return new DomPDFEngine();
            }
        }
        
        // Créer le moteur spécifique
        switch (strtolower($engine_name)) {
            case 'puppeteer':
                $engine = new PuppeteerEngine($config);
                if (!$engine->is_available()) {
                    self::log_engine_selection('DomPDF', 'puppeteer-unavailable');
                    return new DomPDFEngine();
                }
                self::log_engine_selection('Puppeteer', 'explicit');
                return $engine;
                
            case 'dompdf':
                self::log_engine_selection('DomPDF', 'explicit');
                return new DomPDFEngine();
                
            default:
                self::log_engine_selection('DomPDF', 'unknown-engine');
                return new DomPDFEngine();
        }
    }
    
    /**
     * Retourne le moteur singleton (crée une seule instance)
     * 
     * @param string $engine_name Nom du moteur
     * @param array $config Configuration
     * @return PDFEngineInterface Instance du moteur
     */
    public static function get_instance($engine_name = null, $config = []) {
        if (self::$engine_instance === null) {
            self::$engine_instance = self::create($engine_name, $config);
        }
        return self::$engine_instance;
    }
    
    /**
     * Réinitialise l'instance (force la création d'un nouveau moteur)
     */
    public static function reset_instance() {
        self::$engine_instance = null;
    }
    
    /**
     * Liste tous les moteurs disponibles
     * 
     * @return array Liste des moteurs [name => [available, description]]
     */
    public static function list_available_engines() {
        $engines = [];
        
        // Puppeteer
        $puppeteer = new PuppeteerEngine();
        $engines['puppeteer'] = [
            'name' => 'Puppeteer',
            'available' => $puppeteer->is_available(),
            'description' => 'Moteur moderne avec support CSS complet (serveur externe requis)',
            'priority' => 1,
            'features' => [
                'css_moderne' => true,
                'fonts_web' => true,
                'performances' => 'excellent',
                'qualite' => 'excellent'
            ]
        ];
        
        // DomPDF
        $dompdf = new DomPDFEngine();
        $engines['dompdf'] = [
            'name' => 'DomPDF',
            'available' => $dompdf->is_available(),
            'description' => 'Moteur PHP intégré (fallback, support CSS limité)',
            'priority' => 2,
            'features' => [
                'css_moderne' => false,
                'fonts_web' => false,
                'performances' => 'bon',
                'qualite' => 'bon'
            ]
        ];
        
        return $engines;
    }
    
    /**
     * Teste tous les moteurs disponibles
     * 
     * @return array Résultats des tests pour chaque moteur
     */
    public static function test_all_engines() {
        $results = [];
        
        try {
            // Test Puppeteer - charger la configuration depuis wp_pdf_builder_settings
            $puppeteer_config = [
                'api_url' => pdf_builder_get_option('pdf_builder_puppeteer_url', ''),
                'api_token' => pdf_builder_get_option('pdf_builder_puppeteer_token', ''),
                'timeout' => pdf_builder_get_option('pdf_builder_puppeteer_timeout', 30),
                'fallback_enabled' => false,
            ];
            
            if (!empty($puppeteer_config['api_url'])) {
                $puppeteer = new PuppeteerEngine($puppeteer_config);
                $results['puppeteer'] = $puppeteer->test_connection();
            } else {
                $results['puppeteer'] = [
                    'success' => false,
                    'message' => 'URL non configurée'
                ];
            }
        } catch (\Exception $e) {
            $results['puppeteer'] = [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
        
        try {
            // Test DomPDF
            $dompdf = new DomPDFEngine();
            if ($dompdf->is_available()) {
                // Test de génération réel
                $test_html = '<html><body><h1>Test</h1></body></html>';
                $pdf = $dompdf->generate($test_html, ['width' => 794, 'height' => 300]);
                
                $results['dompdf'] = [
                    'success' => $pdf !== false,
                    'message' => $pdf !== false ? 'Fonctionnel' : 'Échec',
                    'details' => [
                        'available' => true,
                        'pdf_size' => $pdf !== false ? strlen($pdf) . ' bytes' : 'N/A'
                    ]
                ];
            } else {
                $results['dompdf'] = [
                    'success' => false,
                    'message' => 'DomPDF non disponible'
                ];
            }
        } catch (\Exception $e) {
            $results['dompdf'] = [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
        
        return $results;
    }
    
    /**
     * Retourne le moteur recommandé en fonction de la configuration
     * 
     * @return string Nom du moteur recommandé
     */
    public static function get_recommended_engine() {
        $puppeteer_url = pdf_builder_get_option('pdf_builder_puppeteer_url', '');
        
        if (!empty($puppeteer_url)) {
            $puppeteer = new PuppeteerEngine([
                'api_url' => $puppeteer_url,
                'api_token' => pdf_builder_get_option('pdf_builder_puppeteer_token', ''),
                'timeout' => pdf_builder_get_option('pdf_builder_puppeteer_timeout', 30),
            ]);
            
            if ($puppeteer->is_available()) {
                return 'puppeteer';
            }
        }
        
        return 'dompdf';
    }
    
    /**
     * Log la sélection du moteur
     * 
     * @param string $engine Nom du moteur sélectionné
     * @param string $reason Raison de la sélection
     */
    private static function log_engine_selection($engine, $reason) {
        $debug_enabled = pdf_builder_get_option('pdf_builder_debug_enabled', false);
        
        if ($debug_enabled || (defined('WP_DEBUG') && WP_DEBUG)) {
            error_log("[PDF Engine Factory] Moteur sélectionné: {$engine} (raison: {$reason})");
        }
    }
}
