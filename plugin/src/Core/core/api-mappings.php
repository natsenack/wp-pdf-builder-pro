<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed
/**
 * PDF Builder API Mappings
 *
 * Centralise toutes les configurations et mappings d'API
 */

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_API_Mappings {

    // ==========================================
    // ENDPOINTS API
    // ==========================================

    private static $api_endpoints = [
        'templates' => [
            'list' => '/wp-json/pdf-builder/v1/templates',
            'create' => '/wp-json/pdf-builder/v1/templates',
            'read' => '/wp-json/pdf-builder/v1/templates/{id}',
            'update' => '/wp-json/pdf-builder/v1/templates/{id}',
            'delete' => '/wp-json/pdf-builder/v1/templates/{id}',
            'duplicate' => '/wp-json/pdf-builder/v1/templates/{id}/duplicate',
            'export' => '/wp-json/pdf-builder/v1/templates/{id}/export'
        ],

        'elements' => [
            'list' => '/wp-json/pdf-builder/v1/elements',
            'create' => '/wp-json/pdf-builder/v1/elements',
            'read' => '/wp-json/pdf-builder/v1/elements/{id}',
            'update' => '/wp-json/pdf-builder/v1/elements/{id}',
            'delete' => '/wp-json/pdf-builder/v1/elements/{id}',
            'batch_update' => '/wp-json/pdf-builder/v1/elements/batch'
        ],

        'settings' => [
            'get' => '/wp-json/pdf-builder/v1/settings',
            'update' => '/wp-json/pdf-builder/v1/settings',
            'reset' => '/wp-json/pdf-builder/v1/settings/reset'
        ],

        'export' => [
            'pdf' => '/wp-json/pdf-builder/v1/export/pdf',
            'png' => '/wp-json/pdf-builder/v1/export/png',
            'jpg' => '/wp-json/pdf-builder/v1/export/jpg',
            'svg' => '/wp-json/pdf-builder/v1/export/svg',
            'batch' => '/wp-json/pdf-builder/v1/export/batch'
        ],

        'upload' => [
            'image' => '/wp-json/pdf-builder/v1/upload/image',
            'font' => '/wp-json/pdf-builder/v1/upload/font',
            'template' => '/wp-json/pdf-builder/v1/upload/template'
        ],

        'fonts' => [
            'list' => '/wp-json/pdf-builder/v1/fonts',
            'google_fonts' => '/wp-json/pdf-builder/v1/fonts/google',
            'system_fonts' => '/wp-json/pdf-builder/v1/fonts/system',
            'custom_fonts' => '/wp-json/pdf-builder/v1/fonts/custom'
        ],

        'preview' => [
            'generate' => '/wp-json/pdf-builder/v1/preview/generate',
            'get' => '/wp-json/pdf-builder/v1/preview/{id}',
            'delete' => '/wp-json/pdf-builder/v1/preview/{id}'
        ],

        'analytics' => [
            'stats' => '/wp-json/pdf-builder/v1/analytics/stats',
            'usage' => '/wp-json/pdf-builder/v1/analytics/usage',
            'performance' => '/wp-json/pdf-builder/v1/analytics/performance'
        ]
    ];

    // ==========================================
    // SCHÉMAS DE DONNÉES API
    // ==========================================

    private static $api_schemas = [
        'template' => [
            'id' => ['type' => 'integer', 'required' => false],
            'name' => ['type' => 'string', 'required' => true, 'max_length' => 100],
            'description' => ['type' => 'string', 'required' => false, 'max_length' => 500],
            'content' => ['type' => 'object', 'required' => true],
            'settings' => ['type' => 'object', 'required' => false],
            'thumbnail' => ['type' => 'string', 'required' => false],
            'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'archived'], 'required' => false],
            'user_id' => ['type' => 'integer', 'required' => false],
            'created_at' => ['type' => 'string', 'format' => 'date-time', 'required' => false],
            'updated_at' => ['type' => 'string', 'format' => 'date-time', 'required' => false]
        ],

        'element' => [
            'id' => ['type' => 'integer', 'required' => false],
            'template_id' => ['type' => 'integer', 'required' => true],
            'type' => ['type' => 'string', 'enum' => ['text', 'image', 'shape', 'line'], 'required' => true],
            'properties' => ['type' => 'object', 'required' => true],
            'layer' => ['type' => 'integer', 'required' => false],
            'z_index' => ['type' => 'integer', 'required' => false],
            'visible' => ['type' => 'boolean', 'required' => false],
            'locked' => ['type' => 'boolean', 'required' => false],
            'created_at' => ['type' => 'string', 'format' => 'date-time', 'required' => false],
            'updated_at' => ['type' => 'string', 'format' => 'date-time', 'required' => false]
        ],

        'settings' => [
            'canvas' => ['type' => 'object', 'required' => false],
            'export' => ['type' => 'object', 'required' => false],
            'performance' => ['type' => 'object', 'required' => false],
            'security' => ['type' => 'object', 'required' => false],
            'ui' => ['type' => 'object', 'required' => false]
        ],

        'export_request' => [
            'template_id' => ['type' => 'integer', 'required' => true],
            'format' => ['type' => 'string', 'enum' => ['pdf', 'png', 'jpg', 'svg'], 'required' => true],
            'quality' => ['type' => 'integer', 'min' => 1, 'max' => 100, 'required' => false],
            'pages' => ['type' => 'array', 'items' => ['type' => 'integer'], 'required' => false],
            'options' => ['type' => 'object', 'required' => false]
        ]
    ];

    // ==========================================
    // CODES DE RÉPONSE API
    // ==========================================

    private static $api_response_codes = [
        'success' => [
            'code' => 200,
            'message' => 'Success',
            'description' => 'The request was successful'
        ],

        'created' => [
            'code' => 201,
            'message' => 'Created',
            'description' => 'The resource was successfully created'
        ],

        'accepted' => [
            'code' => 202,
            'message' => 'Accepted',
            'description' => 'The request has been accepted for processing'
        ],

        'no_content' => [
            'code' => 204,
            'message' => 'No Content',
            'description' => 'The request was successful but there is no content to return'
        ],

        'bad_request' => [
            'code' => 400,
            'message' => 'Bad Request',
            'description' => 'The request could not be understood or was missing required parameters'
        ],

        'unauthorized' => [
            'code' => 401,
            'message' => 'Unauthorized',
            'description' => 'Authentication is required and has failed or not been provided'
        ],

        'forbidden' => [
            'code' => 403,
            'message' => 'Forbidden',
            'description' => 'The request is understood but has been refused'
        ],

        'not_found' => [
            'code' => 404,
            'message' => 'Not Found',
            'description' => 'The requested resource could not be found'
        ],

        'method_not_allowed' => [
            'code' => 405,
            'message' => 'Method Not Allowed',
            'description' => 'The request method is not supported for the requested resource'
        ],

        'conflict' => [
            'code' => 409,
            'message' => 'Conflict',
            'description' => 'The request conflicts with the current state of the resource'
        ],

        'unprocessable_entity' => [
            'code' => 422,
            'message' => 'Unprocessable Entity',
            'description' => 'The request was well-formed but contains semantic errors'
        ],

        'too_many_requests' => [
            'code' => 429,
            'message' => 'Too Many Requests',
            'description' => 'The user has sent too many requests in a given amount of time'
        ],

        'internal_server_error' => [
            'code' => 500,
            'message' => 'Internal Server Error',
            'description' => 'An unexpected error occurred on the server'
        ],

        'not_implemented' => [
            'code' => 501,
            'message' => 'Not Implemented',
            'description' => 'The server does not support the functionality required to fulfill the request'
        ],

        'service_unavailable' => [
            'code' => 503,
            'message' => 'Service Unavailable',
            'description' => 'The server is temporarily unable to handle the request'
        ]
    ];

    // ==========================================
    // AUTHENTIFICATION API
    // ==========================================

    private static $api_authentication = [
        'methods' => [
            'oauth2' => [
                'enabled' => true,
                'token_endpoint' => '/wp-json/pdf-builder/v1/oauth2/token',
                'authorization_endpoint' => '/wp-json/pdf-builder/v1/oauth2/authorize',
                'scopes' => ['read', 'write', 'admin']
            ],

            'api_key' => [
                'enabled' => true,
                'header_name' => 'X-API-Key',
                'param_name' => 'api_key'
            ],

            'basic_auth' => [
                'enabled' => false,
                'realm' => 'PDF Builder API'
            ]
        ],

        'rate_limiting' => [
            'enabled' => true,
            'limits' => [
                'anonymous' => ['requests' => 60, 'window' => 60], // 60 requests per minute
                'authenticated' => ['requests' => 1000, 'window' => 60], // 1000 requests per minute
                'admin' => ['requests' => 5000, 'window' => 60] // 5000 requests per minute
            ]
        ],

        'cors' => [
            'enabled' => true,
            'allowed_origins' => ['*'],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allowed_headers' => ['Content-Type', 'Authorization', 'X-API-Key'],
            'max_age' => 86400
        ]
    ];

    // ==========================================
    // VERSIONNEMENT API
    // ==========================================

    private static $api_versioning = [
        'current_version' => 'v1',
        'supported_versions' => ['v1'],
        'deprecated_versions' => [],
        'version_header' => 'X-API-Version',
        'version_param' => 'version',

        'changelog' => [
            'v1' => [
                'release_date' => '2024-01-01',
                'changes' => [
                    'Initial API release',
                    'Basic CRUD operations for templates and elements',
                    'Export functionality',
                    'Settings management'
                ]
            ]
        ]
    ];

    // ==========================================
    // WEBHOOKS
    // ==========================================

    private static $webhooks = [
        'events' => [
            'template.created' => 'Triggered when a new template is created',
            'template.updated' => 'Triggered when a template is updated',
            'template.deleted' => 'Triggered when a template is deleted',
            'element.created' => 'Triggered when a new element is created',
            'element.updated' => 'Triggered when an element is updated',
            'element.deleted' => 'Triggered when an element is deleted',
            'export.completed' => 'Triggered when an export is completed',
            'export.failed' => 'Triggered when an export fails'
        ],

        'endpoints' => [
            'register' => '/wp-json/pdf-builder/v1/webhooks',
            'list' => '/wp-json/pdf-builder/v1/webhooks',
            'delete' => '/wp-json/pdf-builder/v1/webhooks/{id}',
            'test' => '/wp-json/pdf-builder/v1/webhooks/{id}/test'
        ],

        'security' => [
            'signature_header' => 'X-PDF-Builder-Signature',
            'signature_algorithm' => 'sha256',
            'max_retries' => 3,
            'timeout' => 30
        ]
    ];

    // ==========================================
    // MÉTHODES D'ACCÈS
    // ==========================================

    /**
     * Obtenir tous les endpoints API
     */
    public static function get_api_endpoints() {
        return self::$api_endpoints;
    }

    /**
     * Obtenir un endpoint API spécifique
     */
    public static function get_api_endpoint($category, $action) {
        return self::$api_endpoints[$category][$action] ?? null;
    }

    /**
     * Obtenir l'URL complète d'un endpoint
     */
    public static function get_full_endpoint_url($category, $action, $params = []) {
        $endpoint = self::get_api_endpoint($category, $action);

        if (!$endpoint) {
            return null;
        }

        // Remplacer les paramètres dans l'URL
        foreach ($params as $key => $value) {
            $endpoint = str_replace('{' . $key . '}', $value, $endpoint);
        }

        return get_site_url() . $endpoint;
    }

    /**
     * Obtenir les schémas de données API
     */
    public static function get_api_schemas() {
        return self::$api_schemas;
    }

    /**
     * Obtenir le schéma pour un type de données
     */
    public static function get_api_schema($type) {
        return self::$api_schemas[$type] ?? null;
    }

    /**
     * Obtenir les codes de réponse API
     */
    public static function get_api_response_codes() {
        return self::$api_response_codes;
    }

    /**
     * Obtenir un code de réponse spécifique
     */
    public static function get_api_response_code($key) {
        return self::$api_response_codes[$key] ?? null;
    }

    /**
     * Obtenir la configuration d'authentification
     */
    public static function get_api_authentication() {
        return self::$api_authentication;
    }

    /**
     * Obtenir la configuration de versionnement
     */
    public static function get_api_versioning() {
        return self::$api_versioning;
    }

    /**
     * Obtenir la configuration des webhooks
     */
    public static function get_webhooks() {
        return self::$webhooks;
    }

    /**
     * Valider les données selon un schéma
     */
    public static function validate_api_data($data, $schema_type) {
        $schema = self::get_api_schema($schema_type);

        if (!$schema) {
            return ['valid' => false, 'errors' => ['Unknown schema type']];
        }

        $errors = [];

        foreach ($schema as $field => $rules) {
            $value = $data[$field] ?? null;

            // Vérifier les champs requis
            if ($rules['required'] && (is_null($value) || $value === '')) {
                $errors[] = "Field '{$field}' is required";
                continue;
            }

            // Si non requis et vide, passer au suivant
            if (!$rules['required'] && (is_null($value) || $value === '')) {
                continue;
            }

            // Validation par type
            switch ($rules['type']) {
                case 'string':
                    if (!is_string($value)) {
                        $errors[] = "Field '{$field}' must be a string";
                    } elseif (isset($rules['max_length']) && strlen($value) > $rules['max_length']) {
                        $errors[] = "Field '{$field}' exceeds maximum length of {$rules['max_length']}";
                    } elseif (isset($rules['enum']) && !in_array($value, $rules['enum'])) {
                        $errors[] = "Field '{$field}' must be one of: " . implode(', ', $rules['enum']);
                    }
                    break;

                case 'integer':
                    if (!is_numeric($value) || intval($value) != $value) {
                        $errors[] = "Field '{$field}' must be an integer";
                    } elseif (isset($rules['min']) && $value < $rules['min']) {
                        $errors[] = "Field '{$field}' must be at least {$rules['min']}";
                    } elseif (isset($rules['max']) && $value > $rules['max']) {
                        $errors[] = "Field '{$field}' must be at most {$rules['max']}";
                    }
                    break;

                case 'boolean':
                    if (!is_bool($value) && !in_array($value, [0, 1, '0', '1', 'true', 'false'], true)) {
                        $errors[] = "Field '{$field}' must be a boolean";
                    }
                    break;

                case 'object':
                    if (!is_array($value) && !is_object($value)) {
                        $errors[] = "Field '{$field}' must be an object";
                    }
                    break;

                case 'array':
                    if (!is_array($value)) {
                        $errors[] = "Field '{$field}' must be an array";
                    }
                    break;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Générer une réponse API standardisée
     */
    public static function create_api_response($data = null, $status = 'success', $message = null, $errors = []) {
        $response_code = self::get_api_response_code($status);

        $response = [
            'status' => $status,
            'code' => $response_code ? $response_code['code'] : 200,
            'message' => $message ?: ($response_code ? $response_code['message'] : 'Unknown status'),
            'timestamp' => current_time('mysql')
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return $response;
    }

    /**
     * Vérifier les limites de taux pour une requête API
     */
    public static function check_rate_limit($user_id = null, $endpoint = null) {
        $auth_config = self::$api_authentication['rate_limiting'];

        if (!$auth_config['enabled']) {
            return ['allowed' => true];
        }

        $user_type = 'anonymous';
        if ($user_id) {
            $user_type = current_user_can('manage_options') ? 'admin' : 'authenticated';
        }

        $limits = $auth_config['limits'][$user_type];

        // Ici, vous implémenteriez la logique réelle de vérification des limites
        // Pour l'exemple, on retourne toujours autorisé
        return [
            'allowed' => true,
            'limit' => $limits['requests'],
            'remaining' => $limits['requests'] - 1,
            'reset_time' => time() + $limits['window']
        ];
    }
}



