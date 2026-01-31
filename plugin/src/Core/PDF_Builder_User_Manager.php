<?php
/**
 * PDF Builder Pro - Gestion des utilisateurs et permissions
 * Contrôle l'accès aux fonctionnalités selon les rôles et permissions
 */

class PDF_Builder_User_Manager {
    private static $instance = null;

    // Rôles personnalisés
    const ROLE_PDF_EDITOR = 'pdf_editor';
    const ROLE_PDF_MANAGER = 'pdf_manager';
    const ROLE_PDF_ADMIN = 'pdf_admin';

    // Capacités (permissions)
    const CAP_VIEW_PDFS = 'view_pdfs';
    const CAP_EDIT_PDFS = 'edit_pdfs';
    const CAP_DELETE_PDFS = 'delete_pdfs';
    const CAP_PUBLISH_PDFS = 'publish_pdfs';
    const CAP_MANAGE_TEMPLATES = 'manage_templates';
    const CAP_MANAGE_SETTINGS = 'manage_settings';
    const CAP_VIEW_ANALYTICS = 'view_analytics';
    const CAP_MANAGE_BACKUPS = 'manage_backups';
    const CAP_MANAGE_DEPLOYMENTS = 'manage_deployments';
    const CAP_MANAGE_USERS = 'manage_users';

    // Niveaux d'accès
    const ACCESS_NONE = 0;
    const ACCESS_READ = 1;
    const ACCESS_WRITE = 2;
    const ACCESS_ADMIN = 3;

    // Cache des permissions
    private $permissions_cache = [];
    private $user_capabilities = [];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->register_custom_roles();
        $this->load_user_capabilities();
    }

    private function init_hooks() {
        // Actions AJAX
        add_action('wp_ajax_pdf_builder_save_user_permissions', [$this, 'save_user_permissions_ajax']);
        add_action('wp_ajax_pdf_builder_get_user_permissions', [$this, 'get_user_permissions_ajax']);
        add_action('wp_ajax_pdf_builder_create_user_role', [$this, 'create_user_role_ajax']);
        add_action('wp_ajax_pdf_builder_delete_user_role', [$this, 'delete_user_role_ajax']);

        // Filtres de permissions
        add_filter('pdf_builder_user_can', [$this, 'check_user_permission'], 10, 3);
        add_filter('pdf_builder_current_user_access', [$this, 'get_current_user_access_level'], 10, 2);

        // Actions utilisateur
        add_action('user_register', [$this, 'set_default_user_permissions']);
        add_action('profile_update', [$this, 'update_user_permissions']);
        add_action('delete_user', [$this, 'cleanup_user_permissions']);

        // Actions d'administration
        add_action('admin_init', [$this, 'register_capabilities']);
        add_action('admin_menu', [$this, 'add_user_management_menu']);

        // Nettoyage
        add_action('pdf_builder_monthly_cleanup', [$this, 'cleanup_permissions_cache']);
    }

    /**
     * Enregistre les rôles personnalisés
     */
    private function register_custom_roles() {
        // Rôle Éditeur PDF
        add_role(
            self::ROLE_PDF_EDITOR,
            pdf_builder_translate('Éditeur PDF', 'user'),
            [
                'read' => true,
                self::CAP_VIEW_PDFS => true,
                self::CAP_EDIT_PDFS => true,
            ]
        );

        // Rôle Gestionnaire PDF
        add_role(
            self::ROLE_PDF_MANAGER,
            pdf_builder_translate('Gestionnaire PDF', 'user'),
            [
                'read' => true,
                self::CAP_VIEW_PDFS => true,
                self::CAP_EDIT_PDFS => true,
                self::CAP_DELETE_PDFS => true,
                self::CAP_PUBLISH_PDFS => true,
                self::CAP_MANAGE_TEMPLATES => true,
                self::CAP_VIEW_ANALYTICS => true,
            ]
        );

        // Rôle Administrateur PDF
        add_role(
            self::ROLE_PDF_ADMIN,
            pdf_builder_translate('Administrateur PDF', 'user'),
            [
                'read' => true,
                'manage_options' => true,
                self::CAP_VIEW_PDFS => true,
                self::CAP_EDIT_PDFS => true,
                self::CAP_DELETE_PDFS => true,
                self::CAP_PUBLISH_PDFS => true,
                self::CAP_MANAGE_TEMPLATES => true,
                self::CAP_MANAGE_SETTINGS => true,
                self::CAP_VIEW_ANALYTICS => true,
                self::CAP_MANAGE_BACKUPS => true,
                self::CAP_MANAGE_DEPLOYMENTS => true,
                self::CAP_MANAGE_USERS => true,
            ]
        );
    }

    /**
     * Enregistre les capacités
     */
    public function register_capabilities() {
        $roles = [
            'administrator' => [
                self::CAP_VIEW_PDFS,
                self::CAP_EDIT_PDFS,
                self::CAP_DELETE_PDFS,
                self::CAP_PUBLISH_PDFS,
                self::CAP_MANAGE_TEMPLATES,
                self::CAP_MANAGE_SETTINGS,
                self::CAP_VIEW_ANALYTICS,
                self::CAP_MANAGE_BACKUPS,
                self::CAP_MANAGE_DEPLOYMENTS,
                self::CAP_MANAGE_USERS,
            ],
            'editor' => [
                self::CAP_VIEW_PDFS,
                self::CAP_EDIT_PDFS,
                self::CAP_PUBLISH_PDFS,
            ],
            'author' => [
                self::CAP_VIEW_PDFS,
                self::CAP_EDIT_PDFS,
            ],
            'contributor' => [
                self::CAP_VIEW_PDFS,
            ],
            self::ROLE_PDF_EDITOR => [
                self::CAP_VIEW_PDFS,
                self::CAP_EDIT_PDFS,
            ],
            self::ROLE_PDF_MANAGER => [
                self::CAP_VIEW_PDFS,
                self::CAP_EDIT_PDFS,
                self::CAP_DELETE_PDFS,
                self::CAP_PUBLISH_PDFS,
                self::CAP_MANAGE_TEMPLATES,
                self::CAP_VIEW_ANALYTICS,
            ],
            self::ROLE_PDF_ADMIN => [
                self::CAP_VIEW_PDFS,
                self::CAP_EDIT_PDFS,
                self::CAP_DELETE_PDFS,
                self::CAP_PUBLISH_PDFS,
                self::CAP_MANAGE_TEMPLATES,
                self::CAP_MANAGE_SETTINGS,
                self::CAP_VIEW_ANALYTICS,
                self::CAP_MANAGE_BACKUPS,
                self::CAP_MANAGE_DEPLOYMENTS,
                self::CAP_MANAGE_USERS,
            ]
        ];

        foreach ($roles as $role_name => $capabilities) {
            $role = get_role($role_name);

            if ($role) {
                foreach ($capabilities as $cap) {
                    $role->add_cap($cap);
                }
            }
        }
    }

    /**
     * Charge les capacités utilisateur
     */
    private function load_user_capabilities() {
        $this->user_capabilities = pdf_builder_get_option('pdf_builder_user_capabilities', []);
    }

    /**
     * Ajoute le menu de gestion des utilisateurs
     */
    public function add_user_management_menu() {
        add_submenu_page(
            'pdf-builder-settings',
            pdf_builder_translate('Gestion des utilisateurs', 'user'),
            pdf_builder_translate('Utilisateurs', 'user'),
            'manage_options',
            'pdf-builder-users',
            [$this, 'render_user_management_page']
        );
    }

    /**
     * Rend la page de gestion des utilisateurs
     */
    public function render_user_management_page() {
        if (!current_user_can('manage_options')) {
            wp_die(pdf_builder_translate('Accès refusé', 'user'));
        }

        $users = $this->get_pdf_users();
        $roles = $this->get_pdf_roles();
        $capabilities = $this->get_all_capabilities();

        include PDF_BUILDER_PLUGIN_DIR . 'resources/templates/admin/user-management.php';
    }

    /**
     * Vérifie si un utilisateur a une permission
     */
    public function check_user_permission($capability, $user_id = null, $context = null) {
        $user_id = $user_id ?: get_current_user_id();

        if (!$user_id) {
            return false;
        }

        $user = get_user_by('id', $user_id);

        if (!$user) {
            return false;
        }

        // Vérifier d'abord les capacités WordPress standard
        if ($user->has_cap($capability)) {
            return true;
        }

        // Vérifier les permissions personnalisées
        $user_permissions = $this->get_user_permissions($user_id);

        if (isset($user_permissions[$capability])) {
            return $user_permissions[$capability];
        }

        // Vérifier les permissions par contexte
        if ($context) {
            $context_permissions = $this->get_context_permissions($user_id, $context);

            if (isset($context_permissions[$capability])) {
                return $context_permissions[$capability];
            }
        }

        return false;
    }

    /**
     * Obtient le niveau d'accès d'un utilisateur
     */
    public function get_current_user_access_level($context = null) {
        $user_id = get_current_user_id();

        if (!$user_id) {
            return self::ACCESS_NONE;
        }

        $user = get_user_by('id', $user_id);

        if (!$user) {
            return self::ACCESS_NONE;
        }

        // Administrateur
        if ($user->has_cap('manage_options')) {
            return self::ACCESS_ADMIN;
        }

        // Gestionnaire PDF
        if ($user->has_cap(self::CAP_MANAGE_SETTINGS)) {
            return self::ACCESS_ADMIN;
        }

        // Éditeur
        if ($user->has_cap(self::CAP_EDIT_PDFS)) {
            return self::ACCESS_WRITE;
        }

        // Lecteur
        if ($user->has_cap(self::CAP_VIEW_PDFS)) {
            return self::ACCESS_READ;
        }

        return self::ACCESS_NONE;
    }

    /**
     * Obtient les permissions d'un utilisateur
     */
    public function get_user_permissions($user_id) {
        // Vérifier le cache
        if (isset($this->permissions_cache[$user_id])) {
            return $this->permissions_cache[$user_id];
        }

        $permissions = get_user_meta($user_id, 'pdf_builder_permissions', true);

        if (!$permissions) {
            $permissions = [];
        }

        // Fusionner avec les capacités par défaut du rôle
        $user = get_user_by('id', $user_id);

        if ($user) {
            $role_caps = $this->get_role_capabilities($user->roles);

            foreach ($role_caps as $cap => $granted) {
                if (!isset($permissions[$cap])) {
                    $permissions[$cap] = $granted;
                }
            }
        }

        $this->permissions_cache[$user_id] = $permissions;

        return $permissions;
    }

    /**
     * Obtient les capacités d'un rôle
     */
    private function get_role_capabilities($roles) {
        $capabilities = [];

        foreach ($roles as $role_name) {
            $role = get_role($role_name);

            if ($role) {
                $capabilities = array_merge($capabilities, $role->capabilities);
            }
        }

        return $capabilities;
    }

    /**
     * Obtient les permissions par contexte
     */
    private function get_context_permissions($user_id, $context) {
        $context_permissions = get_user_meta($user_id, 'pdf_builder_context_permissions', true);

        if (!$context_permissions || !isset($context_permissions[$context])) {
            return [];
        }

        return $context_permissions[$context];
    }

    /**
     * Définit les permissions par défaut pour un nouvel utilisateur
     */
    public function set_default_user_permissions($user_id) {
        $default_permissions = [
            self::CAP_VIEW_PDFS => true,
        ];

        update_user_meta($user_id, 'pdf_builder_permissions', $default_permissions);
    }

    /**
     * Met à jour les permissions d'un utilisateur
     */
    public function update_user_permissions($user_id) {
        // Vider le cache des permissions
        unset($this->permissions_cache[$user_id]);
    }

    /**
     * Nettoie les permissions d'un utilisateur supprimé
     */
    public function cleanup_user_permissions($user_id) {
        delete_user_meta($user_id, 'pdf_builder_permissions');
        delete_user_meta($user_id, 'pdf_builder_context_permissions');

        unset($this->permissions_cache[$user_id]);
    }

    /**
     * Sauvegarde les permissions d'un utilisateur
     */
    public function save_user_permissions($user_id, $permissions, $context_permissions = []) {
        update_user_meta($user_id, 'pdf_builder_permissions', $permissions);
        update_user_meta($user_id, 'pdf_builder_context_permissions', $context_permissions);

        // Vider le cache
        unset($this->permissions_cache[$user_id]);

        // Logger l'action
                'user_id' => $user_id,
                'permissions' => $permissions,
                'context_permissions' => $context_permissions
            ]);
        }

        return true;
    }

    /**
     * Crée un rôle personnalisé
     */
    public function create_custom_role($role_name, $display_name, $capabilities) {
        $result = add_role($role_name, $display_name, $capabilities);

        if ($result) {
            // Logger la création
                    'role_name' => $role_name,
                    'display_name' => $display_name,
                    'capabilities' => $capabilities
                ]);
            }
        }

        return $result;
    }

    /**
     * Supprime un rôle personnalisé
     */
    public function delete_custom_role($role_name) {
        $result = remove_role($role_name);

        if ($result) {
            // Logger la suppression
                    'role_name' => $role_name
                ]);
            }
        }

        return $result;
    }

    /**
     * Obtient tous les utilisateurs PDF
     */
    public function get_pdf_users() {
        $users = get_users([
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'pdf_builder_permissions',
                    'compare' => 'EXISTS'
                ],
                [
                    'key' => 'pdf_builder_context_permissions',
                    'compare' => 'EXISTS'
                ]
            ]
        ]);

        $pdf_users = [];

        foreach ($users as $user) {
            $pdf_users[] = [
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email,
                'role' => $user->roles[0] ?? 'subscriber',
                'permissions' => $this->get_user_permissions($user->ID),
                'last_login' => get_user_meta($user->ID, 'last_login', true)
            ];
        }

        return $pdf_users;
    }

    /**
     * Obtient tous les rôles PDF
     */
    public function get_pdf_roles() {
        global $wp_roles;

        $roles = [];

        if ($wp_roles) {
            foreach ($wp_roles->roles as $role_name => $role_info) {
                if (strpos($role_name, 'pdf_') === 0 || in_array($role_name, ['administrator', 'editor', 'author', 'contributor'])) {
                    $roles[$role_name] = [
                        'name' => $role_info['name'],
                        'capabilities' => $role_info['capabilities']
                    ];
                }
            }
        }

        return $roles;
    }

    /**
     * Obtient toutes les capacités
     */
    public function get_all_capabilities() {
        return [
            self::CAP_VIEW_PDFS => pdf_builder_translate('Voir les PDFs', 'capability'),
            self::CAP_EDIT_PDFS => pdf_builder_translate('Modifier les PDFs', 'capability'),
            self::CAP_DELETE_PDFS => pdf_builder_translate('Supprimer les PDFs', 'capability'),
            self::CAP_PUBLISH_PDFS => pdf_builder_translate('Publier les PDFs', 'capability'),
            self::CAP_MANAGE_TEMPLATES => pdf_builder_translate('Gérer les modèles', 'capability'),
            self::CAP_MANAGE_SETTINGS => pdf_builder_translate('Gérer les paramètres', 'capability'),
            self::CAP_VIEW_ANALYTICS => pdf_builder_translate('Voir les analyses', 'capability'),
            self::CAP_MANAGE_BACKUPS => pdf_builder_translate('Gérer les sauvegardes', 'capability'),
            self::CAP_MANAGE_DEPLOYMENTS => pdf_builder_translate('Gérer les déploiements', 'capability'),
            self::CAP_MANAGE_USERS => pdf_builder_translate('Gérer les utilisateurs', 'capability'),
        ];
    }

    /**
     * Assigne un rôle à un utilisateur
     */
    public function assign_user_role($user_id, $role_name) {
        $user = get_user_by('id', $user_id);

        if (!$user) {
            return false;
        }

        $user->set_role($role_name);

        // Logger l'action
                'user_id' => $user_id,
                'role' => $role_name
            ]);
        }

        return true;
    }

    /**
     * Nettoie le cache des permissions
     */
    public function cleanup_permissions_cache() {
        $this->permissions_cache = [];
    }

    /**
     * AJAX - Sauvegarde les permissions utilisateur
     */
    public function save_user_permissions_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $user_id = intval($_POST['user_id'] ?? 0);
            $permissions = $_POST['permissions'] ?? [];
            $context_permissions = $_POST['context_permissions'] ?? [];

            if (!$user_id) {
                wp_send_json_error(['message' => 'ID utilisateur manquant']);
                return;
            }

            $success = $this->save_user_permissions($user_id, $permissions, $context_permissions);

            if ($success) {
                wp_send_json_success([
                    'message' => pdf_builder_translate('Permissions sauvegardées avec succès', 'user')
                ]);
            } else {
                wp_send_json_error(['message' => pdf_builder_translate('Erreur lors de la sauvegarde', 'user')]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient les permissions utilisateur
     */
    public function get_user_permissions_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            $user_id = intval($_POST['user_id'] ?? get_current_user_id());

            $permissions = $this->get_user_permissions($user_id);
            $capabilities = $this->get_all_capabilities();

            wp_send_json_success([
                'message' => 'Permissions récupérées',
                'permissions' => $permissions,
                'capabilities' => $capabilities
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Crée un rôle utilisateur
     */
    public function create_user_role_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $role_name = sanitize_key($_POST['role_name'] ?? '');
            $display_name = sanitize_text_field($_POST['display_name'] ?? '');
            $capabilities = $_POST['capabilities'] ?? [];

            if (empty($role_name) || empty($display_name)) {
                wp_send_json_error(['message' => 'Nom du rôle manquant']);
                return;
            }

            $capabilities = array_map('sanitize_text_field', $capabilities);

            $role = $this->create_custom_role($role_name, $display_name, $capabilities);

            if ($role) {
                wp_send_json_success([
                    'message' => pdf_builder_translate('Rôle créé avec succès', 'user'),
                    'role' => $role
                ]);
            } else {
                wp_send_json_error(['message' => pdf_builder_translate('Erreur lors de la création du rôle', 'user')]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Supprime un rôle utilisateur
     */
    public function delete_user_role_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $role_name = sanitize_key($_POST['role_name'] ?? '');

            if (empty($role_name)) {
                wp_send_json_error(['message' => 'Nom du rôle manquant']);
                return;
            }

            $success = $this->delete_custom_role($role_name);

            if ($success) {
                wp_send_json_success([
                    'message' => pdf_builder_translate('Rôle supprimé avec succès', 'user')
                ]);
            } else {
                wp_send_json_error(['message' => pdf_builder_translate('Erreur lors de la suppression du rôle', 'user')]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}

// Fonctions globales
function pdf_builder_user_manager() {
    return PDF_Builder_User_Manager::get_instance();
}

function pdf_builder_user_can($capability, $user_id = null, $context = null) {
    return PDF_Builder_User_Manager::get_instance()->check_user_permission($capability, $user_id, $context);
}

function pdf_builder_current_user_access($context = null) {
    return PDF_Builder_User_Manager::get_instance()->get_current_user_access_level($context);
}

function pdf_builder_get_user_permissions($user_id = null) {
    $user_id = $user_id ?: get_current_user_id();
    return PDF_Builder_User_Manager::get_instance()->get_user_permissions($user_id);
}

function pdf_builder_save_user_permissions($user_id, $permissions, $context_permissions = []) {
    return PDF_Builder_User_Manager::get_instance()->save_user_permissions($user_id, $permissions, $context_permissions);
}

function pdf_builder_assign_user_role($user_id, $role) {
    return PDF_Builder_User_Manager::get_instance()->assign_user_role($user_id, $role);
}

function pdf_builder_get_pdf_users() {
    return PDF_Builder_User_Manager::get_instance()->get_pdf_users();
}

function pdf_builder_get_pdf_roles() {
    return PDF_Builder_User_Manager::get_instance()->get_pdf_roles();
}

// Vérifications d'accès pour les actions critiques
function pdf_builder_check_access($capability, $context = null) {
    if (!pdf_builder_user_can($capability, null, $context)) {
        wp_die(pdf_builder_translate('Accès refusé - Permissions insuffisantes', 'access'));
    }
}

// Initialiser le système de gestion des utilisateurs
add_action('plugins_loaded', function() {
    PDF_Builder_User_Manager::get_instance();
});




