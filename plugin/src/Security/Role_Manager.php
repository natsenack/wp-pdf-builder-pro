<?php

/**
 * Gestionnaire des rôles et permissions
 * Applique les rôles autorisés configurés dans l'onglet Rôles
 */

namespace WP_PDF_Builder_Pro\Security;

class Role_Manager
{
    /**
     * Initialise le gestionnaire des rôles
     */
    public static function init()
    {
        add_action('plugins_loaded', [__CLASS__, 'registerCapabilities'], 10);
        add_filter('user_has_cap', [__CLASS__, 'checkPdfBuilderCapability'], 10, 4);
    }

    /**
     * Enregistre la capacité personnalisée 'pdf_builder_access'
     * Cette capacité sera attribuée à WordPress dynamiquement
     */
    public static function registerCapabilities()
    {
        // Rien à faire ici, on va utiliser un filter pour vérifier les rôles
    }

    /**
     * Vérifie si l'utilisateur a accès au PDF Builder
     * Filter sur 'user_has_cap' pour intercepter les vérifications de capacités
     */
    public static function checkPdfBuilderCapability($allcaps, $cap, $args, $user)
    {
        // Vérifier la capacité spécifique 'pdf_builder_access'
        if ($cap === 'pdf_builder_access' || (isset($args[0]) && $args[0] === 'pdf_builder_access')) {
// Les administrateurs ont toujours accès
            if (isset($allcaps['manage_options'])) {
                return true;
            }

            // Vérifier si le rôle de l'utilisateur est autorisé
            $allowed_roles = get_option('pdf_builder_allowed_roles', ['administrator', 'editor', 'shop_manager']);
            $user_roles = isset($user->roles) ? $user->roles : [];
            foreach ($user_roles as $role) {
                if (in_array($role, $allowed_roles)) {
        // L'utilisateur a un rôle autorisé
                    return true;
                }
            }

            // Pas accès
            return false;
        }

        return $allcaps;
    }

    /**
     * Vérifie si l'utilisateur actuel a accès au PDF Builder
     *
     * @return bool True si accès autorisé, false sinon
     */
    public static function userCanAccessPdfBuilder()
    {
        return current_user_can('pdf_builder_access');
    }

    /**
     * Obtient les rôles autorisés
     *
     * @return array Tableau des rôles autorisés
     */
    public static function getAllowedRoles()
    {
        $allowed = get_option('pdf_builder_allowed_roles', ['administrator', 'editor', 'shop_manager']);
        return is_array($allowed) ? $allowed : ['administrator', 'editor', 'shop_manager'];
    }

    /**
     * Enregistre les rôles autorisés
     *
     * @param array $roles Tableau des rôles à autoriser
     */
    public static function setAllowedRoles($roles)
    {
        $roles = array_map('sanitize_text_field', (array) $roles);
// S'assurer que l'administrateur est toujours autorisé
        if (!in_array('administrator', $roles)) {
            $roles[] = 'administrator';
        }

        update_option('pdf_builder_allowed_roles', $roles);
        error_log('[PDF Builder] Role Manager: Allowed roles updated to: ' . implode(', ', $roles));
    }

    /**
     * Vérifie si un utilisateur a accès basé sur son rôle
     *
     * @param WP_User $user L'utilisateur à vérifier
     * @return bool True si l'utilisateur a accès
     */
    public static function userHasAccess($user)
    {
        if (!$user) {
            return false;
        }

        // Admin a toujours accès
        if (user_can($user, 'manage_options')) {
            return true;
        }

        $allowed_roles = self::getAllowedRoles();
        $user_roles = isset($user->roles) ? $user->roles : [];
        foreach ($user_roles as $role) {
            if (in_array($role, $allowed_roles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Bloque l'accès si l'utilisateur n'a pas le bon rôle
     * À appeler au début des pages admin
     */
    public static function checkAndBlockAccess()
    {
        if (!current_user_can('manage_options') && !self::userCanAccessPdfBuilder()) {
            wp_die(
                __('Vous n\'avez pas la permission d\'accéder à cette page.', 'pdf-builder-pro'),
                __('Accès refusé', 'pdf-builder-pro'),
                ['response' => 403]
            );
        }
    }

    /**
     * Alias pour checkAndBlockAccess() - compatibilité
     */
    public static function check_and_block_access()
    {
        return self::checkAndBlockAccess();
    }

    /**
     * Retourne la capacité à utiliser pour les menus/pages
     *
     * @return string La capacité WordPress appropriée
     */
    public static function getRequiredCapability()
    {
        // Retourner 'manage_options' pour la compatibilité simple
        // Mais le filtre 'user_has_cap' vérifiera les rôles autorisés
        return 'manage_options';
    }

    /**
     * Obtient les informations sur les rôles actuels
     *
     * @return array Informations détaillées
     */
    public static function getRoleInfo()
    {
        global $wp_roles;
        $allowed_roles = self::getAllowedRoles();
        $all_roles = $wp_roles->roles;
        $info = [
            'allowed_count' => count($allowed_roles),
            'allowed_roles' => $allowed_roles,
            'total_roles' => count($all_roles),
            'current_user_allowed' => current_user_can('pdf_builder_access')
        ];
        return $info;
    }
}

// Initialiser au chargement
Role_Manager::init();
