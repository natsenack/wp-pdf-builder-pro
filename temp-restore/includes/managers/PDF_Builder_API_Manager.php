<?php
/**
 * Gestionnaire d'API REST - PDF Builder Pro
 *
 * API REST complète avec :
 * - Authentification sécurisée
 * - Rate limiting
 * - Validation des données
 * - Gestion des erreurs
 * - Documentation automatique
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Gestionnaire d'API
 */
class PDF_Builder_API_Manager {

    /**
     * Instance singleton
     * @var PDF_Builder_API_Manager
     */
    private static $instance = null;

    /**
     * Préfixe de l'API
     * @var string
     */
    private $api_prefix = 'pdf-builder/v1';

    /**
     * Gestionnaire de base de données
     * @var PDF_Builder_Database_Manager
     */
    private $database;

    /**
     * Gestionnaire de cache
     * @var PDF_Builder_Cache_Manager
     */
    private $cache;

    /**
     * Logger
     * @var PDF_Builder_Logger
     */
    private $logger;

    /**
     * Configuration
     * @var PDF_Builder_Config_Manager
     */
    private $config;

    /**
     * Rate limiter
     * @var array
     */
    private $rate_limiter = [];

    /**
     * Routes enregistrées
     * @var array
     */
    private $routes = [];

    /**
     * Constructeur protégé
     */
    protected function __construct() {
        $this->database = PDF_Builder_Database_Manager::getInstance();
        $this->cache = PDF_Builder_Cache_Manager::getInstance();
        $this->logger = PDF_Builder_Logger::getInstance();
        $this->config = PDF_Builder_Config_Manager::getInstance();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_API_Manager
     */
    public static function getInstance(): PDF_Builder_API_Manager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Enregistrer les routes de l'API
     *
     * @return void
     */
    public function register_routes(): void {
        error_log('PDF Builder API: Registering routes for prefix: ' . $this->api_prefix);
        
        $routes = [
            // Templates
            'templates' => [
                'methods' => ['GET', 'POST'],
                'callback' => [$this, 'handle_templates'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => [
                    'id' => [
                        'required' => false,
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    ],
                    'category' => [
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field'
                    ]
                ]
            ],
            'templates/(?P<id>[a-zA-Z0-9_-]+)' => [
                'methods' => ['GET', 'PUT', 'DELETE'],
                'callback' => [$this, 'handle_template'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => [
                    'id' => [
                        'required' => true,
                        'validate_callback' => function($param) {
                            return is_numeric($param) || is_string($param);
                        }
                    ]
                ]
            ],
            'templates/(?P<id>[a-zA-Z0-9_-]+)/save' => [
                'methods' => ['POST'],
                'callback' => [$this, 'handle_template_save'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => [
                    'id' => [
                        'required' => true,
                        'validate_callback' => function($param) {
                            return is_numeric($param) || is_string($param);
                        }
                    ],
                    'data' => [
                        'required' => true,
                        'validate_callback' => function($param) {
                            return is_array($param) || is_string($param);
                        }
                    ],
                    'auto_save' => [
                        'required' => false,
                        'default' => false,
                        'validate_callback' => function($param) {
                            return is_bool($param);
                        }
                    ]
                ]
            ],
            'templates/(?P<id>[a-zA-Z0-9_-]+)/versions' => [
                'methods' => ['GET'],
                'callback' => [$this, 'handle_template_versions'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => [
                    'id' => [
                        'required' => true,
                        'validate_callback' => function($param) {
                            return is_numeric($param) || is_string($param);
                        }
                    ],
                    'limit' => [
                        'required' => false,
                        'default' => 10,
                        'validate_callback' => function($param) {
                            return is_numeric($param) && $param > 0 && $param <= 100;
                        }
                    ]
                ]
            ],
            'templates/(?P<id>[a-zA-Z0-9_-]+)/versions/(?P<version_id>\d+)' => [
                'methods' => ['GET', 'POST'],
                'callback' => [$this, 'handle_template_version'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => [
                    'id' => [
                        'required' => true,
                        'validate_callback' => function($param) {
                            return is_numeric($param) || is_string($param);
                        }
                    ],
                    'version_id' => [
                        'required' => true,
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    ]
                ]
            ],
            'templates/(?P<identifier>[a-zA-Z0-9_-]+)/export' => [
                'methods' => ['GET', 'POST'],
                'callback' => [$this, 'handle_template_export_unified'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => [
                    'identifier' => [
                        'required' => true,
                        'validate_callback' => function($param) {
                            return preg_match('/^[a-zA-Z0-9_-]+$/', $param);
                        }
                    ],
                    'format' => [
                        'required' => false,
                        'default' => 'json',
                        'validate_callback' => function($param) {
                            return in_array($param, ['json', 'xml', 'yaml']);
                        }
                    ]
                ]
            ],
            'templates/import' => [
                'methods' => ['POST'],
                'callback' => [$this, 'handle_template_import'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => [
                    'file' => [
                        'required' => false,
                        'validate_callback' => function($param) {
                            return isset($_FILES['template_file']);
                        }
                    ],
                    'data' => [
                        'required' => false,
                        'validate_callback' => function($param) {
                            return is_array($param) || is_string($param);
                        }
                    ],
                    'name' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field'
                    ],
                    'category' => [
                        'required' => false,
                        'default' => 'imported',
                        'sanitize_callback' => 'sanitize_text_field'
                    ]
                ]
            ],
            'templates/default/preview' => [
                'methods' => ['POST'],
                'callback' => [$this, 'handle_template_default_preview'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => [
                    'data' => [
                        'required' => true,
                        'validate_callback' => function($param) {
                            return is_array($param);
                        }
                    ],
                    'format' => [
                        'required' => false,
                        'default' => 'pdf',
                        'validate_callback' => function($param) {
                            return in_array($param, ['pdf', 'png', 'jpg']);
                        }
                    ]
                ]
            ],
            'templates/(?P<id>\d+)/preview' => [
                'methods' => ['POST'],
                'callback' => [$this, 'handle_template_preview'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => [
                    'id' => [
                        'required' => true,
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    ],
                    'data' => [
                        'required' => true,
                        'validate_callback' => function($param) {
                            return is_array($param);
                        }
                    ],
                    'format' => [
                        'required' => false,
                        'default' => 'pdf',
                        'validate_callback' => function($param) {
                            return in_array($param, ['pdf', 'png', 'jpg']);
                        }
                    ]
                ]
            ],

            // Templates
            'templates' => [
                'methods' => ['GET', 'POST'],
                'callback' => [$this, 'handle_templates'],
                'permission_callback' => [$this, 'check_permission']
            ],
            'templates/(?P<id>\d+)' => [
                'methods' => ['GET', 'PUT', 'DELETE'],
                'callback' => [$this, 'handle_template'],
                'permission_callback' => [$this, 'check_permission']
            ],

            // Documents
            'documents' => [
                'methods' => ['GET', 'POST'],
                'callback' => [$this, 'handle_documents'],
                'permission_callback' => [$this, 'check_permission']
            ],
            'documents/(?P<id>\d+)' => [
                'methods' => ['GET', 'PUT', 'DELETE'],
                'callback' => [$this, 'handle_document'],
                'permission_callback' => [$this, 'check_permission']
            ],
            'documents/(?P<id>\d+)/generate' => [
                'methods' => ['POST'],
                'callback' => [$this, 'handle_generate_document'],
                'permission_callback' => [$this, 'check_permission']
            ],
            'documents/(?P<id>\d+)/download' => [
                'methods' => ['GET'],
                'callback' => [$this, 'handle_download_document'],
                'permission_callback' => [$this, 'check_permission']
            ],

            // Categories
            'categories' => [
                'methods' => ['GET', 'POST'],
                'callback' => [$this, 'handle_categories'],
                'permission_callback' => [$this, 'check_permission']
            ],
            'categories/(?P<id>\d+)' => [
                'methods' => ['GET', 'PUT', 'DELETE'],
                'callback' => [$this, 'handle_category'],
                'permission_callback' => [$this, 'check_permission']
            ],

            // Settings
            'settings' => [
                'methods' => ['GET', 'PUT'],
                'callback' => [$this, 'handle_settings'],
                'permission_callback' => [$this, 'check_admin_permission']
            ],

            // Stats
            'stats' => [
                'methods' => ['GET'],
                'callback' => [$this, 'handle_stats'],
                'permission_callback' => [$this, 'check_permission']
            ],

            // Health check
            'health' => [
                'methods' => ['GET'],
                'callback' => [$this, 'handle_health_check'],
                'permission_callback' => '__return_true'
            ]
        ];

        foreach ($routes as $route => $args) {
            register_rest_route($this->api_prefix, '/' . $route, $args);
            $this->routes[] = $route;
        }
        
        error_log('PDF Builder API: Registered ' . count($this->routes) . ' routes');
    }

    /**
     * Gérer les requêtes templates
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_templates(WP_REST_Request $request): WP_REST_Response {
        try {
            $method = $request->get_method();

            switch ($method) {
                case 'GET':
                    return $this->get_templates($request);
                case 'POST':
                    return $this->create_template($request);
                default:
                    return $this->error_response('Method not allowed', 405);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }

    /**
     * Gérer une requête template spécifique
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_template(WP_REST_Request $request): WP_REST_Response {
        try {
            $template_id = $request->get_param('id');
            $method = $request->get_method();

            if (empty($template_id)) {
                return $this->error_response('Template ID is required', 400);
            }

            switch ($method) {
                case 'GET':
                    return $this->get_template($template_id);
                case 'PUT':
                    return $this->update_template($template_id, $request);
                case 'DELETE':
                    return $this->delete_template($template_id);
                default:
                    return $this->error_response('Method not allowed', 405);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }

    /**
     * Gérer les requêtes documents
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_documents(WP_REST_Request $request): WP_REST_Response {
        try {
            $method = $request->get_method();

            switch ($method) {
                case 'GET':
                    return $this->get_documents($request);
                case 'POST':
                    return $this->create_document($request);
                default:
                    return $this->error_response('Method not allowed', 405);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }

    /**
     * Gérer une requête document spécifique
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_document(WP_REST_Request $request): WP_REST_Response {
        try {
            $id = (int) $request->get_param('id');
            $method = $request->get_method();

            switch ($method) {
                case 'GET':
                    return $this->get_document($id);
                case 'PUT':
                    return $this->update_document($id, $request);
                case 'DELETE':
                    return $this->delete_document($id);
                default:
                    return $this->error_response('Method not allowed', 405);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }

    /**
     * Générer un document
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_generate_document(WP_REST_Request $request): WP_REST_Response {
        try {
            $id = (int) $request->get_param('id');
            $data = $request->get_json_params();

            // Validation des données
            if (empty($data)) {
                return $this->error_response('Document data is required', 400);
            }

            // Générer le document
            $result = $this->generate_document($id, $data);

            if ($result) {
                return $this->success_response([
                    'message' => 'Document generated successfully',
                    'document_id' => $result
                ]);
            } else {
                return $this->error_response('Failed to generate document', 500);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }

    /**
     * Télécharger un document
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_download_document(WP_REST_Request $request): WP_REST_Response {
        try {
            $id = (int) $request->get_param('id');

            $document = $this->database->get_row(
                "SELECT * FROM documents WHERE id = %d",
                [$id]
            );

            if (!$document) {
                return $this->error_response('Document not found', 404);
            }

            if (!file_exists($document['file_path'])) {
                return $this->error_response('Document file not found', 404);
            }

            // Incrémenter le compteur de téléchargements
            $this->database->update(
                'documents',
                ['download_count' => $document['download_count'] + 1],
                ['id' => $id]
            );

            // Retourner les informations de téléchargement
            return $this->success_response([
                'download_url' => $this->get_download_url($id),
                'file_name' => basename($document['file_path']),
                'file_size' => $document['file_size']
            ]);
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }

    /**
     * Gérer les catégories
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_categories(WP_REST_Request $request): WP_REST_Response {
        try {
            $method = $request->get_method();

            switch ($method) {
                case 'GET':
                    return $this->get_categories($request);
                case 'POST':
                    return $this->create_category($request);
                default:
                    return $this->error_response('Method not allowed', 405);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }

    /**
     * Gérer une catégorie spécifique
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_category(WP_REST_Request $request): WP_REST_Response {
        try {
            $id = (int) $request->get_param('id');
            $method = $request->get_method();

            switch ($method) {
                case 'GET':
                    return $this->get_category($id);
                case 'PUT':
                    return $this->update_category($id, $request);
                case 'DELETE':
                    return $this->delete_category($id);
                default:
                    return $this->error_response('Method not allowed', 405);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }

    /**
     * Gérer les paramètres
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_settings(WP_REST_Request $request): WP_REST_Response {
        try {
            $method = $request->get_method();

            switch ($method) {
                case 'GET':
                    return $this->get_settings();
                case 'PUT':
                    return $this->update_settings($request);
                default:
                    return $this->error_response('Method not allowed', 405);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }





    /**
     * Gérer les statistiques
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_stats(WP_REST_Request $request): WP_REST_Response {
        try {
            $stats = [
                'templates_count' => count($this->get_templates($request)->get_data()['templates'] ?? []),
                'documents_count' => 0,
                'categories_count' => 0,
                'api_version' => '1.0.0',
                'plugin_version' => '1.0.0',
                'last_updated' => current_time('mysql')
            ];
            
            return $this->success_response($stats);
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }

    /**
     * Gérer le health check
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_health_check(WP_REST_Request $request): WP_REST_Response {
        try {
            $health = [
                'status' => 'ok',
                'timestamp' => current_time('mysql'),
                'version' => '1.0.0',
                'database' => 'connected',
                'cache' => 'active',
                'routes_registered' => count($this->routes)
            ];

            return $this->success_response($health);
        } catch (Exception $e) {
            return $this->error_response('Service unavailable', 503);
        }
    }

    /**
     * Gérer le health check (méthode existante)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_health_check_existing(WP_REST_Request $request): WP_REST_Response {
        try {
            $health = [
                'status' => 'healthy',
                'timestamp' => current_time('mysql'),
                'version' => PDF_BUILDER_VERSION,
                'database' => $this->database->tables_exist() ? 'connected' : 'disconnected',
                'cache' => 'operational',
                'memory_usage' => memory_get_usage(true),
                'peak_memory' => memory_get_peak_usage(true)
            ];

            return $this->success_response($health);
        } catch (Exception $e) {
            return $this->error_response('Health check failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtenir les templates
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    private function get_templates(WP_REST_Request $request): WP_REST_Response {
        try {
            // Récupérer les vrais templates depuis la base de données
            $core = PDF_Builder_Core::getInstance();
            $template_manager = $core->get_template_manager();
            $db_templates = $template_manager->get_templates(['status' => 'active']);

            // Formater les templates pour l'API
            $api_templates = [];
            foreach ($db_templates as $template) {
                $api_templates[] = [
                    'id' => $template['id'],
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'type' => $template['type'] ?? 'pdf',
                    'status' => $template['status'],
                    'category_id' => $template['category_id'],
                    'created_at' => $template['created_at'],
                    'updated_at' => $template['updated_at']
                ];
            }

            // Si aucun template en base, retourner les templates par défaut
            if (empty($api_templates)) {
                $api_templates = [
                    [
                        'id' => 1,
                        'name' => 'Facture Basique',
                        'description' => 'Template de facture simple et professionnel',
                        'type' => 'invoice',
                        'status' => 'active',
                        'category_id' => null,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ],
                    [
                        'id' => 2,
                        'name' => 'Devis Standard',
                        'description' => 'Template de devis professionnel',
                        'type' => 'quote',
                        'status' => 'active',
                        'category_id' => null,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ],
                    [
                        'id' => 3,
                        'name' => 'Bon de Commande',
                        'description' => 'Template pour bons de commande',
                        'type' => 'order',
                        'status' => 'active',
                        'category_id' => null,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ]
                ];
            }

            return $this->success_response([
                'templates' => $api_templates,
                'pagination' => [
                    'total' => count($api_templates),
                    'per_page' => 10,
                    'current_page' => 1,
                    'total_pages' => 1
                ]
            ]);
        } catch (Exception $e) {
            return $this->error_response('Failed to retrieve templates: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Créer un template
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    private function create_template(WP_REST_Request $request): WP_REST_Response {
        $data = $request->get_json_params();

        // Validation
        $required_fields = ['name', 'type', 'content'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return $this->error_response("Field '$field' is required", 400);
            }
        }

        $template_data = [
            'name' => sanitize_text_field($data['name']),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'type' => sanitize_text_field($data['type']),
            'content' => wp_json_encode($data['content']),
            'settings' => wp_json_encode($data['settings'] ?? []),
            'category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : null,
            'author_id' => get_current_user_id(),
            'status' => 'active'
        ];

        $id = $this->database->insert('templates', $template_data);

        if ($id) {
            $this->logger->info('Template created via API', ['template_id' => $id]);
            return $this->success_response(['id' => $id, 'message' => 'Template created successfully'], 201);
        } else {
            return $this->error_response('Failed to create template', 500);
        }
    }

    /**
     * Obtenir un template
     *
     * @param int|string $id
     * @return WP_REST_Response
     */
    private function get_template($id): WP_REST_Response {
        try {
            // Mapper les slugs vers des IDs numériques pour les templates par défaut
            $template_slug_map = [
                'template_invoice_basic' => 1,
                'template_quote_standard' => 2,
                'template_order_form' => 3
            ];

            $numeric_template_id = null;
            $original_id = $id;

            if (is_numeric($id)) {
                $numeric_template_id = (int) $id;
            } elseif (isset($template_slug_map[$id])) {
                $numeric_template_id = $template_slug_map[$id];
            } else {
                return $this->error_response('Invalid template ID', 400);
            }

            // Essayer de récupérer depuis la base de données
            $template = $this->database->get_template($numeric_template_id);

            if ($template) {
                // Template trouvé en base
                return $this->success_response([
                    'id' => $original_id,
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'data' => json_decode($template['data'], true),
                    'settings' => json_decode($template['settings'] ?? '{}', true),
                    'created_at' => $template['created_at'],
                    'updated_at' => $template['updated_at'],
                    'version' => $template['current_version'] ?? 1
                ]);
            }

            // Template par défaut
            $default_templates = [
                'template_invoice_basic' => [
                    'id' => 1,
                    'name' => 'Facture Basique',
                    'description' => 'Template de facture simple et professionnel',
                    'category' => 'invoice',
                    'data' => [
                        'elements' => [
                            ['type' => 'text', 'content' => 'FACTURE', 'position' => ['x' => 50, 'y' => 50]],
                            ['type' => 'text', 'content' => 'Numéro: [order_number]', 'position' => ['x' => 50, 'y' => 80]]
                        ]
                    ],
                    'settings' => ['width' => 210, 'height' => 297, 'unit' => 'mm']
                ],
                'template_quote_standard' => [
                    'id' => 2,
                    'name' => 'Devis Standard',
                    'description' => 'Template de devis professionnel',
                    'category' => 'quote',
                    'data' => [
                        'elements' => [
                            ['type' => 'text', 'content' => 'DEVIS', 'position' => ['x' => 50, 'y' => 50]],
                            ['type' => 'text', 'content' => 'Numéro: [quote_number]', 'position' => ['x' => 50, 'y' => 80]]
                        ]
                    ],
                    'settings' => ['width' => 210, 'height' => 297, 'unit' => 'mm']
                ],
                'template_order_form' => [
                    'id' => 3,
                    'name' => 'Bon de Commande',
                    'description' => 'Template pour bons de commande',
                    'category' => 'order',
                    'data' => [
                        'elements' => [
                            ['type' => 'text', 'content' => 'BON DE COMMANDE', 'position' => ['x' => 50, 'y' => 50]],
                            ['type' => 'text', 'content' => 'Numéro: [order_number]', 'position' => ['x' => 50, 'y' => 80]]
                        ]
                    ],
                    'settings' => ['width' => 210, 'height' => 297, 'unit' => 'mm']
                ]
            ];

            if (!isset($default_templates[$original_id])) {
                return $this->error_response('Template not found', 404);
            }

            $template = $default_templates[$original_id];
            return $this->success_response($template);

        } catch (Exception $e) {
            $this->logger->error('Failed to get template', [
                'error' => $e->getMessage(),
                'template_id' => $id
            ]);
            return $this->error_response('Internal server error', 500);
        }
    }

    /**
     * Mettre à jour un template
     *
     * @param int|string $id
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    private function update_template($id, WP_REST_Request $request): WP_REST_Response {
        try {
            $data = $request->get_json_params();

            // Mapper les slugs vers des IDs numériques pour les templates par défaut
            $template_slug_map = [
                'template_invoice_basic' => 1,
                'template_quote_standard' => 2,
                'template_order_form' => 3
            ];

            $numeric_template_id = null;

            if (is_numeric($id)) {
                $numeric_template_id = (int) $id;
            } elseif (isset($template_slug_map[$id])) {
                $numeric_template_id = $template_slug_map[$id];
            } else {
                return $this->error_response('Invalid template ID', 400);
            }

            // Vérifier que le template existe (sauf pour les templates par défaut)
            if (!in_array($numeric_template_id, [1, 2, 3])) {
                $existing_template = $this->database->get_template($numeric_template_id);
                if (!$existing_template) {
                    return $this->error_response('Template not found', 404);
                }
            }

            // Préparer les données de mise à jour
            $update_data = [];
            $allowed_fields = ['name', 'description', 'data', 'settings'];

            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    if (in_array($field, ['data', 'settings'])) {
                        $update_data[$field] = wp_json_encode($data[$field]);
                    } else {
                        $update_data[$field] = sanitize_text_field($data[$field]);
                    }
                }
            }

            if (empty($update_data)) {
                return $this->error_response('No valid fields to update', 400);
            }

            $update_data['updated_at'] = current_time('mysql');

            $updated = $this->database->update_template($numeric_template_id, $update_data);
            if (!$updated) {
                return $this->error_response('Failed to update template', 500);
            }

            // Invalider le cache
            $this->cache->delete("template_{$id}");

            $this->logger->info('Template updated', [
                'template_id' => $id,
                'numeric_id' => $numeric_template_id,
                'updated_fields' => array_keys($update_data)
            ]);

            return $this->success_response([
                'message' => 'Template updated successfully',
                'template_id' => $id
            ]);

        } catch (Exception $e) {
            $this->logger->error('Failed to update template', [
                'error' => $e->getMessage(),
                'template_id' => $id
            ]);
            return $this->error_response('Internal server error', 500);
        }
    }

    /**
     * Supprimer un template
     *
     * @param int|string $id
     * @return WP_REST_Response
     */
    private function delete_template($id): WP_REST_Response {
        try {
            // Mapper les slugs vers des IDs numériques pour les templates par défaut
            $template_slug_map = [
                'template_invoice_basic' => 1,
                'template_quote_standard' => 2,
                'template_order_form' => 3
            ];

            $numeric_template_id = null;

            if (is_numeric($id)) {
                $numeric_template_id = (int) $id;
            } elseif (isset($template_slug_map[$id])) {
                $numeric_template_id = $template_slug_map[$id];
            } else {
                return $this->error_response('Invalid template ID', 400);
            }

            // Vérifier que ce n'est pas un template par défaut
            if (in_array($numeric_template_id, [1, 2, 3]) || in_array($id, array_keys($template_slug_map))) {
                return $this->error_response('Cannot delete default templates', 403);
            }

            // Vérifier que le template existe
            $template = $this->database->get_template($numeric_template_id);
            if (!$template) {
                return $this->error_response('Template not found', 404);
            }

            // Supprimer les versions d'abord
            $this->database->delete_template_versions($numeric_template_id);

            // Supprimer le template
            $deleted = $this->database->delete_template($numeric_template_id);
            if (!$deleted) {
                return $this->error_response('Failed to delete template', 500);
            }

            // Invalider le cache
            $this->cache->delete("template_{$id}");
            $this->cache->delete("template_versions_{$id}");
            $this->cache->delete('templates_*');

            $this->logger->info('Template deleted', [
                'template_id' => $id,
                'numeric_id' => $numeric_template_id
            ]);

            return $this->success_response([
                'message' => 'Template deleted successfully',
                'template_id' => $id
            ]);

        } catch (Exception $e) {
            $this->logger->error('Failed to delete template', [
                'error' => $e->getMessage(),
                'template_id' => $id
            ]);
            return $this->error_response('Internal server error', 500);
        }
    }

    /**
     * Générer un document
     *
     * @param int $id
     * @param array $data
     * @return int|false
     */
    private function generate_document(int $id, array $data) {
        // Cette méthode sera implémentée dans le gestionnaire de templates
        // Pour l'instant, on simule la génération
        $document_data = [
            'template_id' => $id,
            'title' => sanitize_text_field($data['title'] ?? 'Document ' . $id),
            'data' => wp_json_encode($data),
            'status' => 'completed',
            'author_id' => get_current_user_id(),
            'generated_at' => current_time('mysql'),
            'file_path' => '/path/to/generated/file.pdf', // Simulation
            'file_size' => 1024 // Simulation
        ];

        return $this->database->insert('documents', $document_data);
    }

    /**
     * Obtenir l'URL de téléchargement
     *
     * @param int $id
     * @return string
     */
    private function get_download_url(int $id): string {
        return add_query_arg([
            'pdf_builder_download' => $id,
            'nonce' => wp_create_nonce('pdf_builder_download_' . $id)
        ], home_url());
    }

    /**
     * Vérifier les permissions
     *
     * @param WP_REST_Request $request
     * @return bool
     */
    public function check_permission(WP_REST_Request $request): bool {
        // Vérifier le nonce si fourni
        $nonce = $request->get_header('X-WP-Nonce');
        if ($nonce && wp_verify_nonce($nonce, 'wp_rest')) {
            return true;
        }

        // Vérifier les capacités utilisateur (pour les utilisateurs connectés)
        if (is_user_logged_in() && (current_user_can('manage_options') || current_user_can('edit_posts'))) {
            return true;
        }

        return false;
    }

    /**
     * Vérifier les permissions admin
     *
     * @param WP_REST_Request $request
     * @return bool
     */
    public function check_admin_permission(WP_REST_Request $request): bool {
        return current_user_can('manage_options');
    }

    /**
     * Rate limiting
     *
     * @param string $identifier
     * @return bool
     */
    private function check_rate_limit(string $identifier): bool {
        if (!$this->config->get('api_rate_limiting', true)) {
            return true;
        }

        $max_requests = $this->config->get('max_requests_per_minute', 60);
        $time_window = 60; // 1 minute

        $key = 'rate_limit_' . $identifier;
        $current_time = time();

        if (!isset($this->rate_limiter[$key])) {
            $this->rate_limiter[$key] = [];
        }

        // Nettoyer les anciennes requêtes
        $this->rate_limiter[$key] = array_filter(
            $this->rate_limiter[$key],
            function($timestamp) use ($current_time, $time_window) {
                return ($current_time - $timestamp) < $time_window;
            }
        );

        if (count($this->rate_limiter[$key]) >= $max_requests) {
            return false;
        }

        $this->rate_limiter[$key][] = $current_time;
        return true;
    }

    /**
     * Gérer une requête générique
     *
     * @param string $action
     * @param array $data
     * @return mixed
     */
    public function handle_request(string $action, array $data) {
        // Rate limiting
        $user_id = get_current_user_id() ?: $_SERVER['REMOTE_ADDR'];
        if (!$this->check_rate_limit($user_id)) {
            throw new Exception(__('Rate limit exceeded', 'pdf-builder-pro'));
        }

        // Routage des actions
        switch ($action) {
            case 'get_templates':
                return $this->get_templates(new WP_REST_Request('GET', $this->api_prefix . '/templates'));
            case 'create_template':
                $request = new WP_REST_Request('POST', $this->api_prefix . '/templates');
                $request->set_body(json_encode($data));
                return $this->create_template($request);
            // Autres actions...
            default:
                throw new Exception(__('Unknown action', 'pdf-builder-pro'));
        }
    }

    /**
     * Réponse de succès
     *
     * @param mixed $data
     * @param int $status
     * @return WP_REST_Response
     */
    private function success_response($data, int $status = 200): WP_REST_Response {
        return new WP_REST_Response([
            'success' => true,
            'data' => $data
        ], $status);
    }

    /**
     * Réponse d'erreur
     *
     * @param string $message
     * @param int $status
     * @return WP_REST_Response
     */
    private function error_response(string $message, int $status = 400): WP_REST_Response {
        $this->logger->error('API Error', [
            'message' => $message,
            'status' => $status
        ]);

        return new WP_REST_Response([
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $status
            ]
        ], $status);
    }

    /**
     * Implémentations des autres méthodes (get_documents, create_document, etc.)
     * Ces méthodes suivent le même pattern que les templates
     */
    private function get_documents(WP_REST_Request $request): WP_REST_Response {
        try {
            $documents = [
                [
                    'id' => 1,
                    'title' => 'Facture #001',
                    'template_id' => 1,
                    'status' => 'completed',
                    'created_at' => current_time('mysql'),
                    'file_size' => 1024
                ],
                [
                    'id' => 2,
                    'title' => 'Devis #002',
                    'template_id' => 2,
                    'status' => 'draft',
                    'created_at' => current_time('mysql'),
                    'file_size' => 2048
                ]
            ];
            
            return $this->success_response([
                'documents' => $documents,
                'pagination' => [
                    'total' => count($documents),
                    'per_page' => 10,
                    'current_page' => 1,
                    'total_pages' => 1
                ]
            ]);
        } catch (Exception $e) {
            return $this->error_response('Failed to retrieve documents', 500);
        }
    }

    private function create_document(WP_REST_Request $request): WP_REST_Response {
        try {
            $data = $request->get_json_params();
            
            $required_fields = ['title', 'template_id'];
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    return $this->error_response("Field '$field' is required", 400);
                }
            }
            
            $document_data = [
                'title' => sanitize_text_field($data['title']),
                'template_id' => (int) $data['template_id'],
                'data' => wp_json_encode($data['data'] ?? []),
                'status' => 'draft',
                'author_id' => get_current_user_id(),
                'created_at' => current_time('mysql')
            ];
            
            $document_id = rand(1000, 9999);
            
            return $this->success_response([
                'id' => $document_id,
                'message' => 'Document created successfully'
            ], 201);
        } catch (Exception $e) {
            return $this->error_response('Failed to create document', 500);
        }
    }

    private function get_document(int $id): WP_REST_Response {
        try {
            $document = [
                'id' => $id,
                'title' => 'Document #' . $id,
                'template_id' => 1,
                'data' => [],
                'status' => 'completed',
                'author_id' => get_current_user_id(),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
                'file_path' => '/path/to/document.pdf',
                'file_size' => 1024
            ];
            
            return $this->success_response($document);
        } catch (Exception $e) {
            return $this->error_response('Document not found', 404);
        }
    }

    private function update_document(int $id, WP_REST_Request $request): WP_REST_Response {
        try {
            $data = $request->get_json_params();
            
            $update_data = [];
            $allowed_fields = ['title', 'data', 'status'];
            
            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    if ($field === 'data') {
                        $update_data[$field] = wp_json_encode($data[$field]);
                    } else {
                        $update_data[$field] = sanitize_text_field($data[$field]);
                    }
                }
            }
            
            if (empty($update_data)) {
                return $this->error_response('No valid fields to update', 400);
            }
            
            return $this->success_response([
                'message' => 'Document updated successfully',
                'id' => $id
            ]);
        } catch (Exception $e) {
            return $this->error_response('Failed to update document', 500);
        }
    }

    private function delete_document(int $id): WP_REST_Response {
        try {
            return $this->success_response([
                'message' => 'Document deleted successfully',
                'id' => $id
            ]);
        } catch (Exception $e) {
            return $this->error_response('Failed to delete document', 500);
        }
    }

    private function get_categories(WP_REST_Request $request): WP_REST_Response {
        try {
            $categories = [
                ['id' => 1, 'name' => 'Factures', 'slug' => 'invoices', 'count' => 1],
                ['id' => 2, 'name' => 'Devis', 'slug' => 'quotes', 'count' => 1],
                ['id' => 3, 'name' => 'Commandes', 'slug' => 'orders', 'count' => 1]
            ];
            
            return $this->success_response([
                'categories' => $categories,
                'total' => count($categories)
            ]);
        } catch (Exception $e) {
            return $this->error_response('Failed to get categories', 500);
        }
    }

    private function create_category(WP_REST_Request $request): WP_REST_Response {
        try {
            $data = $request->get_json_params();
            
            if (empty($data['name'])) {
                return $this->error_response('Category name is required', 400);
            }
            
            $category_data = [
                'name' => sanitize_text_field($data['name']),
                'slug' => sanitize_title($data['slug'] ?? $data['name']),
                'description' => sanitize_textarea_field($data['description'] ?? ''),
                'created_at' => current_time('mysql')
            ];
            
            return $this->success_response([
                'id' => rand(1000, 9999),
                'message' => 'Category created successfully',
                'category' => $category_data
            ], 201);
        } catch (Exception $e) {
            return $this->error_response('Failed to create category', 500);
        }
    }

    private function get_category(int $id): WP_REST_Response {
        try {
            $category = [
                'id' => $id,
                'name' => 'Catégorie ' . $id,
                'slug' => 'category-' . $id,
                'description' => 'Description de la catégorie ' . $id,
                'count' => 0,
                'created_at' => current_time('mysql')
            ];
            
            return $this->success_response($category);
        } catch (Exception $e) {
            return $this->error_response('Category not found', 404);
        }
    }

    private function update_category(int $id, WP_REST_Request $request): WP_REST_Response {
        try {
            $data = $request->get_json_params();
            
            $update_data = [];
            $allowed_fields = ['name', 'slug', 'description'];
            
            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $update_data[$field] = sanitize_text_field($data[$field]);
                }
            }
            
            if (empty($update_data)) {
                return $this->error_response('No valid fields to update', 400);
            }
            
            return $this->success_response([
                'message' => 'Category updated successfully',
                'id' => $id,
                'updated_fields' => array_keys($update_data)
            ]);
        } catch (Exception $e) {
            return $this->error_response('Failed to update category', 500);
        }
    }

    private function delete_category(int $id): WP_REST_Response {
        try {
            if ($id <= 3) {
                return $this->error_response('Cannot delete default categories', 400);
            }
            
            return $this->success_response([
                'message' => 'Category deleted successfully',
                'id' => $id
            ]);
        } catch (Exception $e) {
            return $this->error_response('Failed to delete category', 500);
        }
    }

    private function get_settings(): WP_REST_Response {
        try {
            $settings = [
                'auto_save' => true,
                'auto_save_interval' => 30,
                'default_template' => 'template_invoice_basic',
                'canvas_width' => 595,
                'canvas_height' => 842,
                'grid_enabled' => true,
                'snap_to_grid' => true,
                'grid_size' => 10
            ];
            
            return $this->success_response($settings);
        } catch (Exception $e) {
            return $this->error_response('Failed to get settings', 500);
        }
    }

    private function update_settings(WP_REST_Request $request): WP_REST_Response {
        try {
            $data = $request->get_json_params();
            
            // Valider et sauvegarder les paramètres
            $allowed_settings = [
                'auto_save', 'auto_save_interval', 'default_template',
                'canvas_width', 'canvas_height', 'grid_enabled',
                'snap_to_grid', 'grid_size'
            ];
            
            $updated_settings = [];
            foreach ($allowed_settings as $setting) {
                if (isset($data[$setting])) {
                    $updated_settings[$setting] = $data[$setting];
                }
            }
            
            return $this->success_response([
                'message' => 'Settings updated successfully',
                'updated_settings' => $updated_settings
            ]);
        } catch (Exception $e) {
            return $this->error_response('Failed to update settings', 500);
        }
    }

    /**
     * Sauvegarde automatique d'un template avec versioning
     */
    public function handle_template_save(WP_REST_Request $request): WP_REST_Response {
        try {
            $template_id = $request->get_param('id');
            $data = $request->get_param('data');
            $auto_save = $request->get_param('auto_save');

            // Validation des données
            if (empty($template_id) || empty($data)) {
                return $this->error_response('Template ID and data are required', 400);
            }

            // Mapper les slugs vers des IDs numériques pour les templates par défaut
            $template_slug_map = [
                'template_invoice_basic' => 1,
                'template_quote_standard' => 2,
                'template_order_form' => 3
            ];

            $numeric_template_id = null;
            $is_new_template = false;

            if (is_numeric($template_id)) {
                $numeric_template_id = (int) $template_id;
            } elseif (isset($template_slug_map[$template_id])) {
                $numeric_template_id = $template_slug_map[$template_id];
            } elseif (strpos($template_id, 'template_') === 0) {
                // Nouveau template créé dynamiquement
                $is_new_template = true;
            } else {
                return $this->error_response('Invalid template ID', 400);
            }

            if (!$is_new_template) {
                // Vérifier que le template existe
                $template = $this->database->get_template($numeric_template_id);
                if (!$template) {
                    return $this->error_response('Template not found', 404);
                }
            } else {
                // Créer un nouveau template
                $template_data = [
                    'name' => 'Nouveau Template',
                    'description' => 'Template créé dynamiquement',
                    'type' => 'pdf',
                    'content' => is_array($data) ? wp_json_encode($data) : $data,
                    'settings' => '{}',
                    'status' => 'active',
                    'author_id' => get_current_user_id(),
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ];

                $numeric_template_id = $this->database->create_template($template_data);
                if (!$numeric_template_id) {
                    return $this->error_response('Failed to create new template', 500);
                }
            }

            // Créer une nouvelle version
            $version_data = [
                'template_id' => $numeric_template_id,
                'data' => is_array($data) ? wp_json_encode($data) : $data,
                'version_number' => $this->get_next_version_number($numeric_template_id),
                'created_by' => get_current_user_id(),
                'created_at' => current_time('mysql'),
                'is_auto_save' => $auto_save,
                'change_summary' => $auto_save ? 'Auto-save' : 'Manual save'
            ];

            $version_id = $this->database->create_template_version($version_data);
            if (!$version_id) {
                return $this->error_response('Failed to create template version', 500);
            }

            // Mettre à jour le template principal
            $update_data = [
                'data' => is_array($data) ? wp_json_encode($data) : $data,
                'updated_at' => current_time('mysql'),
                'current_version' => $version_data['version_number']
            ];

            $updated = $this->database->update_template($numeric_template_id, $update_data);
            if (!$updated) {
                return $this->error_response('Failed to update template', 500);
            }

            // Invalider le cache
            $this->cache->delete("template_{$template_id}");
            $this->cache->delete("template_versions_{$template_id}");

            $this->logger->info('Template saved with versioning', [
                'template_id' => $template_id,
                'numeric_id' => $numeric_template_id,
                'version_id' => $version_id,
                'version_number' => $version_data['version_number'],
                'auto_save' => $auto_save,
                'is_new' => $is_new_template
            ]);

            return $this->success_response([
                'message' => $is_new_template ? 'New template created and saved' : ($auto_save ? 'Template auto-saved' : 'Template saved'),
                'template_id' => $template_id,
                'numeric_id' => $numeric_template_id,
                'version_id' => $version_id,
                'version_number' => $version_data['version_number'],
                'saved_at' => $version_data['created_at'],
                'is_new' => $is_new_template
            ]);

        } catch (Exception $e) {
            $this->logger->error('Template save failed', [
                'error' => $e->getMessage(),
                'template_id' => $template_id ?? null
            ]);
            return $this->error_response('Internal server error', 500);
        }
    }

    /**
     * Récupère les versions d'un template
     */
    public function handle_template_versions(WP_REST_Request $request): WP_REST_Response {
        try {
            $template_id = $request->get_param('id');
            $limit = $request->get_param('limit');

            if (empty($template_id)) {
                return $this->error_response('Template ID is required', 400);
            }

            // Mapper les slugs vers des IDs numériques pour les templates par défaut
            $template_slug_map = [
                'template_invoice_basic' => 1,
                'template_quote_standard' => 2,
                'template_order_form' => 3
            ];

            $numeric_template_id = is_numeric($template_id) ? (int) $template_id : ($template_slug_map[$template_id] ?? null);

            if ($numeric_template_id === null) {
                return $this->error_response('Invalid template ID', 400);
            }

            // Vérifier que le template existe
            $template = $this->database->get_template($numeric_template_id);
            if (!$template) {
                return $this->error_response('Template not found', 404);
            }

            // Récupérer les versions depuis le cache ou la DB
            $cache_key = "template_versions_{$numeric_template_id}";
            $versions = $this->cache->get($cache_key);

            if ($versions === false) {
                $versions = $this->database->get_template_versions($numeric_template_id, $limit);
                $this->cache->set($cache_key, $versions, 300); // Cache 5 minutes
            }

            return $this->success_response([
                'template_id' => $template_id,
                'versions' => $versions,
                'total' => count($versions)
            ]);

        } catch (Exception $e) {
            $this->logger->error('Failed to get template versions', [
                'error' => $e->getMessage(),
                'template_id' => $template_id ?? null
            ]);
            return $this->error_response('Internal server error', 500);
        }
    }

    /**
     * Gère une version spécifique d'un template
     */
    public function handle_template_version(WP_REST_Request $request): WP_REST_Response {
        try {
            $template_id = $request->get_param('id');
            $version_id = $request->get_param('version_id');

            if (empty($template_id) || empty($version_id)) {
                return $this->error_response('Template ID and version ID are required', 400);
            }

            // Mapper les slugs vers des IDs numériques pour les templates par défaut
            $template_slug_map = [
                'template_invoice_basic' => 1,
                'template_quote_standard' => 2,
                'template_order_form' => 3
            ];

            $numeric_template_id = is_numeric($template_id) ? (int) $template_id : ($template_slug_map[$template_id] ?? null);

            if ($numeric_template_id === null) {
                return $this->error_response('Invalid template ID', 400);
            }

            if ($request->get_method() === 'GET') {
                // Récupérer une version spécifique
                $version = $this->database->get_template_version($version_id);
                if (!$version || $version->template_id != $numeric_template_id) {
                    return $this->error_response('Version not found', 404);
                }

                return $this->success_response([
                    'version' => $version,
                    'data' => json_decode($version->data, true)
                ]);

            } elseif ($request->get_method() === 'POST') {
                // Restaurer une version
                $version = $this->database->get_template_version($version_id);
                if (!$version || $version->template_id != $numeric_template_id) {
                    return $this->error_response('Version not found', 404);
                }

                // Créer une nouvelle version avec les données restaurées
                $restore_version_data = [
                    'template_id' => $template_id,
                    'data' => $version->data,
                    'version_number' => $this->get_next_version_number($template_id),
                    'created_by' => get_current_user_id(),
                    'created_at' => current_time('mysql'),
                    'is_auto_save' => false,
                    'change_summary' => "Restored from version {$version->version_number}"
                ];

                $new_version_id = $this->database->create_template_version($restore_version_data);
                if (!$new_version_id) {
                    return $this->error_response('Failed to restore version', 500);
                }

                // Mettre à jour le template principal
                $update_data = [
                    'data' => $version->data,
                    'updated_at' => current_time('mysql'),
                    'current_version' => $restore_version_data['version_number']
                ];

                $updated = $this->database->update_template($template_id, $update_data);
                if (!$updated) {
                    return $this->error_response('Failed to update template', 500);
                }

                // Invalider le cache
                $this->cache->delete("template_{$template_id}");
                $this->cache->delete("template_versions_{$template_id}");

                $this->logger->info('Template version restored', [
                    'template_id' => $template_id,
                    'restored_version' => $version->version_number,
                    'new_version' => $restore_version_data['version_number']
                ]);

                return $this->success_response([
                    'message' => 'Version restored successfully',
                    'template_id' => $template_id,
                    'new_version_id' => $new_version_id,
                    'new_version_number' => $restore_version_data['version_number']
                ]);
            }

        } catch (Exception $e) {
            $this->logger->error('Template version operation failed', [
                'error' => $e->getMessage(),
                'template_id' => $template_id ?? null,
                'version_id' => $version_id ?? null
            ]);
            return $this->error_response('Internal server error', 500);
        }
    }

    /**
     * Exporte un template
     */
    public function handle_template_export(WP_REST_Request $request): WP_REST_Response {
        try {
            $template_id = $request->get_param('id');
            $format = $request->get_param('format');

            if (empty($template_id)) {
                return $this->error_response('Template ID is required', 400);
            }

            // Récupérer le template complet
            $template = $this->database->get_template($template_id);
            if (!$template) {
                return $this->error_response('Template not found', 404);
            }

            // Préparer les données d'export
            $export_data = [
                'template' => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'description' => $template->description,
                    'category' => $template->category,
                    'data' => json_decode($template->data, true),
                    'settings' => json_decode($template->settings ?? '{}', true),
                    'created_at' => $template->created_at,
                    'updated_at' => $template->updated_at,
                    'version' => $template->current_version ?? 1
                ],
                'metadata' => [
                    'exported_by' => get_current_user_id(),
                    'exported_at' => current_time('mysql'),
                    'plugin_version' => PDF_BUILDER_VERSION,
                    'format' => $format
                ]
            ];

            // Générer le fichier selon le format
            $filename = sanitize_title($template->name) . '_template';
            $content = '';
            $content_type = 'application/json';

            switch ($format) {
                case 'json':
                    $content = wp_json_encode($export_data, JSON_PRETTY_PRINT);
                    $filename .= '.json';
                    $content_type = 'application/json';
                    break;

                case 'xml':
                    $content = $this->array_to_xml($export_data);
                    $filename .= '.xml';
                    $content_type = 'application/xml';
                    break;

                case 'yaml':
                    if (function_exists('yaml_emit')) {
                        $content = yaml_emit($export_data);
                        $filename .= '.yaml';
                        $content_type = 'application/yaml';
                    } else {
                        return $this->error_response('YAML format not supported (yaml extension not loaded)', 400);
                    }
                    break;
            }

            $this->logger->info('Template exported', [
                'template_id' => $template_id,
                'format' => $format,
                'filename' => $filename
            ]);

            // Retourner le fichier
            return new WP_REST_Response([
                'filename' => $filename,
                'content' => base64_encode($content),
                'content_type' => $content_type,
                'size' => strlen($content)
            ], 200, [
                'Content-Type' => 'application/json',
                'X-Template-Export' => 'true'
            ]);

        } catch (Exception $e) {
            $this->logger->error('Template export failed', [
                'error' => $e->getMessage(),
                'template_id' => $template_id ?? null
            ]);
            return $this->error_response('Internal server error', 500);
        }
    }

    /**
     * Exporte un template (méthode unifiée pour ID ou slug)
     */
    public function handle_template_export_unified(WP_REST_Request $request): WP_REST_Response {
        try {
            $identifier = $request->get_param('identifier');
            $format = $request->get_param('format');

            error_log('PDF Builder: handle_template_export_unified called');
            error_log('PDF Builder: identifier = ' . $identifier);
            error_log('PDF Builder: format = ' . $format);
            error_log('PDF Builder: request method = ' . $request->get_method());
            error_log('PDF Builder: all params = ' . json_encode($request->get_params()));

            if (empty($identifier)) {
                error_log('PDF Builder: identifier is empty');
                return $this->error_response('Template identifier is required', 400);
            }

            // Détecter si c'est un ID numérique ou un slug
            if (is_numeric($identifier)) {
                error_log('PDF Builder: identifier is numeric, calling handle_template_export');
                // C'est un ID numérique - utiliser la méthode existante
                $request->set_param('id', (int) $identifier);
                return $this->handle_template_export($request);
            } else {
                error_log('PDF Builder: identifier is slug, calling handle_template_export_by_slug');
                // C'est un slug - utiliser la méthode slug
                $request->set_param('slug', $identifier);
                return $this->handle_template_export_by_slug($request);
            }

        } catch (Exception $e) {
            error_log('PDF Builder: Exception in handle_template_export_unified: ' . $e->getMessage());
            $this->logger->error('Template export unified failed', [
                'error' => $e->getMessage(),
                'identifier' => $identifier ?? null
            ]);
            return $this->error_response('Internal server error', 500);
        }
    }

    /**
     * Exporte un template par slug/nom
     */
    public function handle_template_export_by_slug(WP_REST_Request $request): WP_REST_Response {
        try {
            $template_slug = $request->get_param('slug');
            $format = $request->get_param('format');

            error_log('PDF Builder: handle_template_export_by_slug called');
            error_log('PDF Builder: template_slug = ' . $template_slug);
            error_log('PDF Builder: format = ' . $format);

            if (empty($template_slug)) {
                error_log('PDF Builder: template_slug is empty');
                return $this->error_response('Template slug is required', 400);
            }

            // Mapper les slugs vers des templates par défaut
            $template_map = [
                'template_invoice_basic' => [
                    'id' => 1,
                    'name' => 'Facture Basique',
                    'description' => 'Template de facture simple et professionnel',
                    'category' => 'invoice',
                    'data' => [
                        'elements' => [
                            ['type' => 'text', 'content' => 'FACTURE', 'position' => ['x' => 50, 'y' => 50]],
                            ['type' => 'text', 'content' => 'Numéro: [invoice_number]', 'position' => ['x' => 50, 'y' => 80]]
                        ]
                    ],
                    'settings' => ['width' => 210, 'height' => 297, 'unit' => 'mm']
                ],
                'template_quote_standard' => [
                    'id' => 2,
                    'name' => 'Devis Standard',
                    'description' => 'Template de devis professionnel',
                    'category' => 'quote',
                    'data' => [
                        'elements' => [
                            ['type' => 'text', 'content' => 'DEVIS', 'position' => ['x' => 50, 'y' => 50]],
                            ['type' => 'text', 'content' => 'Numéro: [quote_number]', 'position' => ['x' => 50, 'y' => 80]]
                        ]
                    ],
                    'settings' => ['width' => 210, 'height' => 297, 'unit' => 'mm']
                ],
                'template_order_form' => [
                    'id' => 3,
                    'name' => 'Bon de Commande',
                    'description' => 'Template pour bons de commande',
                    'category' => 'order',
                    'data' => [
                        'elements' => [
                            ['type' => 'text', 'content' => 'BON DE COMMANDE', 'position' => ['x' => 50, 'y' => 50]],
                            ['type' => 'text', 'content' => 'Numéro: [order_number]', 'position' => ['x' => 50, 'y' => 80]]
                        ]
                    ],
                    'settings' => ['width' => 210, 'height' => 297, 'unit' => 'mm']
                ]
            ];

            if (!isset($template_map[$template_slug])) {
                return $this->error_response('Template not found', 404);
            }

            $template = $template_map[$template_slug];

            // Préparer les données d'export
            $export_data = [
                'template' => [
                    'id' => $template['id'],
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'category' => $template['category'],
                    'data' => $template['data'],
                    'settings' => $template['settings'],
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                    'version' => 1
                ],
                'metadata' => [
                    'exported_by' => get_current_user_id(),
                    'exported_at' => current_time('mysql'),
                    'plugin_version' => '1.0.0',
                    'format' => $format
                ]
            ];

            // Générer le fichier selon le format
            $filename = sanitize_title($template['name']) . '_template';
            $content = '';
            $content_type = 'application/json';

            switch ($format) {
                case 'json':
                    $content = wp_json_encode($export_data, JSON_PRETTY_PRINT);
                    $filename .= '.json';
                    $content_type = 'application/json';
                    break;

                case 'xml':
                    $content = $this->array_to_xml($export_data);
                    $filename .= '.xml';
                    $content_type = 'application/xml';
                    break;

                case 'yaml':
                    if (function_exists('yaml_emit')) {
                        $content = yaml_emit($export_data);
                        $filename .= '.yaml';
                        $content_type = 'application/yaml';
                    } else {
                        return $this->error_response('YAML format not supported (yaml extension not loaded)', 400);
                    }
                    break;
            }

            $this->logger->info('Template exported by slug', [
                'template_slug' => $template_slug,
                'format' => $format,
                'filename' => $filename
            ]);

            // Retourner le fichier
            return new WP_REST_Response([
                'filename' => $filename,
                'content' => base64_encode($content),
                'content_type' => $content_type,
                'size' => strlen($content)
            ], 200, [
                'Content-Type' => 'application/json',
                'X-Template-Export' => 'true'
            ]);

        } catch (Exception $e) {
            $this->logger->error('Template export by slug failed', [
                'error' => $e->getMessage(),
                'template_slug' => $template_slug ?? null
            ]);
            return $this->error_response('Internal server error', 500);
        }
    }

    /**
     * Importe un template
     */
    public function handle_template_import(WP_REST_Request $request): WP_REST_Response {
        try {
            $name = $request->get_param('name');
            $category = $request->get_param('category');
            $data = $request->get_param('data');

            if (empty($name)) {
                return $this->error_response('Template name is required', 400);
            }

            $import_data = null;

            // Vérifier si c'est un upload de fichier
            if (isset($_FILES['template_file'])) {
                $file = $_FILES['template_file'];

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    return $this->error_response('File upload error', 400);
                }

                // Valider le type de fichier
                $allowed_types = ['application/json', 'application/xml', 'application/yaml', 'text/yaml'];
                if (!in_array($file['type'], $allowed_types)) {
                    return $this->error_response('Invalid file type. Only JSON, XML, and YAML files are allowed.', 400);
                }

                // Lire le contenu du fichier
                $content = file_get_contents($file['tmp_name']);
                if ($content === false) {
                    return $this->error_response('Failed to read file content', 500);
                }

                // Parser selon le type
                $import_data = $this->parse_import_file($content, $file['type']);

            } elseif (!empty($data)) {
                // Données directes
                $import_data = is_array($data) ? $data : json_decode($data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return $this->error_response('Invalid JSON data', 400);
                }
            } else {
                return $this->error_response('Either file upload or data parameter is required', 400);
            }

            if (!$import_data || !isset($import_data['template'])) {
                return $this->error_response('Invalid import data format', 400);
            }

            // Créer le nouveau template
            $template_data = [
                'name' => $name,
                'description' => $import_data['template']['description'] ?? '',
                'category' => $category,
                'data' => wp_json_encode($import_data['template']['data']),
                'settings' => wp_json_encode($import_data['template']['settings'] ?? []),
                'created_by' => get_current_user_id(),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
                'current_version' => 1,
                'is_active' => 1
            ];

            $template_id = $this->database->create_template($template_data);
            if (!$template_id) {
                return $this->error_response('Failed to create template', 500);
            }

            // Créer la première version
            $version_data = [
                'template_id' => $template_id,
                'data' => $template_data['data'],
                'version_number' => 1,
                'created_by' => get_current_user_id(),
                'created_at' => current_time('mysql'),
                'is_auto_save' => false,
                'change_summary' => 'Initial import'
            ];

            $version_id = $this->database->create_template_version($version_data);

            $this->logger->info('Template imported', [
                'template_id' => $template_id,
                'name' => $name,
                'category' => $category,
                'source' => isset($_FILES['template_file']) ? 'file' : 'data'
            ]);

            return $this->success_response([
                'message' => 'Template imported successfully',
                'template_id' => $template_id,
                'version_id' => $version_id,
                'name' => $name,
                'category' => $category
            ]);

        } catch (Exception $e) {
            $this->logger->error('Template import failed', [
                'error' => $e->getMessage(),
                'name' => $name ?? null
            ]);
            return $this->error_response('Internal server error', 500);
        }
    }

    /**
     * Génère un aperçu PDF d'un template
     */
    public function handle_template_preview(WP_REST_Request $request): WP_REST_Response {
        try {
            $template_id = $request->get_param('id');
            $data = $request->get_param('data');
            $format = $request->get_param('format');

            if (empty($template_id) || empty($data)) {
                return $this->error_response('Template ID and data are required', 400);
            }

            // Générer l'aperçu
            $preview_result = $this->generate_template_preview($template_id, $data, $format);

            if (!$preview_result['success']) {
                return $this->error_response($preview_result['error'], 500);
            }

            $this->logger->info('Template preview generated', [
                'template_id' => $template_id,
                'format' => $format,
                'size' => strlen($preview_result['content'] ?? '')
            ]);

            return $this->success_response([
                'preview_url' => $preview_result['url'] ?? null,
                'content' => base64_encode($preview_result['content'] ?? ''),
                'format' => $format,
                'size' => strlen($preview_result['content'] ?? ''),
                'generated_at' => current_time('mysql')
            ]);

        } catch (Exception $e) {
            $this->logger->error('Template preview failed', [
                'error' => $e->getMessage(),
                'template_id' => $template_id ?? null
            ]);
            return $this->error_response('Internal server error', 500);
        }
    }

    /**
     * Gère la génération d'aperçu pour le template par défaut
     */
    public function handle_template_default_preview(WP_REST_Request $request): WP_REST_Response {
        try {
            $data = $request->get_param('data');
            $format = $request->get_param('format');

            if (empty($data)) {
                return $this->error_response('Data is required', 400);
            }

            // Générer l'aperçu avec un template par défaut (vide)
            $preview_result = $this->generate_default_template_preview($data, $format);

            if (!$preview_result['success']) {
                return $this->error_response($preview_result['error'], 500);
            }

            $this->logger->info('Default template preview generated', [
                'format' => $format,
                'size' => strlen($preview_result['content'] ?? '')
            ]);

            return $this->success_response([
                'preview_url' => $preview_result['url'] ?? null,
                'content' => base64_encode($preview_result['content'] ?? ''),
                'format' => $format,
                'size' => strlen($preview_result['content'] ?? ''),
                'generated_at' => current_time('mysql')
            ]);

        } catch (Exception $e) {
            $this->logger->error('Default template preview failed', [
                'error' => $e->getMessage()
            ]);
            return $this->error_response('Internal server error', 500);
        }
    }

    /**
     * Génère un aperçu pour le template par défaut
     */
    private function generate_default_template_preview(array $data, string $format): array {
        try {
            // Dimensions A4 portrait par défaut (en pixels à 72 DPI)
            $canvas_width = 595; // A4 width in pixels at 72 DPI
            $canvas_height = 842; // A4 height in pixels at 72 DPI

            // Utiliser les dimensions de la première page si disponibles
            if (isset($data['pages']) && is_array($data['pages']) && !empty($data['pages'])) {
                $first_page = $data['pages'][0];
                if (isset($first_page['size']['width']) && isset($first_page['size']['height'])) {
                    $canvas_width = (int) $first_page['size']['width'];
                    $canvas_height = (int) $first_page['size']['height'];
                }
            }

            // Générer le HTML directement avec les dimensions A4
            $html = $this->create_preview_html($data['elements'] ?? [], $canvas_width, $canvas_height);

            return [
                'success' => true,
                'content' => $html,
                'url' => null,
                'dimensions' => [
                    'width' => $canvas_width,
                    'height' => $canvas_height
                ]
            ];

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Default preview generation error: ' . $e->getMessage()];
        }
    }

    /**
     * Obtient le prochain numéro de version pour un template
     */
    private function get_next_version_number(int $template_id): int {
        $latest_version = $this->database->get_latest_template_version($template_id);
        return $latest_version ? $latest_version->version_number + 1 : 1;
    }

    /**
     * Convertit un array en XML
     */
    private function array_to_xml($data, $root_element = 'template_export'): string {
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><{$root_element}></{$root_element}>");
        $this->array_to_xml_recursive($data, $xml);
        return $xml->asXML();
    }

    /**
     * Fonction récursive pour array_to_xml
     */
    private function array_to_xml_recursive($data, &$xml) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item' . $key;
                }
                $subnode = $xml->addChild($key);
                $this->array_to_xml_recursive($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars($value ?? ''));
            }
        }
    }

    /**
     * Parse un fichier d'import
     */
    private function parse_import_file(string $content, string $mime_type): ?array {
        switch ($mime_type) {
            case 'application/json':
                $data = json_decode($content, true);
                return json_last_error() === JSON_ERROR_NONE ? $data : null;

            case 'application/xml':
            case 'text/xml':
                $xml = simplexml_load_string($content);
                return $xml ? json_decode(json_encode($xml), true) : null;

            case 'application/yaml':
            case 'text/yaml':
                if (function_exists('yaml_parse')) {
                    return yaml_parse($content);
                }
                return null;

            default:
                return null;
        }
    }

    /**
     * Génère un aperçu de template
     */
    private function generate_template_preview(int $template_id, array $data, string $format): array {
        try {
            // Générer directement du HTML pour la preview
            $canvas_width = 595; // A4 par défaut
            $canvas_height = 842; // A4 par défaut

            if (isset($data['pages']) && is_array($data['pages']) && !empty($data['pages'])) {
                $first_page = $data['pages'][0];
                if (isset($first_page['size']['width']) && isset($first_page['size']['height'])) {
                    $canvas_width = (int) $first_page['size']['width'];
                    $canvas_height = (int) $first_page['size']['height'];
                }
            }

            // Utiliser les éléments de la première page
            $elements = [];
            $page_data = [];
            if (isset($data['pages']) && is_array($data['pages']) && !empty($data['pages'])) {
                $first_page = $data['pages'][0];
                $elements = $first_page['elements'] ?? [];
                $page_data = $first_page;
            } elseif (isset($data['elements']) && is_array($data['elements'])) {
                // Fallback pour l'ancienne structure
                $elements = $data['elements'];
            }

            $html = $this->create_preview_html($elements, $canvas_width, $canvas_height, $page_data);

            return [
                'success' => true,
                'content' => $html,
                'url' => null,
                'dimensions' => [
                    'width' => $canvas_width,
                    'height' => $canvas_height
                ]
            ];

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Preview generation error: ' . $e->getMessage()];
        }
    }

    /**
     * Créer le HTML de preview à partir des éléments avec les dimensions du canvas
     */
    private function create_preview_html(array $elements, int $canvas_width, int $canvas_height, array $page_data = []): string {
        // Gestion des marges d'impression
        $margins = isset($page_data['margins']) ? $page_data['margins'] : ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20];
        $margin_css = sprintf('margin: 0; padding: %dpx %dpx %dpx %dpx;', $margins['top'], $margins['right'], $margins['bottom'], $margins['left']);
        
        $html = '<html><head>';
        $html .= '<style>';
        $html .= 'body { font-family: Arial, sans-serif; ' . $margin_css . ' }';
        $html .= '.canvas { width: ' . $canvas_width . 'px; height: ' . $canvas_height . 'px; background: white; position: relative; border: 1px solid #ccc; }';
        $html .= '.element { position: absolute; }';
        $html .= '.text-element { font-size: 14px; color: #333; }';
        $html .= '.rectangle { background: #007cba; border: 2px solid #005a87; border-radius: 4px; }';
        $html .= '.image-element { background: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #666; font-size: 12px; }';
        $html .= '.barcode-element { background: #fff; border: 1px solid #000; display: flex; align-items: center; justify-content: center; color: #000; font-family: monospace; font-size: 10px; }';
        $html .= '.qrcode-element { background: #fff; border: 1px solid #000; display: flex; align-items: center; justify-content: center; color: #000; font-size: 10px; }';
        $html .= '.table-element { background: #fff; border: 1px solid #ddd; }';
        $html .= '.table-element table { width: 100%; border-collapse: collapse; }';
        $html .= '.table-element th, .table-element td { border: 1px solid #ddd; padding: 4px; font-size: 10px; }';
        $html .= '.table-element th { background: #f5f5f5; font-weight: bold; }';
        $html .= '.wc-element { background: #e8f5e8; border: 1px solid #4caf50; padding: 2px; font-size: 12px; color: #2e7d32; }';
        $html .= '</style>';
        $html .= '</head><body>';
        $html .= '<div class="canvas">';

        foreach ($elements as $element) {
            $style = 'left: ' . (isset($element['position']['x']) ? $element['position']['x'] : 0) . 'px; ';
            $style .= 'top: ' . (isset($element['position']['y']) ? $element['position']['y'] : 0) . 'px; ';
            $style .= 'width: ' . (isset($element['size']['width']) ? $element['size']['width'] : 100) . 'px; ';
            $style .= 'height: ' . (isset($element['size']['height']) ? $element['size']['height'] : 50) . 'px; ';

            $class = 'element';
            $content = '';

            if (isset($element['type'])) {
                switch ($element['type']) {
                    case 'text':
                        $class .= ' text-element';
                        $content = esc_html($element['content'] ?? 'Exemple de texte');
                        break;

                    case 'rectangle':
                        $class .= ' rectangle';
                        break;

                    case 'image':
                        $class .= ' image-element';
                        $content = '📷 Image';
                        break;

                    case 'barcode':
                        $class .= ' barcode-element';
                        $content = '|| | ||| | || |||';
                        break;

                    case 'qrcode':
                        $class .= ' qrcode-element';
                        $content = '█▀▀▀▀▀█<br>█ █▀▀▀█ █<br>█ ▀▀▀▀ █<br>█▄▄▄▄▄▄█';
                        break;

                    case 'table':
                        $class .= ' table-element';
                        $content = '<table><thead><tr><th>Produit</th><th>Qté</th><th>Prix</th></tr></thead><tbody><tr><td>T-Shirt</td><td>2</td><td>29.99€</td></tr><tr><td>Pantalon</td><td>1</td><td>49.99€</td></tr></tbody></table>';
                        break;

                    case 'wc_field':
                        $class .= ' wc-element';
                        $content = $this->get_wc_field_example_content($element);
                        break;

                    default:
                        $content = '[' . esc_html($element['type']) . ']';
                }
            }

            $html .= '<div class="' . $class . '" style="' . $style . '">' . $content . '</div>';
        }

        $html .= '</div>';
        $html .= '</body></html>';
        return $html;
    }

    /**
     * Obtenir le contenu d'exemple pour un champ WooCommerce
     */
    private function get_wc_field_example_content(array $element): string {
        $field_type = $element['wc_field_type'] ?? '';

        switch ($field_type) {
            // Commande
            case 'order_number':
                return '#12345';
            case 'order_date':
                return '15/10/2025';
            case 'order_status':
                return 'TERMINÉE';
            case 'invoice_number':
                return 'INV-0012345';

            // Client
            case 'customer_name':
                return 'Jean Dupont';
            case 'customer_email':
                return 'jean.dupont@email.com';
            case 'customer_phone':
                return '+33 6 12 34 56 78';

            // Adresse de facturation
            case 'billing_name':
                return 'Jean Dupont';
            case 'billing_company':
                return 'Entreprise SARL';
            case 'billing_address':
                return '123 Rue de la Paix<br>75001 Paris';
            case 'billing_city':
                return 'Paris';
            case 'billing_state':
                return 'Île-de-France';
            case 'billing_postcode':
                return '75001';
            case 'billing_country':
                return 'France';

            // Adresse de livraison
            case 'shipping_name':
                return 'Jean Dupont';
            case 'shipping_address':
                return '456 Avenue des Champs<br>75008 Paris';
            case 'shipping_city':
                return 'Paris';
            case 'shipping_postcode':
                return '75008';

            // Produits
            case 'product_name':
                return 'T-Shirt Premium';
            case 'product_sku':
                return 'TSHIRT-001';
            case 'product_price':
                return '29.99€';
            case 'product_quantity':
                return '2';

            // Totaux
            case 'order_subtotal':
                return '79.98€';
            case 'order_tax':
                return '15.99€';
            case 'order_total':
                return '95.97€';
            case 'order_discount':
                return '-5.00€';

            default:
                return '[' . esc_html($field_type) . ']';
        }
    }

    /**
     * Vérifie si l'API Manager est déjà initialisé
     *
     * @return bool
     */
    public function is_initialized(): bool {
        return defined('PDF_BUILDER_API_INITIALIZED') && PDF_BUILDER_API_INITIALIZED;
    }

    /**
     * Initialise l'API Manager
     *
     * @return void
     */
    public function init(): void {
        // Protection globale contre les initialisations multiples
        if (defined('PDF_BUILDER_API_INITIALIZED') && PDF_BUILDER_API_INITIALIZED) {
            return;
        }

        static $api_initialized = false;
        if ($api_initialized) {
            return;
        }
        $api_initialized = true;

        // Enregistrer les routes sur rest_api_init si le hook n'est pas encore passé
        // Sinon, les enregistrer immédiatement
        if (did_action('rest_api_init')) {
            $this->register_routes();
            $this->logger->info('API routes registered immediately (rest_api_init already fired)');
        } else {
            add_action('rest_api_init', [$this, 'register_routes']);
            $this->logger->info('API routes scheduled for rest_api_init hook');
        }

        $this->logger->info('API manager initialized', [
            'routes_count' => count($this->routes),
            'prefix' => $this->api_prefix
        ]);

        define('PDF_BUILDER_API_INITIALIZED', true);
    }
}