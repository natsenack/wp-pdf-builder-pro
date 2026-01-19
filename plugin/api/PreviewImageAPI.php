<?php

namespace PDF_Builder\Api;

use PDF_Builder\Generators\GeneratorManager;
use PDF_Builder\Data\SampleDataProvider;
use PDF_Builder\Data\WooCommerceDataProvider;
use PDF_Builder\Utilities\ImageConverter;

// Système de cache supprimé - génération directe des données

// Declare WooCommerce functions for linter
if (!function_exists('wc_get_order')) {
/**
     * WooCommerce order getter function (declared for linter)
     * @param int $order_id
     * @return mixed
     */
    function wcGetOrder($order_id)
    {
        return null;
    }

}

class PreviewImageAPI
{
    private static $cache_dir;
    private static $max_cache_age = 3600;
// 1 heure
    private $rate_limit_window = 60;
// 1 minute
    private $rate_limit_max = 10;
// 10 requêtes par minute
    private $performance_metrics = [
        'requests_total' => 0,
        'requests_cached' => 0,
        'requests_generated' => 0,
        'generation_times' => [],
        'errors_total' => 0,
        'cache_hit_rate' => 0,
        'last_cleanup' => null
    ];
    private $generator_manager;
    private $request_log = [];

    public function __construct()
    {

        self::$cache_dir = (defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : sys_get_temp_dir())
            . '/cache/wp-pdf-builder-previews/';
// Créer répertoire cache si inexistant
        if (!file_exists(self::$cache_dir)) {
            wp_mkdir_p(self::$cache_dir);
        }

        // Initialiser le gestionnaire de générateurs
        $this->generator_manager = new GeneratorManager();
// Enregistrer l'endpoint REST pour l'étape 1.4
        add_action('rest_api_init', array($this, 'register_rest_routes'));
// NOTE: Les actions AJAX sont enregistrées dans pdf-builder-pro.php, pas ici
        // pour éviter les conflits de double enregistrement


        // Ajouter un hook pour le nettoyage manuel lors des visites admin
        add_action('admin_init', array(__CLASS__, 'maybe_trigger_manual_cleanup'));

        // Initialiser le cron seulement si nécessaire (une fois par session)
        $this->init_cron_schedule();

        // Nettoyage automatique du cache avec gestion d'erreur améliorée
        add_action('wp_pdf_cleanup_preview_cache', array(__CLASS__, 'cleanup_cache'));

        // Nettoyage du cache intelligent avec gestion d'erreur
        add_action('wp_pdf_cleanup_intelligent_cache', array(__CLASS__, 'cleanup_intelligent_cache'));
        try {
            if (!wp_next_scheduled('wp_pdf_cleanup_intelligent_cache')) {
                $result = wp_schedule_event(time(), 'daily', 'wp_pdf_cleanup_intelligent_cache');
                if (!$result) {
                    // error_log('PDF Builder: Impossible de planifier wp_pdf_cleanup_intelligent_cache - cron system unavailable');
                }
            }
        } catch (Exception $e) {
            // error_log('PDF Builder: Exception lors de la planification du cron intelligent cache: ' . $e->getMessage());
        }
    }

    /**
     * Vérifier et déclencher un nettoyage manuel si nécessaire
     * Utilisé quand le système cron n'est pas disponible
     */
    /**
     * Initialiser la planification du cron de nettoyage du cache
     * Cette fonction ne s'exécute qu'une fois par session pour éviter les conflits
     */
    private function init_cron_schedule() {
        // Vérifier si le cron a déjà été initialisé dans cette session
        static $cron_initialized = false;
        if ($cron_initialized) {
            return;
        }
        $cron_initialized = true;

        // Essayer de planifier l'événement cron avec gestion d'erreur améliorée
        try {
            // Ne pas utiliser wp_clear_scheduled_hook() à chaque chargement de page
            // Vérifier seulement si le cron existe déjà
            if (!wp_next_scheduled('wp_pdf_cleanup_preview_cache')) {
                $result = wp_schedule_event(time(), 'hourly', 'wp_pdf_cleanup_preview_cache');
                if (!$result) {
                    // Cron système indisponible - utiliser une approche alternative
                    pdf_builder_update_option('pdf_builder_manual_cache_cleanup_needed', '1');
                } else {
                    // Cron réussi - supprimer le flag manuel
                    delete_option('pdf_builder_manual_cache_cleanup_needed');
                }
            }
        } catch (Exception $e) {
            // En cas d'exception, activer le mode manuel
            pdf_builder_update_option('pdf_builder_manual_cache_cleanup_needed', '1');
        }
    }

    /**
     * Déclencher un nettoyage manuel du cache si nécessaire
     */
    public static function maybe_trigger_manual_cleanup() {
        // Vérifier si un nettoyage manuel est nécessaire
        if (pdf_builder_get_option('pdf_builder_manual_cache_cleanup_needed') !== '1') {
            return;
        }

        // Vérifier le dernier nettoyage manuel
        $last_manual_cleanup = pdf_builder_get_option('pdf_builder_last_manual_cleanup', 0);
        $time_since_last_cleanup = time() - $last_manual_cleanup;

        // Nettoyer toutes les 6 heures (21600 secondes) si nécessaire
        if ($time_since_last_cleanup > 21600) {
            self::cleanup_cache();
            pdf_builder_update_option('pdf_builder_last_manual_cleanup', time());
        }
    }

    /**
     * Enregistrer les routes REST API pour l'étape 1.4
     */
    public function register_rest_routes()
    {
        register_rest_route('wp-pdf-builder-pro/v1', '/preview', array(
            'methods' => 'POST',
            'callback' => array($this, 'handleRestPreview'),
            'permission_callback' => array($this, 'checkRestPermissions'),
            'args' => array(
                'templateId' => array(
                    'required' => false,
                    'type' => 'integer',
                    'description' => 'ID du template à utiliser'
                ),
                'orderId' => array(
                    'required' => false,
                    'type' => 'integer',
                    'description' => 'ID de la commande WooCommerce'
                ),
                'context' => array(
                    'required' => true,
                    'type' => 'string',
                    'enum' => array('editor', 'metabox'),
                    'description' => 'Contexte de l\'aperçu'
                ),
                'templateData' => array(
                    'required' => false,
                    'type' => 'object',
                    'description' => 'Données du template JSON'
                ),
                'quality' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 150,
                    'minimum' => 50,
                    'maximum' => 300,
                    'description' => 'Qualité de l\'image (50-300)'
                ),
                'format' => array(
                    'required' => false,
                    'type' => 'string',
                    'enum' => array('png', 'jpg', 'pdf'),
                    'default' => 'png',
                    'description' => 'Format de sortie'
                )
            )
        ));
    }

        /**
     * Vérifier les permissions REST API
     */
    public function checkRestPermissions($request)
    {
        $context = $request->getParam('context');
        switch ($context) {
            case 'editor':
                return current_user_can('manage_options');
            case 'metabox':
                return current_user_can('edit_shop_orders');
            default:
                return false;
        }
    }

    /**
     * Handler pour l'endpoint REST /preview - VERSION SIMPLIFIEE JOUR 1-2
     * Pour commencer doucement : juste validation et réponse de base
     */
    public function handleRestPreview($request)
    {
        try {
            // Récupération des paramètres
            $params = array(
                'template_id' => $request->getParam('templateId'),
                'order_id' => $request->getParam('orderId'),
                'context' => $request->getParam('context'),
                'template_data' => $request->getParam('templateData'),
                'quality' => $request->getParam('quality') ?: 150,
                'format' => $request->getParam('format') ?: 'png'
            );

            // Validation des paramètres (Jour 1-2)
            $validated_params = $this->validateRestParams($params);

            // === JOUR 3-4 : Génération PDF avec DomPDF ===
            $generation_result = $this->generatePDFPreview($validated_params);

            return new \WP_REST_Response(array(
                'success' => true,
                'message' => 'PDF généré avec succès - Jour 3-4 validé',
                'data' => array(
                    'validated_params' => $validated_params,
                    'generation_result' => $generation_result,
                    'version' => '1.0-jour3-4'
                )
            ), 200);

        } catch (\Exception $e) {
            return new \WP_Error(
                'preview_validation_error',
                'Erreur de validation: ' . $e->getMessage(),
                array('status' => 400)
            );
        }
    }

    /**
     * Validation des paramètres REST
     */
    private function validateRestParams($params)
    {
        $validated = array(
            'context' => sanitize_text_field($params['context']),
            'quality' => max(50, min(300, intval($params['quality']))),
            'format' => in_array(strtolower($params['format']), array('png', 'jpg', 'pdf')) ?
                       strtolower($params['format']) : 'png'
        );
// Validation selon contexte
        switch ($validated['context']) {
            case 'editor':
                if (empty($params['templateData'])) {
                    throw new Exception('templateData is required for editor context');
                }
                $validated['template_data'] = $this->validateTemplateData(json_encode($params['templateData']));
                $validated['preview_type'] = 'design';

                break;
            case 'metabox':
                if (empty($params['templateData'])) {
                    throw new Exception('templateData is required for metabox context');
                }
                $validated['template_data'] = $this->validateTemplateData(json_encode($params['templateData']));
                $validated['order_id'] = !empty($params['orderId']) ? intval($params['orderId']) : null;
                $validated['preview_type'] = $validated['order_id'] ? 'order' : 'design';
            // Validation commande existe
                if ($validated['order_id'] && function_exists('wc_get_order')) {
                    $order = \wc_get_order($validated['order_id']);
                    if (!$order) {
                        throw new Exception('Order not found', 404);
                    }
                }

                break;
            default:
                throw new Exception('Invalid context: ' . $validated['context']);
        }

        return $validated;
    }

    /**
     * Point d'entrée unifié pour tous les aperçus
     */
    public function generatePreview()
    {
        // === GESTION D'ERREUR EXTRÊMEMENT ROBUSTE ===
        // Nettoyer tous les output buffers d'abord
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Commencer un nouveau buffer
        ob_start();
// Set JSON header IMMÉDIATEMENT
        header('Content-Type: application/json; charset=UTF-8');
// Enregistrer un handler d'erreur shutdown pour les erreurs fatales
        register_shutdown_function(array($this, 'shutdownHandler'));
        $start_time = microtime(true);
        try {
        // VALIDATION TRÈS SIMPLE INLINE
            // Vérification nonce
            if (!isset($_POST['nonce'])) {
                throw new \Exception('Missing nonce');
            }

            if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions')) {
                throw new \Exception('Invalid nonce');
            }

            // Vérification permissions
            $context = sanitize_text_field($_POST['context'] ?? 'editor');
            $required_cap = $this->getRequiredCapability($context);
            if (!current_user_can($required_cap)) {
                throw new \Exception('Insufficient permissions');
            }



            // Rate limiting
            $this->checkRateLimit();
        // Récupération et validation des paramètres

            $params = $this->getValidatedParams();
        // Génération avec cache intelligent

            $result = $this->generateWithCache($params);
        // Clean buffer et envoyer réponse
            ob_clean();
            $this->sendCompressedResponse($result);
        } catch (\Exception $e) {
            ob_clean();
            $this->sendJsonError($e->getMessage());
        }
    }

    /**
     * Handler d'erreur PHP shutdown pour capturer les erreurs fatales
     */
    public function shutdownHandler()
    {
        $error = error_get_last();
        if (
            $error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE
                || $error['type'] === E_CORE_ERROR || $error['type'] === E_COMPILE_ERROR)
        ) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header('Content-Type: application/json; charset=UTF-8', true);
            echo json_encode(['success' => false, 'message' => 'Fatal error: ' . substr($error['message'], 0, 100)]);
            exit;
        }
    }

    /**
     * Rate limiting pour prévenir les abus
     */
    private function checkRateLimit()
    {
        // ✅ RATE LIMIT DÉSACTIVÉ - laisser passer toutes les requêtes
        // Les transients ne sont plus utilisés
    }

    /**
     * Récupération et validation des paramètres selon contexte
     */
    private function getValidatedParams()
    {
        $context = sanitize_text_field($_POST['context'] ?? 'editor');
        $params = [
            'context' => $context,
            'template_data' => null,
            'preview_type' => 'design',
            'order_id' => null,
            'quality' => 150,
            'format' => 'png'
        ];
// Paramètres spécifiques selon contexte
        switch ($context) {
            case 'editor':
                // Aperçu design - données fictives
                $params['template_data'] = $this->validateTemplateData($_POST['template_data'] ?? '');
                $params['preview_type'] = 'design';

                break;
            case 'metabox':
                // Aperçu commande réelle
                $params['template_data'] = $this->validateTemplateData($_POST['template_data'] ?? '');
                $params['order_id'] = isset($_POST['order_id']) ? intval($_POST['order_id']) : null;
                $params['preview_type'] = $params['order_id'] ? 'order' : 'design';
            // Validation commande existe
                if ($params['order_id'] && function_exists('wc_get_order')) {
                    $order = \wc_get_order($params['order_id']);
                    if (!$order) {
                        $this->sendJsonError('Order not found', 404);
                    }
                }

                break;
            default:
                $this->sendJsonError('Invalid context', 400);
        }

        // Paramètres optionnels
        if (isset($_POST['quality'])) {
            $params['quality'] = max(50, min(300, intval($_POST['quality'])));
        }

        if (isset($_POST['format'])) {
            $allowed_formats = ['png', 'jpg', 'jpeg'];
            $params['format'] = in_array(strtolower($_POST['format']), $allowed_formats) ?
                               strtolower($_POST['format']) : 'png';
        }

        return $params;
    }

    /**
     * Validation des données template
     */
    private function validateTemplateData($data)
    {
        if (empty($data)) {
            return $this->getDefaultTemplate();
        }

        $sanitized_data = \PDF_Builder\Admin\Utils\Utils::sanitizeJsonInput($data);
        $decoded = json_decode($sanitized_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendJsonError('Invalid template data: ' . json_last_error_msg(), 400);
        }

        // Validation structure de base - accepter les deux formats
        // Format 1: {template: {elements: [...]}}
        // Format 2: {elements: [...]}
        if (isset($decoded['template']) && isset($decoded['template']['elements'])) {
// Format avec template wrapper
            $elements = $decoded['template']['elements'];
        } elseif (isset($decoded['elements'])) {
        // Format direct
            $elements = $decoded['elements'];
        } else {
            $this->sendJsonError('Invalid template structure - missing elements', 400);
        }

        // Vérifier que elements est un array
        if (!is_array($elements)) {
            $this->sendJsonError('Elements must be an array', 400);
        }

        // Recréer la structure attendue
        $decoded = [
            'template' => [
                'elements' => $elements
            ]
        ];
// Sanitisation des éléments
        $decoded['template']['elements'] = array_map(function ($element) {

            return $this->sanitizeElement($element);
        }, $decoded['template']['elements']);
        return $decoded;
    }

    /**
     * Sanitisation d'un élément template
     */
    private function sanitizeElement($element)
    {
        $sanitized = [];
// Champs autorisés selon type
        $allowed_fields = [
            'type', 'content', 'x', 'y', 'width', 'height', 'fontSize', 'fontFamily',
            'color', 'backgroundColor', 'textAlign', 'fontWeight', 'fontStyle'
        ];
        foreach ($allowed_fields as $field) {
            if (isset($element[$field])) {
                if (is_string($element[$field])) {
                    $sanitized[$field] = sanitize_text_field($element[$field]);
                } elseif (is_numeric($element[$field])) {
                    $sanitized[$field] = floatval($element[$field]);
                } else {
                    $sanitized[$field] = $element[$field];
                }
            }
        }

        return $sanitized;
    }

    /**
     * Génération avec cache intelligent - VERSION RÉELLE POUR ÉTAPE 1.4
     */
    public function generateWithCache($params)
    {
        $start_time = microtime(true);
        $this->performance_metrics['requests_total']++;

        $cache_key = $this->generateCacheKey($params);

        // Système de cache supprimé - génération directe des métadonnées
        $cache_file = self::$cache_dir . $cache_key . '.' . $params['format'];

        // Vérifier si cache fichier valide (seul système de cache restant)
        if ($this->isCacheValid($cache_file, $params)) {
            $this->performance_metrics['requests_cached']++;
            $generation_time = microtime(true) - $start_time;
            $this->performance_metrics['generation_times'][] = $generation_time;

            return array(
                'image_url' => $this->getCacheUrl($cache_key, $params['format']),
                'cached' => true,
                'cache_key' => $cache_key,
                'status' => 'preview_ready',
                'format' => $params['format'],
                'quality' => $params['quality'],
                'cache_source' => 'file',
                'generation_time' => round($generation_time, 3)
            );
        }

        // Générer l'image réelle
        $this->performance_metrics['requests_generated']++;
        $image_url = $this->generateRealImage($params, $cache_file);
        $generation_time = microtime(true) - $start_time;
        $this->performance_metrics['generation_times'][] = $generation_time;

        $result = array(
            'image_url' => $image_url,
            'cached' => false,
            'cache_key' => $cache_key,
            'status' => 'preview_ready',
            'format' => $params['format'],
            'quality' => $params['quality'],
            'cache_source' => 'generated',
            'generation_time' => round($generation_time, 3)
        );

        return $result;
    }

    /**
     * Génération d'une vraie image à partir des données template
     * VERSION ARCHITECTURALE UNIFIÉE - Utilise GeneratorManager
     */
    private function generateRealImage($params, $cache_file)
    {
        try {
// Créer le fournisseur de données selon le contexte
            $data_provider = $this->createDataProvider($params);
// Préparer les données du template
            $template_data = $params['template_data'];
            
            // Logging pour debug
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // error_log('[PDF Builder API] generateRealImage - template_data: ' . print_r($template_data, true));
                // error_log('[PDF Builder API] generateRealImage - context: ' . ($params['context'] ?? 'unknown'));
                // error_log('[PDF Builder API] generateRealImage - data_provider: ' . get_class($data_provider));
            }
            
// Utiliser GeneratorManager pour générer l'image
            $result = $this->generator_manager->generatePreview($template_data, $data_provider, $params['format'], [
                    'quality' => $params['quality'],
                    'output_file' => $cache_file,
                    'context' => $params['context']
                ]);
// Vérifier si la génération a réussi
            // GeneratorManager peut retourner :
            // - true/false pour les générateurs simples
            // - array avec 'success' => true/false pour les générateurs complexes



            $generation_successful = false;
            if (is_array($result)) {
                $generation_successful = isset($result['success']) ? $result['success'] : true;
            } elseif (is_bool($result)) {
                $generation_successful = $result;
            } elseif ($result) {
                $generation_successful = true;
            // Valeur truthy
            } else {
            }



            if (!$generation_successful) {
                throw new Exception('Image generation failed: All generators failed');
            }

            // Si le générateur a retourné des données d'image au lieu de créer un fichier,
            // les sauvegarder dans le cache
            if (is_array($result) && isset($result['data'])) {
                file_put_contents($cache_file, $result['data']);
            }

            // Retourner l'URL de l'image
            // Si le générateur a retourné une URL complète, l'utiliser
            if (is_array($result) && isset($result['image_url'])) {
                return $result['image_url'];
            }

            // Sinon, construire l'URL du fichier cache
            return $this->getCacheUrl(basename($cache_file, '.' . $params['format']), $params['format']);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Crée le fournisseur de données approprié selon le contexte
     */
    private function createDataProvider($params)
    {
        $context = $params['context'] ?? 'canvas';
        if ($context === 'metabox' && !empty($params['order_id'])) {
        // Données réelles WooCommerce
            return new WooCommerceDataProvider($params['order_id'], $context);
        } else {
        // Données fictives pour l'éditeur
            return new SampleDataProvider($context);
        }
    }

    /**
     * Création du générateur selon contexte
     * À implémenter ultérieurement selon la roadmap
     */
    private function createGenerator($params)
    {
        // Implémentation future - étapes 3+ de la roadmap
        throw new \Exception('Generator not yet implemented - phase 1.5 en cours');
    }

    /**
     * Génération clé cache unique
     */
    private function generateCacheKey($params)
    {
        $data = [
            'template' => md5(serialize($params['template_data'])),
            'context' => $params['context'],
            'order_id' => $params['order_id'],
            'quality' => $params['quality'],
            'format' => $params['format']
        ];
        return md5(serialize($data));
    }

    /**
     * Vérification validité cache
     */
    private function isCacheValid($cache_file, $params)
    {
        if (!file_exists($cache_file)) {
            return false;
        }

        // Vérifier âge du fichier
        $file_age = time() - filemtime($cache_file);
        if ($file_age > self::$max_cache_age) {
            return false;
        }

        // Pour les aperçus de commande, vérifier si commande modifiée récemment
        if ($params['order_id'] && function_exists('wc_get_order')) {
            $order = \wc_get_order($params['order_id']);
            if ($order) {
                $order_modified = strtotime($order->getDateModified());
                if ($order_modified > filemtime($cache_file)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * URL du fichier en cache
     */
    private function getCacheUrl($cache_key, $format)
    {
        // Utiliser le même chemin que le stockage (WP_CONTENT_DIR)
        $content_url = content_url();
        return $content_url . '/cache/wp-pdf-builder-previews/' . $cache_key . '.' . $format;
    }

    /**
     * Nettoyage automatique du cache
     */
    public function cleanupCache()
    {
        if (!is_dir(self::$cache_dir)) {
            return;
        }

        $files = glob(self::$cache_dir . '*');
        $now = time();
        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > self::$max_cache_age) {
                unlink($file);
            }
        }
    }

    /**
     * Template par défaut pour validation
     */
    private function getDefaultTemplate()
    {
        return [
            'template' => [
                'elements' => [
                    [
                        'type' => 'text',
                        'content' => 'Aperçu PDF Builder Pro',
                        'x' => 50,
                        'y' => 50,
                        'width' => 200,
                        'height' => 30,
                        'fontSize' => 16,
                        'color' => '#000000'
                    ]
                ]
            ]
        ];
    }

    /**
     * Permission requise selon contexte
     */
    private function getRequiredCapability($context)
    {
        switch ($context) {
            case 'editor':
                return 'manage_options';
// Admin seulement pour éditeur
            case 'metabox':
                return 'edit_shop_orders';
// Vendeurs peuvent voir metabox
            default:
                return 'manage_options';
        }
    }

    /**
     * Log des événements de sécurité
     */
    private function logSecurityEvent($event, $ip, $extra = null)
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $message = sprintf(
                '[PDF Builder Security] %s - IP: %s - Extra: %s',
                $event,
                $ip,
                is_scalar($extra) ? $extra : json_encode($extra)
            );
        }
    }

    /**
     * Log des requêtes pour monitoring
     */
    private function logRequest($params)
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->request_log[] = [
                'timestamp' => time(),
                'user_id' => get_current_user_id(),
                'context' => $params['context'],
                'order_id' => $params['order_id']
            ];
        }
    }

    /**
     * Réponse compressée pour performance
     */
    private function sendCompressedResponse($result)
    {
        // Compression GZIP si supportée
        if (
            isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
            strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false
        ) {
            ob_start('ob_gzhandler');
        }

        $this->sendJsonSuccess($result);
    }

    /**
     * Envoi de succès JSON forcé
     */
    private function sendJsonSuccess($data)
    {
        wp_send_json_success($data);
    }

    /**
     * Envoi d'erreur JSON - SIMPLE ET ROBUSTE
     */
    private function sendJsonError($message, $code = 400)
    {
        // Nettoyer les buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Headers
        header('Content-Type: application/json; charset=UTF-8', true);
        http_response_code($code);
// Réponse JSON simple - même pattern que l'autre plugin
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }

    /**
     * Gestion centralisée des erreurs
     */
    private function handleError($exception, $start_time)
    {
        $duration = microtime(true) - $start_time;
        $error_message = $exception->getMessage();
// Pour debug, log aussi dans les headers de réponse
        header('X-PDF-Error: ' . substr($error_message, 0, 100));
// Réponse d'erreur générique pour sécurité
        wp_send_json_error([
            'message' => 'Preview generation failed: ' . substr($error_message, 0, 50),
            'code' => 'PREVIEW_ERROR',
            'debug' => defined('WP_DEBUG') && WP_DEBUG ? $error_message : null
        ]);
    }

    public static function cleanup_cache()
    {
        try {
            if (!is_dir(self::$cache_dir)) {
                return;
            }

            $files = glob(self::$cache_dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
            $now = time();
            $deleted_count = 0;

            foreach ($files as $file) {
                if (is_file($file)) {
                    $file_age = $now - filemtime($file);
                    if ($file_age > self::$max_cache_age) {
                        if (unlink($file)) {
                            $deleted_count++;
                        }
                    }
                }
            }

            // Log pour debug
            if (defined('WP_DEBUG') && WP_DEBUG) {
                
            }

        } catch (\Exception $e) {
            // Log l'erreur mais ne pas interrompre l'exécution
            
        }
    }

    /**
     * Génère un aperçu PDF avec DomPDF (Jour 3-4) - Méthode publique pour tests
     *
     * @param array $validated_params Paramètres validés
     * @return array Résultat de la génération
     */
    public function generatePDFPreviewPublic($validated_params)
    {
        return $this->generatePDFPreview($validated_params);
    }

    /**
     * Génère un aperçu PDF avec DomPDF (Jour 3-4)
     *
     * @param array $validated_params Paramètres validés
     * @return array Résultat de la génération
     */
    private function generatePDFPreview($validated_params)
    {
        try {
            // Créer un fournisseur de données statiques (pas de variables dynamiques)
            $data_provider = new SampleDataProvider();

            // Préparer les données du template
            $template_data = $validated_params['template_data'] ?? $this->getDefaultTemplateData();

            // Configuration pour DomPDF optimisée
            $generator_config = [
                'dpi' => 150, // Résolution optimisée
                'compression' => 'FAST', // Compression rapide
                'memory_limit' => '256M', // Limite mémoire
                'timeout' => 30, // Timeout 30 secondes
                'paper_size' => 'A4',
                'orientation' => 'portrait'
            ];

            // Générer le PDF avec le GeneratorManager
            $result = $this->generator_manager->generatePreview(
                $template_data,
                $data_provider,
                'pdf', // Format de sortie souhaité
                $generator_config
            );

            if ($result === false) {
                throw new \Exception('Échec de la génération PDF - tous les générateurs ont échoué');
            }

            // Pour les jours 3-4, nous retournons des informations sur la génération
            // Dans les jours 5-7, nous convertirons en image
            $pdf_content = $result;

            // === JOUR 5-7 : Conversion PDF vers Images ===
            if ($validated_params['format'] !== 'pdf') {
                $image_result = ImageConverter::convertPdfToImage($pdf_content, $validated_params);

                if ($image_result['success']) {
                    return [
                        'pdf_generated' => true,
                        'image_converted' => true,
                        'format' => $validated_params['format'],
                        'generator_used' => 'dompdf',
                        'converter_used' => $image_result['converter'],
                        'file_size' => strlen($image_result['image_data']),
                        'quality' => $validated_params['quality'],
                        'config' => $generator_config,
                        'template_elements' => count($template_data['elements'] ?? []),
                        'data_provider' => 'SampleDataProvider (statique)',
                        'ready_for_image_conversion' => true,
                        'image_data' => base64_encode($image_result['image_data']),
                        'cache_key' => $this->generateCacheKey($validated_params)
                    ];
                } else {
                    // Fallback : retourner le PDF si conversion échoue
                    return [
                        'pdf_generated' => true,
                        'image_converted' => false,
                        'format' => 'pdf', // Fallback vers PDF
                        'error' => $image_result['error'],
                        'generator_used' => 'dompdf',
                        'file_size' => strlen($pdf_content),
                        'config' => $generator_config,
                        'template_elements' => count($template_data['elements'] ?? []),
                        'data_provider' => 'SampleDataProvider (statique)',
                        'ready_for_image_conversion' => true,
                        'fallback_used' => true
                    ];
                }
            }

            // Format PDF demandé - retourner directement
            return [
                'pdf_generated' => true,
                'image_converted' => false,
                'format' => 'pdf',
                'generator_used' => 'dompdf',
                'file_size' => strlen($pdf_content),
                'config' => $generator_config,
                'template_elements' => count($template_data['elements'] ?? []),
                'data_provider' => 'SampleDataProvider (statique)',
                'ready_for_image_conversion' => true
            ];

        } catch (\Exception $e) {
            // En cas d'erreur, retourner les détails de l'erreur
            return [
                'pdf_generated' => false,
                'error' => $e->getMessage(),
                'generator_attempts' => $this->generator_manager->getAttemptHistory(),
                'config' => $generator_config ?? [],
                'fallback_used' => true
            ];
        }
    }

    /**
     * Nettoie le cache intelligent (fonction supprimée - système de cache retiré)
     * Supprime les entrées expirées et optimise les performances
     */
    public static function cleanup_intelligent_cache()
    {
        // Système de cache supprimé - cette méthode ne fait plus rien
        return;
    }

    /**
     * Invalide le cache pour un template spécifique
     *
     * @param int $template_id ID du template
     * @param string $context Contexte (editor/metabox)
     */
    public static function invalidateTemplateCache($template_id, $context = null)
    {
        try {
            $pattern = 'preview_metadata_*' . $template_id . '*';

            if ($context) {
                $pattern .= '*' . $context . '*';
            }

            // Système de cache supprimé - plus de suppression par pattern

            if (defined('WP_DEBUG') && WP_DEBUG) {
                // error_log('[PDF Builder] Cache invalidé pour template ' . $template_id . ' (' . $context . ')');
            }

        } catch (\Exception $e) {
            // error_log('[PDF Builder] Erreur invalidation cache template: ' . $e->getMessage());
        }
    }

    /**
     * Obtient les métriques de performance du cache
     *
     * @return array Métriques de cache
     */
    public static function getCacheMetrics()
    {
        $instance = self::getInstance();
        // Système de cache intelligent supprimé
        $intelligent_metrics = [
            'status' => 'disabled',
            'message' => 'Intelligent cache system removed'
        ];

        // Calculer le taux de succès du cache
        $total_requests = $instance->performance_metrics['requests_total'];
        $cached_requests = $instance->performance_metrics['requests_cached'];
        $hit_rate = $total_requests > 0 ? round(($cached_requests / $total_requests) * 100, 2) : 0;

        // Calculer les temps moyens de génération
        $generation_times = $instance->performance_metrics['generation_times'];
        $avg_generation_time = !empty($generation_times) ? round(array_sum($generation_times) / count($generation_times), 3) : 0;
        $max_generation_time = !empty($generation_times) ? round(max($generation_times), 3) : 0;

        return [
            'file_cache' => [
                'directory' => self::$cache_dir,
                'max_age' => self::$max_cache_age,
                'exists' => file_exists(self::$cache_dir)
            ],
            'intelligent_cache' => $intelligent_metrics,
            'performance' => [
                'total_requests' => $total_requests,
                'cached_requests' => $cached_requests,
                'generated_requests' => $instance->performance_metrics['requests_generated'],
                'cache_hit_rate' => $hit_rate . '%',
                'avg_generation_time' => $avg_generation_time . 's',
                'max_generation_time' => $max_generation_time . 's',
                'error_count' => $instance->performance_metrics['errors_total']
            ],
            'rate_limiting' => [
                'window' => 60, // secondes
                'max_requests' => 10
            ],
            'last_cleanup' => $instance->performance_metrics['last_cleanup']
        ];
    }
}




