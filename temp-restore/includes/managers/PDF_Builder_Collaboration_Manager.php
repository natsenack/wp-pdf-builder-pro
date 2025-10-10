<?php
/**
 * Gestionnaire de Collaboration - PDF Builder Pro
 *
 * Système de collaboration multi-utilisateur avancé avec :
 * - Permissions granulaires
 * - Versioning complet des documents
 * - Commentaires et annotations
 * - Workflows d'approbation
 * - Collaboration temps réel
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Gestionnaire de Collaboration
 */
class PDF_Builder_Collaboration_Manager {

    /**
     * Instance singleton
     * @var PDF_Builder_Collaboration_Manager
     */
    private static $instance = null;

    /**
     * Gestionnaire de base de données
     * @var PDF_Builder_Database_Manager
     */
    private $database;

    /**
     * Logger
     * @var PDF_Builder_Logger
     */
    private $logger;

    /**
     * Cache manager
     * @var PDF_Builder_Cache_Manager
     */
    private $cache;

    /**
     * Niveaux de permissions
     * @var array
     */
    private $permission_levels = [
        'none' => 0,
        'view' => 1,
        'comment' => 2,
        'edit' => 3,
        'approve' => 4,
        'admin' => 5
    ];

    /**
     * Statuts de workflow
     * @var array
     */
    private $workflow_statuses = [
        'draft' => 'Brouillon',
        'review' => 'En révision',
        'approved' => 'Approuvé',
        'rejected' => 'Rejeté',
        'published' => 'Publié'
    ];

    /**
     * Constructeur privé
     */
    private function __construct() {
        $core = PDF_Builder_Core::getInstance();
        $this->database = $core->get_database_manager();
        $this->logger = $core->get_logger();
        $this->cache = $core->get_cache_manager();

        $this->init_hooks();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Collaboration_Manager
     */
    public static function getInstance(): PDF_Builder_Collaboration_Manager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les hooks WordPress
     */
    private function init_hooks(): void {
        // Hooks AJAX pour la collaboration
        add_action('wp_ajax_pdf_builder_share_document', [$this, 'ajax_share_document']);
        add_action('wp_ajax_pdf_builder_add_comment', [$this, 'ajax_add_comment']);
        add_action('wp_ajax_pdf_builder_get_comments', [$this, 'ajax_get_comments']);
        add_action('wp_ajax_pdf_builder_update_permissions', [$this, 'ajax_update_permissions']);
        add_action('wp_ajax_pdf_builder_workflow_action', [$this, 'ajax_workflow_action']);

        // Hooks pour le versioning
        add_action('pdf_builder_document_saved', [$this, 'create_version_snapshot'], 10, 2);
        add_action('pdf_builder_document_restored', [$this, 'log_version_restore'], 10, 2);

        // Hooks pour les notifications
        add_action('pdf_builder_comment_added', [$this, 'notify_comment_mention'], 10, 2);
        add_action('pdf_builder_permission_changed', [$this, 'notify_permission_change'], 10, 3);
        add_action('pdf_builder_workflow_status_changed', [$this, 'notify_workflow_change'], 10, 3);

        // Hooks pour le nettoyage
        add_action('pdf_builder_cleanup_old_versions', [$this, 'cleanup_old_versions']);

        if (!wp_next_scheduled('pdf_builder_cleanup_old_versions')) {
            wp_schedule_event(time(), 'weekly', 'pdf_builder_cleanup_old_versions');
        }
    }

    /**
     * Partager un document avec un utilisateur
     *
     * @param int $document_id
     * @param int $user_id
     * @param string $permission_level
     * @param int $shared_by
     * @return bool
     */
    public function share_document(int $document_id, int $user_id, string $permission_level, int $shared_by): bool {
        try {
            // Vérifier que le niveau de permission existe
            if (!isset($this->permission_levels[$permission_level])) {
                throw new Exception("Niveau de permission invalide: {$permission_level}");
            }

            // Vérifier que l'utilisateur qui partage a les droits
            if (!$this->can_user_manage_sharing($shared_by, $document_id)) {
                throw new Exception("Permissions insuffisantes pour partager ce document");
            }

            global $wpdb;

            // Insérer ou mettre à jour le partage
            $result = $wpdb->replace(
                $wpdb->prefix . 'pdf_builder_document_shares',
                [
                    'document_id' => $document_id,
                    'user_id' => $user_id,
                    'permission_level' => $permission_level,
                    'shared_by' => $shared_by,
                    'shared_at' => current_time('mysql'),
                    'last_accessed' => null
                ],
                ['%d', '%d', '%s', '%d', '%s', '%s']
            );

            if ($result === false) {
                throw new Exception("Erreur lors du partage du document");
            }

            // Invalider le cache
            $this->cache->delete("document_permissions_{$document_id}");

            // Logger l'action
            $this->logger->info('Document shared', [
                'document_id' => $document_id,
                'shared_with' => $user_id,
                'permission' => $permission_level,
                'shared_by' => $shared_by
            ]);

            // Notifier l'utilisateur
            do_action('pdf_builder_permission_changed', $document_id, $user_id, $permission_level);

            return true;

        } catch (Exception $e) {
            $this->logger->error('Failed to share document', [
                'document_id' => $document_id,
                'user_id' => $user_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir les permissions d'un utilisateur pour un document
     *
     * @param int $user_id
     * @param int $document_id
     * @return string
     */
    public function get_user_permission(int $user_id, int $document_id): string {
        // Vérifier le cache d'abord
        $cache_key = "user_permission_{$user_id}_{$document_id}";
        $cached_permission = $this->cache->get($cache_key);

        if ($cached_permission !== false) {
            return $cached_permission;
        }

        global $wpdb;

        // Vérifier si l'utilisateur est le propriétaire
        $document = $this->database->get_document($document_id);
        if ($document && $document['author_id'] == $user_id) {
            $permission = 'admin';
        } else {
            // Vérifier les partages
            $share = $wpdb->get_row(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
                SELECT permission_level
                FROM {$wpdb->prefix}pdf_builder_document_shares
                WHERE document_id = %d AND user_id = %d
            ", $document_id, $user_id));

            $permission = $share ? $share->permission_level : 'none';
        }

        // Mettre en cache pour 5 minutes
        $this->cache->set($cache_key, $permission, 300);

        return $permission;
    }

    /**
     * Vérifier si un utilisateur peut effectuer une action
     *
     * @param int $user_id
     * @param int $document_id
     * @param string $required_permission
     * @return bool
     */
    public function can_user_perform_action(int $user_id, int $document_id, string $required_permission): bool {
        $user_permission = $this->get_user_permission($user_id, $document_id);
        $user_level = $this->permission_levels[$user_permission] ?? 0;
        $required_level = $this->permission_levels[$required_permission] ?? 999;

        return $user_level >= $required_level;
    }

    /**
     * Ajouter un commentaire à un document
     *
     * @param int $document_id
     * @param int $user_id
     * @param string $comment
     * @param array $metadata
     * @return int|false
     */
    public function add_comment(int $document_id, int $user_id, string $comment, array $metadata = []): int|false {
        try {
            // Vérifier les permissions
            if (!$this->can_user_perform_action($user_id, $document_id, 'comment')) {
                throw new Exception("Permissions insuffisantes pour commenter");
            }

            global $wpdb;

            $result = $wpdb->insert(
                $wpdb->prefix . 'pdf_builder_comments',
                [
                    'document_id' => $document_id,
                    'user_id' => $user_id,
                    'comment' => $comment,
                    'metadata' => wp_json_encode($metadata),
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ],
                ['%d', '%d', '%s', '%s', '%s', '%s']
            );

            if ($result === false) {
                throw new Exception("Erreur lors de l'ajout du commentaire");
            }

            $comment_id = $wpdb->insert_id;

            // Invalider le cache
            $this->cache->delete("document_comments_{$document_id}");

            // Logger l'action
            $this->logger->info('Comment added', [
                'comment_id' => $comment_id,
                'document_id' => $document_id,
                'user_id' => $user_id
            ]);

            // Déclencher les notifications
            do_action('pdf_builder_comment_added', $comment_id, $document_id);

            return $comment_id;

        } catch (Exception $e) {
            $this->logger->error('Failed to add comment', [
                'document_id' => $document_id,
                'user_id' => $user_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir les commentaires d'un document
     *
     * @param int $document_id
     * @param int $user_id
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get_comments(int $document_id, int $user_id, int $limit = 50, int $offset = 0): array {
        // Vérifier les permissions
        if (!$this->can_user_perform_action($user_id, $document_id, 'view')) {
            return [];
        }

        // Vérifier le cache
        $cache_key = "document_comments_{$document_id}_{$limit}_{$offset}";
        $cached_comments = $this->cache->get($cache_key);

        if ($cached_comments !== false) {
            return $cached_comments;
        }

        global $wpdb;

        $comments = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT
                c.*,
                u.display_name as author_name,
                u.user_email as author_email
            FROM {$wpdb->prefix}pdf_builder_comments c
            LEFT JOIN {$wpdb->users} u ON c.user_id = u.ID
            WHERE c.document_id = %d
            ORDER BY c.created_at DESC
            LIMIT %d OFFSET %d
        ", $document_id, $limit, $offset));

        // Enrichir les commentaires
        foreach ($comments as &$comment) {
            $comment->metadata = json_decode($comment->metadata, true);
            $comment->is_own = $comment->user_id == $user_id;
            $comment->can_edit = $this->can_user_edit_comment($user_id, $comment->id);
            $comment->can_delete = $this->can_user_delete_comment($user_id, $comment->id);
        }

        // Mettre en cache pour 5 minutes
        $this->cache->set($cache_key, $comments, 300);

        return $comments;
    }

    /**
     * Créer un snapshot de version lors de la sauvegarde
     *
     * @param int $document_id
     * @param array $document_data
     */
    public function create_version_snapshot(int $document_id, array $document_data): void {
        try {
            global $wpdb;

            // Obtenir le numéro de version suivant
            $next_version = $wpdb->get_var(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
                SELECT COALESCE(MAX(version_number), 0) + 1
                FROM {$wpdb->prefix}pdf_builder_document_versions
                WHERE document_id = %d
            ", $document_id));

            // Créer le snapshot
            $wpdb->insert(
                $wpdb->prefix . 'pdf_builder_document_versions',
                [
                    'document_id' => $document_id,
                    'version_number' => $next_version,
                    'data' => wp_json_encode($document_data),
                    'created_by' => get_current_user_id(),
                    'change_summary' => 'Sauvegarde automatique',
                    'created_at' => current_time('mysql')
                ],
                ['%d', '%d', '%s', '%d', '%s', '%s']
            );

            // Garder seulement les 20 dernières versions
            $this->cleanup_old_versions_for_document($document_id, 20);

            $this->logger->info('Version snapshot created', [
                'document_id' => $document_id,
                'version' => $next_version
            ]);

        } catch (Exception $e) {
            $this->logger->error('Failed to create version snapshot', [
                'document_id' => $document_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Restaurer une version de document
     *
     * @param int $document_id
     * @param int $version_number
     * @param int $user_id
     * @return bool
     */
    public function restore_version(int $document_id, int $version_number, int $user_id): bool {
        try {
            // Vérifier les permissions
            if (!$this->can_user_perform_action($user_id, $document_id, 'edit')) {
                throw new Exception("Permissions insuffisantes pour restaurer une version");
            }

            global $wpdb;

            // Récupérer la version
            $version = $wpdb->get_row(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
                SELECT data
                FROM {$wpdb->prefix}pdf_builder_document_versions
                WHERE document_id = %d AND version_number = %d
            ", $document_id, $version_number));

            if (!$version) {
                throw new Exception("Version introuvable");
            }

            $version_data = json_decode($version->data, true);
            if (!$version_data) {
                throw new Exception("Données de version corrompues");
            }

            // Restaurer les données
            $result = $this->database->update_document($document_id, $version_data);

            if (!$result) {
                throw new Exception("Erreur lors de la restauration");
            }

            // Logger l'action
            $this->logger->info('Document version restored', [
                'document_id' => $document_id,
                'version' => $version_number,
                'restored_by' => $user_id
            ]);

            // Créer un nouveau snapshot après restauration
            do_action('pdf_builder_document_restored', $document_id, $version_number);

            return true;

        } catch (Exception $e) {
            $this->logger->error('Failed to restore version', [
                'document_id' => $document_id,
                'version' => $version_number,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir l'historique des versions d'un document
     *
     * @param int $document_id
     * @param int $user_id
     * @return array
     */
    public function get_version_history(int $document_id, int $user_id): array {
        // Vérifier les permissions
        if (!$this->can_user_perform_action($user_id, $document_id, 'view')) {
            return [];
        }

        global $wpdb;

        $versions = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT
                v.*,
                u.display_name as author_name
            FROM {$wpdb->prefix}pdf_builder_document_versions v
            LEFT JOIN {$wpdb->users} u ON v.created_by = u.ID
            WHERE v.document_id = %d
            ORDER BY v.version_number DESC
            LIMIT 50
        ", $document_id));

        return array_map(function($version) {
            return [
                'id' => $version->id,
                'version_number' => $version->version_number,
                'change_summary' => $version->change_summary,
                'author_name' => $version->author_name,
                'created_at' => $version->created_at,
                'can_restore' => current_user_can('edit_pdf_documents')
            ];
        }, $versions);
    }

    /**
     * Mettre à jour le statut du workflow
     *
     * @param int $document_id
     * @param string $status
     * @param int $user_id
     * @param string $comment
     * @return bool
     */
    public function update_workflow_status(int $document_id, string $status, int $user_id, string $comment = ''): bool {
        try {
            // Vérifier que le statut existe
            if (!isset($this->workflow_statuses[$status])) {
                throw new Exception("Statut de workflow invalide: {$status}");
            }

            // Vérifier les permissions
            if (!$this->can_user_perform_action($user_id, $document_id, 'approve')) {
                throw new Exception("Permissions insuffisantes pour changer le statut");
            }

            global $wpdb;

            // Mettre à jour le statut
            $result = $wpdb->update(
                $wpdb->prefix . 'pdf_builder_documents',
                [
                    'workflow_status' => $status,
                    'updated_at' => current_time('mysql')
                ],
                ['id' => $document_id],
                ['%s', '%s'],
                ['%d']
            );

            if ($result === false) {
                throw new Exception("Erreur lors de la mise à jour du statut");
            }

            // Ajouter une entrée dans l'historique du workflow
            $wpdb->insert(
                $wpdb->prefix . 'pdf_builder_workflow_history',
                [
                    'document_id' => $document_id,
                    'user_id' => $user_id,
                    'action' => 'status_change',
                    'old_value' => '', // À récupérer si nécessaire
                    'new_value' => $status,
                    'comment' => $comment,
                    'created_at' => current_time('mysql')
                ],
                ['%d', '%d', '%s', '%s', '%s', '%s', '%s']
            );

            // Invalider le cache
            $this->cache->delete("document_{$document_id}");

            // Logger l'action
            $this->logger->info('Workflow status updated', [
                'document_id' => $document_id,
                'status' => $status,
                'user_id' => $user_id
            ]);

            // Notifier les changements
            do_action('pdf_builder_workflow_status_changed', $document_id, $status, $user_id);

            return true;

        } catch (Exception $e) {
            $this->logger->error('Failed to update workflow status', [
                'document_id' => $document_id,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir l'historique du workflow
     *
     * @param int $document_id
     * @param int $user_id
     * @return array
     */
    public function get_workflow_history(int $document_id, int $user_id): array {
        // Vérifier les permissions
        if (!$this->can_user_perform_action($user_id, $document_id, 'view')) {
            return [];
        }

        global $wpdb;

        $history = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT
                h.*,
                u.display_name as author_name
            FROM {$wpdb->prefix}pdf_builder_workflow_history h
            LEFT JOIN {$wpdb->users} u ON h.user_id = u.ID
            WHERE h.document_id = %d
            ORDER BY h.created_at DESC
            LIMIT 100
        ", $document_id));

        return array_map(function($entry) {
            return [
                'id' => $entry->id,
                'action' => $entry->action,
                'old_value' => $entry->old_value,
                'new_value' => $entry->new_value,
                'comment' => $entry->comment,
                'author_name' => $entry->author_name,
                'created_at' => $entry->created_at
            ];
        }, $history);
    }

    /**
     * Vérifier si un utilisateur peut gérer le partage d'un document
     *
     * @param int $user_id
     * @param int $document_id
     * @return bool
     */
    private function can_user_manage_sharing(int $user_id, int $document_id): bool {
        $document = $this->database->get_document($document_id);
        return $document && ($document['author_id'] == $user_id || current_user_can('manage_options'));
    }

    /**
     * Vérifier si un utilisateur peut éditer un commentaire
     *
     * @param int $user_id
     * @param int $comment_id
     * @return bool
     */
    private function can_user_edit_comment(int $user_id, int $comment_id): bool {
        global $wpdb;

        $comment = $wpdb->get_row(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT user_id, created_at
            FROM {$wpdb->prefix}pdf_builder_comments
            WHERE id = %d
        ", $comment_id));

        if (!$comment) {
            return false;
        }

        // L'auteur peut éditer ses commentaires pendant 24h
        if ($comment->user_id == $user_id) {
            $comment_time = strtotime($comment->created_at);
            $time_diff = time() - $comment_time;
            return $time_diff < (24 * 60 * 60); // 24 heures
        }

        // Les admins peuvent éditer tous les commentaires
        return current_user_can('manage_options');
    }

    /**
     * Vérifier si un utilisateur peut supprimer un commentaire
     *
     * @param int $user_id
     * @param int $comment_id
     * @return bool
     */
    private function can_user_delete_comment(int $user_id, int $comment_id): bool {
        global $wpdb;

        $comment = $wpdb->get_row(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT user_id
            FROM {$wpdb->prefix}pdf_builder_comments
            WHERE id = %d
        ", $comment_id));

        if (!$comment) {
            return false;
        }

        // L'auteur peut supprimer ses commentaires
        if ($comment->user_id == $user_id) {
            return true;
        }

        // Les admins peuvent supprimer tous les commentaires
        return current_user_can('manage_options');
    }

    /**
     * Nettoyer les anciennes versions d'un document
     *
     * @param int $document_id
     * @param int $keep_versions
     */
    private function cleanup_old_versions_for_document(int $document_id, int $keep_versions = 20): void {
        global $wpdb;

        $wpdb->query(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            DELETE FROM {$wpdb->prefix}pdf_builder_document_versions
            WHERE document_id = %d
            AND version_number NOT IN (
                SELECT version_number
                FROM (
                    SELECT version_number
                    FROM {$wpdb->prefix}pdf_builder_document_versions
                    WHERE document_id = %d
                    ORDER BY version_number DESC
                    LIMIT %d
                ) latest_versions
            )
        ", $document_id, $document_id, $keep_versions));
    }

    /**
     * Nettoyer les anciennes versions de tous les documents
     */
    public function cleanup_old_versions(): void {
        global $wpdb;

        // Supprimer les versions de plus de 90 jours pour les documents anciens
        $wpdb->query("
            DELETE FROM {$wpdb->prefix}pdf_builder_document_versions
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
            AND document_id IN (
                SELECT id FROM {$wpdb->prefix}pdf_builder_documents
                WHERE updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
            )
        ");

        $this->logger->info('Old document versions cleaned up');
    }

    /**
     * Notifier les mentions dans les commentaires
     *
     * @param int $comment_id
     * @param int $document_id
     */
    public function notify_comment_mention(int $comment_id, int $document_id): void {
        // Logique de notification des mentions @username
        // À implémenter selon les besoins
    }

    /**
     * Notifier les changements de permissions
     *
     * @param int $document_id
     * @param int $user_id
     * @param string $permission
     */
    public function notify_permission_change(int $document_id, int $user_id, string $permission): void {
        $user = get_user_by('id', $user_id);
        if (!$user) return;

        $document = $this->database->get_document($document_id);
        if (!$document) return;

        $subject = sprintf(__('Permissions modifiées pour "%s"', 'pdf-builder-pro'), $document['title']);
        $message = sprintf(
            __('Bonjour %s,<br><br>Vos permissions ont été modifiées pour le document "%s".<br>Nouveau niveau: %s<br><br>Cordialement,<br>%s', 'pdf-builder-pro'),
            $user->display_name,
            $document['title'],
            $this->get_permission_label($permission),
            get_bloginfo('name')
        );

        wp_mail($user->user_email, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
    }

    /**
     * Notifier les changements de workflow
     *
     * @param int $document_id
     * @param string $status
     * @param int $user_id
     */
    public function notify_workflow_change(int $document_id, string $status, int $user_id): void {
        // Notifier tous les utilisateurs ayant accès au document
        global $wpdb;

        $users = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT DISTINCT u.ID, u.user_email, u.display_name
            FROM {$wpdb->users} u
            INNER JOIN {$wpdb->prefix}pdf_builder_document_shares ds ON u.ID = ds.user_id
            WHERE ds.document_id = %d AND ds.permission_level IN ('edit', 'approve', 'admin')
            AND u.ID != %d
        ", $document_id, $user_id));

        $document = $this->database->get_document($document_id);
        if (!$document) return;

        $status_label = $this->workflow_statuses[$status] ?? $status;

        foreach ($users as $user) {
            $subject = sprintf(__('Statut modifié pour "%s"', 'pdf-builder-pro'), $document['title']);
            $message = sprintf(
                __('Bonjour %s,<br><br>Le statut du document "%s" a été changé à: %s<br><br>Cordialement,<br>%s', 'pdf-builder-pro'),
                $user->display_name,
                $document['title'],
                $status_label,
                get_bloginfo('name')
            );

            wp_mail($user->user_email, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
        }
    }

    /**
     * Obtenir le label d'une permission
     *
     * @param string $permission
     * @return string
     */
    private function get_permission_label(string $permission): string {
        $labels = [
            'none' => __('Aucune', 'pdf-builder-pro'),
            'view' => __('Voir', 'pdf-builder-pro'),
            'comment' => __('Commenter', 'pdf-builder-pro'),
            'edit' => __('Modifier', 'pdf-builder-pro'),
            'approve' => __('Approuver', 'pdf-builder-pro'),
            'admin' => __('Administrer', 'pdf-builder-pro')
        ];

        return $labels[$permission] ?? $permission;
    }

    /**
     * AJAX: Partager un document
     */
    public function ajax_share_document(): void {
        check_ajax_referer('pdf_builder_collaboration_nonce', 'nonce');

        $document_id = intval($_POST['document_id'] ?? 0);
        $user_id = intval($_POST['user_id'] ?? 0);
        $permission = sanitize_text_field($_POST['permission'] ?? 'view');

        if (!$document_id || !$user_id) {
            wp_send_json_error(['message' => 'Paramètres invalides']);
        }

        if (!current_user_can('edit_pdf_documents')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }

        try {
            $result = $this->share_document($document_id, $user_id, $permission, get_current_user_id());
            wp_send_json_success(['message' => 'Document partagé avec succès']);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Ajouter un commentaire
     */
    public function ajax_add_comment(): void {
        check_ajax_referer('pdf_builder_collaboration_nonce', 'nonce');

        $document_id = intval($_POST['document_id'] ?? 0);
        $comment = sanitize_textarea_field($_POST['comment'] ?? '');
        $metadata = json_decode(stripslashes($_POST['metadata'] ?? '{}'), true) ?: [];

        if (!$document_id || empty($comment)) {
            wp_send_json_error(['message' => 'Paramètres invalides']);
        }

        try {
            $comment_id = $this->add_comment($document_id, get_current_user_id(), $comment, $metadata);
            wp_send_json_success([
                'comment_id' => $comment_id,
                'message' => 'Commentaire ajouté avec succès'
            ]);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Obtenir les commentaires
     */
    public function ajax_get_comments(): void {
        check_ajax_referer('pdf_builder_collaboration_nonce', 'nonce');

        $document_id = intval($_POST['document_id'] ?? 0);
        $limit = intval($_POST['limit'] ?? 50);
        $offset = intval($_POST['offset'] ?? 0);

        if (!$document_id) {
            wp_send_json_error(['message' => 'ID de document invalide']);
        }

        try {
            $comments = $this->get_comments($document_id, get_current_user_id(), $limit, $offset);
            wp_send_json_success(['comments' => $comments]);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Mettre à jour les permissions
     */
    public function ajax_update_permissions(): void {
        check_ajax_referer('pdf_builder_collaboration_nonce', 'nonce');

        $document_id = intval($_POST['document_id'] ?? 0);
        $user_id = intval($_POST['user_id'] ?? 0);
        $permission = sanitize_text_field($_POST['permission'] ?? 'view');

        if (!$document_id || !$user_id) {
            wp_send_json_error(['message' => 'Paramètres invalides']);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }

        try {
            $result = $this->share_document($document_id, $user_id, $permission, get_current_user_id());
            wp_send_json_success(['message' => 'Permissions mises à jour']);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Action de workflow
     */
    public function ajax_workflow_action(): void {
        check_ajax_referer('pdf_builder_collaboration_nonce', 'nonce');

        $document_id = intval($_POST['document_id'] ?? 0);
        $action = sanitize_text_field($_POST['action'] ?? '');
        $comment = sanitize_textarea_field($_POST['comment'] ?? '');

        if (!$document_id || !in_array($action, array_keys($this->workflow_statuses))) {
            wp_send_json_error(['message' => 'Paramètres invalides']);
        }

        try {
            $result = $this->update_workflow_status($document_id, $action, get_current_user_id(), $comment);
            wp_send_json_success(['message' => 'Statut mis à jour avec succès']);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Obtenir les statistiques de collaboration
     */
    public function get_collaboration_stats(): array {
        global $wpdb;

        $stats = $wpdb->get_row("
            SELECT
                COUNT(DISTINCT ds.document_id) as shared_documents,
                COUNT(DISTINCT ds.user_id) as active_collaborators,
                COUNT(c.id) as total_comments,
                COUNT(DISTINCT CASE WHEN ds.permission_level IN ('edit', 'approve', 'admin') THEN ds.user_id END) as power_users,
                AVG(CASE WHEN ds.last_accessed IS NOT NULL THEN TIMESTAMPDIFF(DAY, ds.shared_at, ds.last_accessed) END) as avg_engagement_days
            FROM {$wpdb->prefix}pdf_builder_document_shares ds
            LEFT JOIN {$wpdb->prefix}pdf_builder_comments c ON ds.document_id = c.document_id
            WHERE ds.shared_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");

        return [
            'shared_documents' => intval($stats->shared_documents ?? 0),
            'active_collaborators' => intval($stats->active_collaborators ?? 0),
            'total_comments' => intval($stats->total_comments ?? 0),
            'power_users' => intval($stats->power_users ?? 0),
            'avg_engagement_days' => round(floatval($stats->avg_engagement_days ?? 0), 1)
        ];
    }
}