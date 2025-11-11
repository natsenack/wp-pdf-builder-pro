<?php

namespace WP_PDF_Builder_Pro\Api;

use WP_PDF_Builder_Pro\Generators\GeneratorManager;
use WP_PDF_Builder_Pro\Data\SampleDataProvider;
use WP_PDF_Builder_Pro\Data\WooCommerceDataProvider;

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
    private $cache_dir;
    private $max_cache_age = 3600;
// 1 heure
    private $rate_limit_window = 60;
// 1 minute
    private $rate_limit_max = 10;
// 10 requêtes par minute
    private $request_log = [];
    private $generator_manager;

    public function __construct()
    {

        $this->cache_dir = (defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : sys_get_temp_dir())
            . '/cache/wp-pdf-builder-previews/';
// Créer répertoire cache si inexistant
        if (!file_exists($this->cache_dir)) {
            wp_mkdir_p($this->cache_dir);
        }

        // Initialiser le gestionnaire de générateurs
        $this->generator_manager = new GeneratorManager();
// Enregistrer l'endpoint REST pour l'étape 1.4
        add_action('rest_api_init', array($this, 'register_rest_routes'));
// NOTE: Les actions AJAX sont enregistrées dans pdf-builder-pro.php, pas ici
        // pour éviter les conflits de double enregistrement


        // Nettoyage automatique du cache
        add_action('wp_pdf_cleanup_preview_cache', array($this, 'cleanup_cache'));
        if (!wp_next_scheduled('wp_pdf_cleanup_preview_cache')) {
            wp_schedule_event(time(), 'hourly', 'wp_pdf_cleanup_preview_cache');
        }
    }

    /**
     * Enregistrer les routes REST API pour l'étape 1.4
     */
    public function registerRestRoutes()
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
     * Handler pour l'endpoint REST /preview
     */
    public function handleRestPreview($request)
    {
        $start_time = microtime(true);
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
        // Validation des paramètres
            $validated_params = $this->validateRestParams($params);
        // Rate limiting
            $this->checkRateLimit();
        // Génération avec cache
            $result = $this->generateWithCache($validated_params);
        // Log des performances


            return new WP_REST_Response(array(
                'success' => true,
                'data' => $result,
                'performance' => array(
                    'duration' => round(microtime(true) - $start_time, 3),
                    'cached' => $result['cached'] ?? false
                )
            ), 200);
        } catch (Exception $e) {
            return new WP_Error('preview_generation_failed', $e->getMessage(), array(
                    'status' => 500,
                    'performance' => array(
                        'duration' => round(microtime(true) - $start_time, 3)
                    )
                ));
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
                    $order = wc_get_order($validated['order_id']);
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
        register_shutdown_function(array($this, '_shutdown_handler'));
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
                    $order = wc_get_order($params['order_id']);
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

        $decoded = json_decode(stripslashes($data), true);
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
        $cache_key = $this->generateCacheKey($params);
        $cache_file = $this->cache_dir . $cache_key . '.' . $params['format'];
// Vérifier si cache valide
        if ($this->isCacheValid($cache_file, $params)) {
            return array(
                'image_url' => $this->getCacheUrl($cache_key, $params['format']),
                'cached' => true,
                'cache_key' => $cache_key,
                'status' => 'preview_ready',
                'format' => $params['format'],
                'quality' => $params['quality']
            );
        }

        // Générer l'image réelle
        $image_url = $this->generateRealImage($params, $cache_file);
        return array(
            'image_url' => $image_url,
            'cached' => false,
            'cache_key' => $cache_key,
            'status' => 'preview_ready',
            'format' => $params['format'],
            'quality' => $params['quality']
        );
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
        if ($file_age > $this->max_cache_age) {
            return false;
        }

        // Pour les aperçus de commande, vérifier si commande modifiée récemment
        if ($params['order_id'] && function_exists('wc_get_order')) {
            $order = wc_get_order($params['order_id']);
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
        if (!is_dir($this->cache_dir)) {
            return;
        }

        $files = glob($this->cache_dir . '*');
        $now = time();
        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > $this->max_cache_age) {
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
}
