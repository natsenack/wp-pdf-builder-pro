<?php
/**
 * Gestionnaire de Templates - PDF Builder Pro
 *
 * Gestion avancée des templates avec :
 * - Création et édition de templates
 * - Variables dynamiques
 * - Validation des templates
 * - Cache des templates
 * - Export/Import
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Gestionnaire de Templates
 */
class PDF_Builder_Template_Manager {

    /**
     * Instance singleton
     * @var PDF_Builder_Template_Manager
     */
    private static $instance = null;

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
     * Templates en cache
     * @var array
     */
    private $template_cache = [];

    /**
     * Constructeur privé
     */
    private function __construct() {
        $this->database = PDF_Builder_Database_Manager::getInstance();
        $this->cache = PDF_Builder_Cache_Manager::getInstance();
        $this->logger = PDF_Builder_Logger::getInstance();
        $this->config = PDF_Builder_Config_Manager::getInstance();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Template_Manager
     */
    public static function getInstance(): PDF_Builder_Template_Manager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Créer un nouveau template
     *
     * @param array $data
     * @return int|false
     */
    public function create_template(array $data) {
        try {
            // Validation des données
            $this->validate_template_data($data);

            // Préparer les données
            $template_data = $this->prepare_template_data($data);

            // Insérer en base
            $id = $this->database->insert('templates', $template_data);

            if ($id) {
                // Invalider le cache
                $this->invalidate_template_cache();

                $this->logger->info('Template created', [
                    'template_id' => $id,
                    'name' => $data['name']
                ]);

                return $id;
            }

            return false;
        } catch (Exception $e) {
            $this->logger->error('Failed to create template', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return false;
        }
    }

    /**
     * Obtenir un template par ID
     *
     * @param int $id
     * @return array|null
     */
    public function get_template(int $id) {
        // Vérifier le cache
        $cache_key = 'template_' . $id;
        $cached_template = $this->cache->get($cache_key);

        if ($cached_template) {
            return $cached_template;
        }

        // Vérifier que la table templates existe avant de faire la requête
        global $wpdb;
        $templates_table = $wpdb->prefix . 'pdf_builder_templates';
        if ($wpdb->get_var("SHOW TABLES LIKE '$templates_table'") !== $templates_table) {
            return null;
        }

        // Récupérer de la base
        $template = $this->database->get_row(
            "SELECT t.*, u.display_name as author_name FROM {$wpdb->prefix}pdf_builder_templates t LEFT JOIN {$wpdb->users} u ON t.author_id = u.ID WHERE t.id = %d",
            [$id]
        );

        if ($template) {
            // Décoder les données JSON
            $template = $this->decode_template_data($template);

            // Mettre en cache
            $this->cache->set($cache_key, $template, 3600); // 1 heure

            return $template;
        }

        return null;
    }

    /**
     * Obtenir tous les templates
     *
     * @param array $filters
     * @return array
     */
    public function get_templates(array $filters = []): array {
        // Pour la compatibilité, appeler get_templates_paginated sans pagination
        return $this->get_templates_paginated($filters, 1, PHP_INT_MAX);
    }

    public function get_templates_paginated(array $filters = [], int $page = 1, int $per_page = 12): array {
        // S'assurer que les tables existent et ont la bonne structure
        $this->database->create_essential_tables();

        // Vérifier et corriger la structure des tables existantes
        $this->database->verify_and_fix_table_structure();

        $cache_key = 'templates_paginated_' . $page . '_' . $per_page . '_' . md5(serialize($filters));
        $cached_result = $this->cache->get($cache_key);

        if ($cached_result) {
            return $cached_result;
        }

        $where = ['1=1'];
        $args = [];

        // Appliquer les filtres
        if (!empty($filters['status'])) {
            $where[] = 'status = %s';
            $args[] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $where[] = 'type = %s';
            $args[] = $filters['type'];
        }

        if (!empty($filters['category_id'])) {
            $where[] = 'category_id = %d';
            $args[] = $filters['category_id'];
        }

        if (!empty($filters['author_id'])) {
            $where[] = 'author_id = %d';
            $args[] = $filters['author_id'];
        }

        $where_clause = implode(' AND ', $where);
        $offset = ($page - 1) * $per_page;

        // DEBUG: Log des paramètres de pagination
        error_log("PDF Builder: get_templates_paginated - page: $page, per_page: $per_page, offset: $offset");
        error_log("PDF Builder: get_templates_paginated - where_clause: $where_clause");
        error_log("PDF Builder: get_templates_paginated - args: " . print_r($args, true));

        // Vérifier que la table templates existe avant de faire la requête
        global $wpdb;
        $templates_table = $wpdb->prefix . 'pdf_builder_templates';
        if ($wpdb->get_var("SHOW TABLES LIKE '$templates_table'") === $templates_table) {
            $query = "SELECT t.*, u.display_name as author_name FROM {$wpdb->prefix}pdf_builder_templates t LEFT JOIN {$wpdb->users} u ON t.author_id = u.ID WHERE $where_clause ORDER BY t.created_at DESC, t.id DESC LIMIT %d OFFSET %d";
            $query_args = array_merge($args, [$per_page, $offset]);

            error_log("PDF Builder: get_templates_paginated - query: $query");
            error_log("PDF Builder: get_templates_paginated - query_args: " . print_r($query_args, true));

            $templates = $this->database->get_results($query, $query_args);

            error_log("PDF Builder: get_templates_paginated - templates found: " . count($templates));
            if (!empty($templates)) {
                $template_ids = array_map(function($t) { return $t['id']; }, $templates);
                error_log("PDF Builder: get_templates_paginated - template IDs: " . implode(', ', $template_ids));
                error_log("PDF Builder: get_templates_paginated - first template: " . print_r($templates[0], true));
            }

            // Compter le total pour la pagination
            $total_query = "SELECT COUNT(*) FROM {$wpdb->prefix}pdf_builder_templates t LEFT JOIN {$wpdb->users} u ON t.author_id = u.ID WHERE $where_clause";
            $total = $this->database->get_var($total_query, $args);

            error_log("PDF Builder: get_templates_paginated - total count: $total");
        } else {
            $templates = [];
            $total = 0;
            error_log("PDF Builder: get_templates_paginated - table does not exist: $templates_table");
        }

        // Décoder les données JSON pour chaque template
        foreach ($templates as &$template) {
            $template = $this->decode_template_data($template);
        }

        $result = [
            'templates' => $templates,
            'total' => (int) $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ];

        // Mettre en cache
        $this->cache->set($cache_key, $result, 1800); // 30 minutes

        return $result;
    }

    /**
     * Mettre à jour un template
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update_template(int $id, array $data): bool {
        try {
            // Validation des données
            $this->validate_template_data($data, false);

            // Préparer les données
            $update_data = $this->prepare_template_data($data, false);

            // Mettre à jour en base
            $result = $this->database->update('templates', $update_data, ['id' => $id]);

            if ($result !== false) {
                // Invalider le cache
                $this->invalidate_template_cache($id);

                $this->logger->info('Template updated', [
                    'template_id' => $id,
                    'changes' => array_keys($update_data)
                ]);

                return true;
            }

            return false;
        } catch (Exception $e) {
            $this->logger->error('Failed to update template', [
                'template_id' => $id,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return false;
        }
    }

    /**
     * Supprimer un template
     *
     * @param int $id
     * @return bool
     */
    public function delete_template(int $id): bool {
        try {
            // Vérifier si le template existe
            $template = $this->get_template($id);
            if (!$template) {
                return false;
            }

            // Supprimer de la base
            $result = $this->database->delete('templates', ['id' => $id]);

            if ($result) {
                // Invalider le cache
                $this->invalidate_template_cache($id);

                $this->logger->info('Template deleted', [
                    'template_id' => $id,
                    'name' => $template['name']
                ]);

                return true;
            }

            return false;
        } catch (Exception $e) {
            $this->logger->error('Failed to delete template', [
                'template_id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Dupliquer un template
     *
     * @param int $id
     * @param string $new_name
     * @return int|false
     */
    public function duplicate_template(int $id, string $new_name = ''): int|false {
        try {
            $template = $this->get_template($id);
            if (!$template) {
                return false;
            }

            $duplicate_data = $template;
            unset($duplicate_data['id'], $duplicate_data['created_at'], $duplicate_data['updated_at']);

            $duplicate_data['name'] = $new_name ?: $template['name'] . ' (Copy)';
            $duplicate_data['status'] = 'draft';
            $duplicate_data['author_id'] = get_current_user_id();

            return $this->create_template($duplicate_data);
        } catch (Exception $e) {
            $this->logger->error('Failed to duplicate template', [
                'template_id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Définir ou retirer le statut par défaut d'un template
     *
     * @param int $template_id
     * @param bool $is_default
     * @return bool
     */
    public function set_default_template(int $template_id, bool $is_default): bool {
        try {
            // Vérifier si le template existe
            $template = $this->get_template($template_id);
            if (!$template) {
                $this->logger->error('Template not found for set_default_template', [
                    'template_id' => $template_id
                ]);
                return false;
            }

            global $wpdb;
            $table_name = $wpdb->prefix . 'pdf_builder_templates';

            if ($is_default) {
                // Si on définit comme défaut, retirer le statut défaut des autres templates du même type
                $wpdb->update(
                    $table_name,
                    ['is_default' => 0],
                    ['type' => $template['type']],
                    ['%d'],
                    ['%s']
                );

                $this->logger->info('Removed default status from other templates', [
                    'type' => $template['type'],
                    'new_default_template_id' => $template_id
                ]);
            }

            // Mettre à jour le statut du template cible
            $result = $this->database->update('templates', [
                'is_default' => $is_default ? 1 : 0,
                'updated_at' => current_time('mysql')
            ], ['id' => $template_id]);

            if ($result !== false) {
                // Invalider le cache
                $this->invalidate_template_cache($template_id);

                $this->logger->info('Template default status updated', [
                    'template_id' => $template_id,
                    'name' => $template['name'],
                    'is_default' => $is_default
                ]);

                return true;
            }

            return false;
        } catch (Exception $e) {
            $this->logger->error('Failed to set default template', [
                'template_id' => $template_id,
                'is_default' => $is_default,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Générer un PDF à partir d'un template
     *
     * @param int $template_id
     * @param array $data
     * @return string|false
     */
    public function generate_pdf(int $template_id, array $data) {
        try {
            $template = $this->get_template($template_id);
            if (!$template) {
                throw new Exception('Template not found');
            }

            // Traiter les variables dynamiques
            $processed_content = $this->process_template_variables($template['content'], $data);

            // Générer le PDF (cette partie sera étendue avec une vraie bibliothèque PDF)
            $pdf_content = $this->render_pdf($processed_content, $template['settings']);

            // Sauvegarder le document
            $document_data = [
                'template_id' => $template_id,
                'title' => sanitize_text_field($data['title'] ?? 'Generated Document'),
                'data' => wp_json_encode($data),
                'status' => 'completed',
                'author_id' => get_current_user_id(),
                'generated_at' => current_time('mysql'),
                'file_path' => $this->save_pdf_file($pdf_content, $template_id),
                'file_size' => strlen($pdf_content)
            ];

            $document_id = $this->database->insert('documents', $document_data);

            if ($document_id) {
                $this->logger->info('PDF generated successfully', [
                    'template_id' => $template_id,
                    'document_id' => $document_id
                ]);

                return $document_id;
            }

            return false;
        } catch (Exception $e) {
            $this->logger->error('Failed to generate PDF', [
                'template_id' => $template_id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Exporter un template
     *
     * @param int $id
     * @return array|null
     */
    public function export_template(int $id): ?array {
        $template = $this->get_template($id);

        if (!$template) {
            return null;
        }

        return [
            'version' => PDF_BUILDER_VERSION,
            'exported_at' => current_time('mysql'),
            'template' => [
                'name' => $template['name'],
                'description' => $template['description'],
                'type' => $template['type'],
                'content' => $template['content'],
                'settings' => $template['settings'],
                'category_id' => $template['category_id']
            ]
        ];
    }

    /**
     * Importer un template
     *
     * @param array $data
     * @return int|false
     */
    public function import_template(array $data): int|false {
        if (!isset($data['template'])) {
            return false;
        }

        $template_data = $data['template'];
        $template_data['status'] = 'draft'; // Les imports sont en brouillon par défaut
        $template_data['author_id'] = get_current_user_id();

        return $this->create_template($template_data);
    }

    /**
     * Valider les données d'un template
     *
     * @param array $data
     * @param bool $is_new
     * @return void
     * @throws Exception
     */
    private function validate_template_data(array $data, bool $is_new = true): void {
        $required_fields = ['name', 'type'];

        if ($is_new) {
            $required_fields[] = 'content';
        }

        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '$field' is required");
            }
        }

        // Validation du type
        $allowed_types = $this->config->get('allowed_template_types', ['pdf', 'docx', 'html']);
        if (!in_array($data['type'], $allowed_types)) {
            throw new Exception("Invalid template type: {$data['type']}");
        }

        // Validation de la taille du contenu
        if (isset($data['content'])) {
            $max_size = $this->config->get('max_template_size', 50 * 1024 * 1024);
            $content_size = strlen(json_encode($data['content']));

            if ($content_size > $max_size) {
                throw new Exception("Template content exceeds maximum size limit");
            }
        }

        // Validation du nom
        if (strlen($data['name']) > 255) {
            throw new Exception("Template name is too long (max 255 characters)");
        }
    }

    /**
     * Préparer les données d'un template pour l'insertion
     *
     * @param array $data
     * @param bool $is_new
     * @return array
     */
    private function prepare_template_data(array $data, bool $is_new = true): array {
        $prepared_data = [];

        // Champs de base
        if (isset($data['name'])) {
            $prepared_data['name'] = sanitize_text_field($data['name']);
        }
        if (isset($data['description'])) {
            $prepared_data['description'] = sanitize_textarea_field($data['description'] ?? '');
        }
        if (isset($data['type'])) {
            $prepared_data['type'] = sanitize_text_field($data['type']);
        }
        if (isset($data['status'])) {
            $prepared_data['status'] = sanitize_text_field($data['status'] ?? 'active');
        }
        if (isset($data['category_id'])) {
            $prepared_data['category_id'] = !empty($data['category_id']) ? (int) $data['category_id'] : null;
        }
        if (isset($data['updated_at'])) {
            $prepared_data['updated_at'] = $data['updated_at'];
        }

        if (isset($data['content'])) {
            $prepared_data['content'] = wp_json_encode($data['content']);
        }

        if (isset($data['settings'])) {
            $prepared_data['settings'] = wp_json_encode($data['settings']);
        }

        if ($is_new) {
            $prepared_data['author_id'] = get_current_user_id();
        }

        return $prepared_data;
    }

    /**
     * Décoder les données JSON d'un template
     *
     * @param array $template
     * @return array
     */
    private function decode_template_data(array $template): array {
        if (isset($template['content']) && is_string($template['content'])) {
            $template['content'] = json_decode($template['content'], true);
        }

        if (isset($template['settings']) && is_string($template['settings'])) {
            $template['settings'] = json_decode($template['settings'], true);
        }

        return $template;
    }

    /**
     * Traiter les variables dynamiques dans un template
     *
     * @param array $content
     * @param array $data
     * @return array
     */
    private function process_template_variables(array $content, array $data): array {
        // Cette méthode traite les variables comme {{nom}}, {{date}}, etc.
        array_walk_recursive($content, function(&$value) use ($data) {
            if (is_string($value)) {
                // Remplacer les variables simples
                $value = preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($data) {
                    $key = $matches[1];
                    return $data[$key] ?? $matches[0];
                }, $value);

                // Variables spéciales
                $special_vars = [
                    'date' => current_time('Y-m-d'),
                    'datetime' => current_time('Y-m-d H:i:s'),
                    'user_name' => wp_get_current_user()->display_name,
                    'site_name' => get_bloginfo('name'),
                    'site_url' => get_bloginfo('url')
                ];

                foreach ($special_vars as $var => $replacement) {
                    $value = str_replace("{{{$var}}}", $replacement, $value);
                }
            }
        });

        return $content;
    }

    /**
     * Rendre le PDF (simulation pour l'instant)
     *
     * @param array $content
     * @param array $settings
     * @return string
     */
    private function render_pdf(array $content, array $settings): string {
        // Cette méthode sera étendue avec une vraie bibliothèque PDF comme TCPDF ou DomPDF
        // Pour l'instant, on retourne un contenu simulé
        return "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n/Contents 4 0 R\n>>\nendobj\n4 0 obj\n<<\n/Length 44\n>>\nstream\nBT\n/F1 12 Tf\n100 700 Td\n(" . json_encode($content) . ") Tj\nET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000200 00000 n \ntrailer\n<<\n/Size 5\n/Root 1 0 R\n>>\nstartxref\n284\n%%EOF";
    }

    /**
     * Sauvegarder le fichier PDF
     *
     * @param string $content
     * @param int $template_id
     * @return string
     */
    private function save_pdf_file(string $content, int $template_id): string {
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/pdf-builder/generated/';

        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }

        $filename = 'pdf_' . $template_id . '_' . time() . '.pdf';
        $file_path = $pdf_dir . $filename;

        file_put_contents($file_path, $content);

        return $file_path;
    }

    /**
     * Invalider le cache des templates
     *
     * @param int|null $template_id
     * @return void
     */
    private function invalidate_template_cache(?int $template_id = null): void {
        if ($template_id) {
            $this->cache->delete('template_' . $template_id);
        }

        // Invalider les caches de listes
        $this->cache->delete('templates_*');
    }

    /**
     * Obtenir les statistiques des templates
     *
     * @return array
     */
    public function get_stats(): array {
        // S'assurer que les tables existent et ont la bonne structure
        $this->database->create_essential_tables();

        // Vérifier et corriger la structure des tables existantes
        $this->database->verify_and_fix_table_structure();

        // Vérifier que la table templates existe avant de faire la requête
        global $wpdb;
        $templates_table = $wpdb->prefix . 'pdf_builder_templates';
        if ($wpdb->get_var("SHOW TABLES LIKE '$templates_table'") === $templates_table) {
            $stats = $this->database->get_results(
                "SELECT status, COUNT(*) as count FROM {$wpdb->prefix}pdf_builder_templates GROUP BY status"
            );
        } else {
            $stats = [];
        }

        $total = 0;
        $stats_by_status = [];

        foreach ($stats as $stat) {
            $stats_by_status[$stat['status']] = (int) $stat['count'];
            $total += (int) $stat['count'];
        }

        return [
            'total' => $total,
            'by_status' => $stats_by_status,
            'cache_hits' => $this->cache->get_stats()['hits'] ?? 0
        ];
    }

    /**
     * Nettoyer les templates expirés ou inutilisés
     *
     * @return void
     */
    public function cleanup(): void {
        // Supprimer les templates en brouillon plus vieux que 30 jours
        $cutoff_date = date('Y-m-d H:i:s', strtotime('-30 days'));

        $deleted = $this->database->delete('templates', [
            'status' => 'draft',
            'created_at' => ['<', $cutoff_date]
        ]);

        if ($deleted) {
            $this->logger->info('Old draft templates cleaned up', ['count' => $deleted]);
        }
    }

    /**
     * Initialisation du gestionnaire
     *
     * @return void
     */
    public function init(): void {
        // Nettoyer périodiquement
        if (!wp_next_scheduled('pdf_builder_cleanup_templates')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_cleanup_templates');
        }
        add_action('pdf_builder_cleanup_templates', [$this, 'cleanup']);

        $this->logger->info('Template manager initialized');
    }
}

