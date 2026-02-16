<?php
/**
 * Puppeteer Engine - Génère des PDF via un serveur Puppeteer externe
 * 
 * @package PDF_Builder_Pro
 * @subpackage PDF\Engines
 * @version 1.0.0
 */

namespace PDF_Builder\PDF\Engines;

class PuppeteerEngine implements PDFEngineInterface {
    
    /**
     * Configuration de l'API Puppeteer
     */
    private $api_url;
    private $api_token;
    private $timeout;
    private $fallback_engine;
    
    /**
     * Logs de debug
     */
    private $debug_enabled;
    
    /**
     * Constructeur
     * 
     * @param array $config Configuration [api_url, api_token, timeout, fallback_to_dompdf]
     */
    public function __construct($config = []) {
        $this->api_url = $config['api_url'] ?? get_option('pdf_builder_puppeteer_url', '');
        $this->api_token = $config['api_token'] ?? get_option('pdf_builder_puppeteer_token', '');
        $this->timeout = $config['timeout'] ?? get_option('pdf_builder_puppeteer_timeout', 30);
        $this->fallback_engine = $config['fallback_to_dompdf'] ?? get_option('pdf_builder_puppeteer_fallback', true);
        $this->debug_enabled = get_option('pdf_builder_debug_enabled', false);
    }
    
    /**
     * Génère un PDF à partir de HTML
     * 
     * @param string $html Contenu HTML
     * @param array $options Options de génération [width, height, orientation, etc.]
     * @return string|false Contenu PDF binaire ou false en cas d'erreur
     */
    public function generate($html, $options = []) {
        $this->debug_log("========== GÉNÉRATION PDF PUPPETEER ==========");
        $this->debug_log("API URL: {$this->api_url}");
        $this->debug_log("HTML size: " . strlen($html) . " bytes");
        
        // Vérifier configuration
        if (empty($this->api_url) || empty($this->api_token)) {
            $this->debug_log("Configuration Puppeteer incomplète", "ERROR");
            return $this->fallback_to_dompdf($html, $options);
        }
        
        try {
            // Préparer les données pour l'API
            $payload = [
                'html' => $html,
                'options' => $this->prepare_options($options)
            ];
            
            $this->debug_log("Options PDF: " . json_encode($payload['options']));
            
            // Envoyer requête à l'API Puppeteer
            $response = $this->send_api_request($payload);
            
            if ($response === false) {
                $this->debug_log("Échec requête API Puppeteer", "ERROR");
                return $this->fallback_to_dompdf($html, $options);
            }
            
            $this->debug_log("PDF généré avec succès - Taille: " . strlen($response) . " bytes");
            return $response;
            
        } catch (\Exception $e) {
            $this->debug_log("Erreur Puppeteer: " . $e->getMessage(), "ERROR");
            return $this->fallback_to_dompdf($html, $options);
        }
    }
    
    /**
     * Génère une image (PNG/JPG) à partir de HTML
     * 
     * @param string $html Contenu HTML
     * @param array $options Options [format => 'png'|'jpg', width, height, quality]
     * @return string|false Contenu image binaire ou false
     */
    public function generate_image($html, $options = []) {
        $this->debug_log("========== GÉNÉRATION IMAGE PUPPETEER ==========");
        
        // Vérifier configuration
        if (empty($this->api_url) || empty($this->api_token)) {
            $this->debug_log("Configuration Puppeteer incomplète", "ERROR");
            return false;
        }
        
        try {
            $format = $options['format'] ?? 'png';
            $this->debug_log("Format image: {$format}");
            
            // Préparer les données pour l'API
            $payload = [
                'html' => $html,
                'format' => $format,
                'options' => [
                    'type' => $format === 'jpg' ? 'jpeg' : 'png',
                    'fullPage' => false,
                    'width' => $options['width'] ?? 794,
                    'height' => $options['height'] ?? 1123,
                    'quality' => $format === 'jpg' ? ($options['quality'] ?? 90) : null
                ]
            ];
            
            // Envoyer requête à l'API Puppeteer (endpoint screenshot)
            $response = $this->send_api_request($payload, 'screenshot');
            
            if ($response === false) {
                $this->debug_log("Échec génération image", "ERROR");
                return false;
            }
            
            $this->debug_log("Image générée avec succès - Taille: " . strlen($response) . " bytes");
            return $response;
            
        } catch (\Exception $e) {
            $this->debug_log("Erreur génération image: " . $e->getMessage(), "ERROR");
            return false;
        }
    }
    
    /**
     * Envoie une requête à l'API Puppeteer
     * 
     * @param array $payload Données à envoyer
     * @param string $endpoint Endpoint de l'API ('pdf' ou 'screenshot')
     * @return string|false Réponse binaire ou false
     */
    private function send_api_request($payload, $endpoint = 'pdf') {
        $url = rtrim($this->api_url, '/') . '/' . $endpoint;
        
        $this->debug_log("Envoi requête vers: {$url}");
        
        // Préparer les headers
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_token,
            'X-PDF-Builder-Version: ' . PDF_BUILDER_PRO_VERSION
        ];
        
        // Configuration cURL
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);
        
        $start_time = microtime(true);
        $response = curl_exec($ch);
        $duration = round((microtime(true) - $start_time) * 1000, 2);
        
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        $this->debug_log("Réponse HTTP: {$http_code} - Durée: {$duration}ms");
        
        // Vérifier erreurs
        if ($response === false) {
            $this->debug_log("Erreur cURL: {$curl_error}", "ERROR");
            return false;
        }
        
        if ($http_code !== 200) {
            $this->debug_log("Erreur HTTP {$http_code}: " . substr($response, 0, 200), "ERROR");
            return false;
        }
        
        return $response;
    }
    
    /**
     * Prépare les options pour l'API Puppeteer
     * 
     * @param array $options Options brutes
     * @return array Options formatées pour Puppeteer
     */
    private function prepare_options($options) {
        // Dimensions par défaut : A4 @ 96 DPI (794×1123px)
        $width = $options['width'] ?? 794;
        $height = $options['height'] ?? 1123;
        $orientation = ($width > $height) ? 'landscape' : 'portrait';
        
        // Convertir pixels en inches pour Puppeteer (96 DPI)
        $width_in = round($width / 96, 2);
        $height_in = round($height / 96, 2);
        
        return [
            'format' => $options['format'] ?? null, // null = custom
            'width' => $width_in . 'in',
            'height' => $height_in . 'in',
            'landscape' => $orientation === 'landscape',
            'printBackground' => true,
            'preferCSSPageSize' => false,
            'displayHeaderFooter' => false,
            'margin' => [
                'top' => '0',
                'right' => '0',
                'bottom' => '0',
                'left' => '0'
            ]
        ];
    }
    
    /**
     * Fallback vers DomPDF si Puppeteer échoue
     * 
     * @param string $html HTML à convertir
     * @param array $options Options de génération
     * @return string|false Contenu PDF ou false
     */
    private function fallback_to_dompdf($html, $options) {
        if (!$this->fallback_engine) {
            $this->debug_log("Fallback DomPDF désactivé", "WARNING");
            return false;
        }
        
        $this->debug_log("Basculement vers DomPDF", "WARNING");
        
        try {
            $dompdf_engine = new DomPDFEngine();
            return $dompdf_engine->generate($html, $options);
        } catch (\Exception $e) {
            $this->debug_log("Erreur DomPDF fallback: " . $e->getMessage(), "ERROR");
            return false;
        }
    }
    
    /**
     * Teste la connexion à l'API Puppeteer
     * 
     * @return array Résultat du test [success => bool, message => string, details => array]
     */
    public function test_connection() {
        if (empty($this->api_url) || empty($this->api_token)) {
            return [
                'success' => false,
                'message' => 'Configuration incomplète',
                'details' => [
                    'api_url' => !empty($this->api_url),
                    'api_token' => !empty($this->api_token)
                ]
            ];
        }
        
        $test_html = '<html><body><h1>Test PDF Builder Pro</h1><p>Connexion réussie!</p></body></html>';
        
        $start_time = microtime(true);
        $result = $this->generate($test_html, ['width' => 794, 'height' => 300]);
        $duration = round((microtime(true) - $start_time) * 1000, 2);
        
        if ($result !== false) {
            return [
                'success' => true,
                'message' => 'Connexion réussie',
                'details' => [
                    'api_url' => $this->api_url,
                    'pdf_size' => strlen($result) . ' bytes',
                    'duration' => $duration . 'ms'
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Échec de la connexion',
                'details' => [
                    'api_url' => $this->api_url,
                    'duration' => $duration . 'ms'
                ]
            ];
        }
    }
    
    /**
     * Retourne le nom du moteur
     * 
     * @return string Nom du moteur
     */
    public function get_name() {
        return 'Puppeteer';
    }
    
    /**
     * Vérifie si le moteur est disponible
     * 
     * @return bool True si disponible
     */
    public function is_available() {
        return !empty($this->api_url) && !empty($this->api_token);
    }
    
    /**
     * Logger conditionnel
     * 
     * @param string $message Message
     * @param string $level Niveau (INFO, WARNING, ERROR)
     */
    private function debug_log($message, $level = 'INFO') {
        if ($this->debug_enabled || (defined('WP_DEBUG') && WP_DEBUG)) {
            error_log("[Puppeteer Engine - {$level}] {$message}");
        }
    }
}
