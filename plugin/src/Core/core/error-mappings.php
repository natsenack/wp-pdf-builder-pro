<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed
/**
 * PDF Builder Error Mappings
 *
 * Centralise tous les messages d'erreur utilisés dans le plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_Error_Mappings {

    // ==========================================
    // CODES D'ERREUR
    // ==========================================

    private static $error_codes = [
        // Erreurs générales
        'GENERAL_ERROR' => 'PDF_BUILDER_GENERAL_ERROR',
        'VALIDATION_ERROR' => 'PDF_BUILDER_VALIDATION_ERROR',
        'PERMISSION_ERROR' => 'PDF_BUILDER_PERMISSION_ERROR',
        'SECURITY_ERROR' => 'PDF_BUILDER_SECURITY_ERROR',
        'DATABASE_ERROR' => 'PDF_BUILDER_DATABASE_ERROR',
        'FILE_ERROR' => 'PDF_BUILDER_FILE_ERROR',
        'NETWORK_ERROR' => 'PDF_BUILDER_NETWORK_ERROR',
        'MEMORY_ERROR' => 'PDF_BUILDER_MEMORY_ERROR',
        'TIMEOUT_ERROR' => 'PDF_BUILDER_TIMEOUT_ERROR',

        // Erreurs de canvas
        'CANVAS_INIT_ERROR' => 'PDF_BUILDER_CANVAS_INIT_ERROR',
        'CANVAS_RENDER_ERROR' => 'PDF_BUILDER_CANVAS_RENDER_ERROR',
        'CANVAS_EXPORT_ERROR' => 'PDF_BUILDER_CANVAS_EXPORT_ERROR',
        'CANVAS_SAVE_ERROR' => 'PDF_BUILDER_CANVAS_SAVE_ERROR',
        'CANVAS_LOAD_ERROR' => 'PDF_BUILDER_CANVAS_LOAD_ERROR',

        // Erreurs d'éléments
        'ELEMENT_CREATE_ERROR' => 'PDF_BUILDER_ELEMENT_CREATE_ERROR',
        'ELEMENT_UPDATE_ERROR' => 'PDF_BUILDER_ELEMENT_UPDATE_ERROR',
        'ELEMENT_DELETE_ERROR' => 'PDF_BUILDER_ELEMENT_DELETE_ERROR',
        'ELEMENT_LOAD_ERROR' => 'PDF_BUILDER_ELEMENT_LOAD_ERROR',
        'ELEMENT_VALIDATION_ERROR' => 'PDF_BUILDER_ELEMENT_VALIDATION_ERROR',

        // Erreurs de template
        'TEMPLATE_CREATE_ERROR' => 'PDF_BUILDER_TEMPLATE_CREATE_ERROR',
        'TEMPLATE_UPDATE_ERROR' => 'PDF_BUILDER_TEMPLATE_UPDATE_ERROR',
        'TEMPLATE_DELETE_ERROR' => 'PDF_BUILDER_TEMPLATE_DELETE_ERROR',
        'TEMPLATE_LOAD_ERROR' => 'PDF_BUILDER_TEMPLATE_LOAD_ERROR',
        'TEMPLATE_EXPORT_ERROR' => 'PDF_BUILDER_TEMPLATE_EXPORT_ERROR',

        // Erreurs d'upload
        'UPLOAD_SIZE_ERROR' => 'PDF_BUILDER_UPLOAD_SIZE_ERROR',
        'UPLOAD_TYPE_ERROR' => 'PDF_BUILDER_UPLOAD_TYPE_ERROR',
        'UPLOAD_SECURITY_ERROR' => 'PDF_BUILDER_UPLOAD_SECURITY_ERROR',
        'UPLOAD_SAVE_ERROR' => 'PDF_BUILDER_UPLOAD_SAVE_ERROR',

        // Erreurs AJAX
        'AJAX_INVALID_ACTION' => 'PDF_BUILDER_AJAX_INVALID_ACTION',
        'AJAX_MISSING_DATA' => 'PDF_BUILDER_AJAX_MISSING_DATA',
        'AJAX_INVALID_DATA' => 'PDF_BUILDER_AJAX_INVALID_DATA',
        'AJAX_PROCESSING_ERROR' => 'PDF_BUILDER_AJAX_PROCESSING_ERROR',

        // Erreurs de configuration
        'CONFIG_LOAD_ERROR' => 'PDF_BUILDER_CONFIG_LOAD_ERROR',
        'CONFIG_SAVE_ERROR' => 'PDF_BUILDER_CONFIG_SAVE_ERROR',
        'CONFIG_VALIDATION_ERROR' => 'PDF_BUILDER_CONFIG_VALIDATION_ERROR'
    ];

    // ==========================================
    // MESSAGES D'ERREUR
    // ==========================================

    private static $error_messages = [
        // Erreurs générales
        'PDF_BUILDER_GENERAL_ERROR' => 'Une erreur inattendue s\'est produite. Veuillez réessayer.',
        'PDF_BUILDER_VALIDATION_ERROR' => 'Les données fournies sont invalides.',
        'PDF_BUILDER_PERMISSION_ERROR' => 'Vous n\'avez pas les permissions nécessaires pour effectuer cette action.',
        'PDF_BUILDER_SECURITY_ERROR' => 'Violation de sécurité détectée.',
        'PDF_BUILDER_DATABASE_ERROR' => 'Erreur de base de données. Veuillez contacter l\'administrateur.',
        'PDF_BUILDER_FILE_ERROR' => 'Erreur de fichier. Vérifiez les permissions et l\'espace disque.',
        'PDF_BUILDER_NETWORK_ERROR' => 'Erreur de réseau. Vérifiez votre connexion internet.',
        'PDF_BUILDER_MEMORY_ERROR' => 'Mémoire insuffisante. Essayez de réduire la taille du document.',
        'PDF_BUILDER_TIMEOUT_ERROR' => 'Délai d\'attente dépassé. Veuillez réessayer.',

        // Erreurs de canvas
        'PDF_BUILDER_CANVAS_INIT_ERROR' => 'Impossible d\'initialiser le canvas.',
        'PDF_BUILDER_CANVAS_RENDER_ERROR' => 'Erreur lors du rendu du canvas.',
        'PDF_BUILDER_CANVAS_EXPORT_ERROR' => 'Erreur lors de l\'export du canvas.',
        'PDF_BUILDER_CANVAS_SAVE_ERROR' => 'Erreur lors de la sauvegarde du canvas.',
        'PDF_BUILDER_CANVAS_LOAD_ERROR' => 'Erreur lors du chargement du canvas.',

        // Erreurs d'éléments
        'PDF_BUILDER_ELEMENT_CREATE_ERROR' => 'Impossible de créer l\'élément.',
        'PDF_BUILDER_ELEMENT_UPDATE_ERROR' => 'Impossible de mettre à jour l\'élément.',
        'PDF_BUILDER_ELEMENT_DELETE_ERROR' => 'Impossible de supprimer l\'élément.',
        'PDF_BUILDER_ELEMENT_LOAD_ERROR' => 'Impossible de charger l\'élément.',
        'PDF_BUILDER_ELEMENT_VALIDATION_ERROR' => 'L\'élément contient des données invalides.',

        // Erreurs de template
        'PDF_BUILDER_TEMPLATE_CREATE_ERROR' => 'Impossible de créer le template.',
        'PDF_BUILDER_TEMPLATE_UPDATE_ERROR' => 'Impossible de mettre à jour le template.',
        'PDF_BUILDER_TEMPLATE_DELETE_ERROR' => 'Impossible de supprimer le template.',
        'PDF_BUILDER_TEMPLATE_LOAD_ERROR' => 'Impossible de charger le template.',
        'PDF_BUILDER_TEMPLATE_EXPORT_ERROR' => 'Erreur lors de l\'export du template.',

        // Erreurs d'upload
        'PDF_BUILDER_UPLOAD_SIZE_ERROR' => 'Le fichier est trop volumineux.',
        'PDF_BUILDER_UPLOAD_TYPE_ERROR' => 'Type de fichier non autorisé.',
        'PDF_BUILDER_UPLOAD_SECURITY_ERROR' => 'Fichier potentiellement dangereux détecté.',
        'PDF_BUILDER_UPLOAD_SAVE_ERROR' => 'Erreur lors de la sauvegarde du fichier.',

        // Erreurs AJAX
        'PDF_BUILDER_AJAX_INVALID_ACTION' => 'Action AJAX invalide.',
        'PDF_BUILDER_AJAX_MISSING_DATA' => 'Données manquantes dans la requête.',
        'PDF_BUILDER_AJAX_INVALID_DATA' => 'Données invalides dans la requête.',
        'PDF_BUILDER_AJAX_PROCESSING_ERROR' => 'Erreur lors du traitement de la requête.',

        // Erreurs de configuration
        'PDF_BUILDER_CONFIG_LOAD_ERROR' => 'Impossible de charger la configuration.',
        'PDF_BUILDER_CONFIG_SAVE_ERROR' => 'Impossible de sauvegarder la configuration.',
        'PDF_BUILDER_CONFIG_VALIDATION_ERROR' => 'Configuration invalide.'
    ];

    // ==========================================
    // MESSAGES D'ERREUR DÉTAILLÉS
    // ==========================================

    private static $detailed_error_messages = [
        'PDF_BUILDER_GENERAL_ERROR' => [
            'title' => 'Erreur générale',
            'description' => 'Une erreur inattendue s\'est produite lors de l\'exécution de l\'opération.',
            'suggestions' => [
                'Vérifiez votre connexion internet',
                'Actualisez la page et réessayez',
                'Contactez le support si le problème persiste'
            ]
        ],

        'PDF_BUILDER_VALIDATION_ERROR' => [
            'title' => 'Erreur de validation',
            'description' => 'Les données saisies ne respectent pas les règles de validation.',
            'suggestions' => [
                'Vérifiez que tous les champs requis sont remplis',
                'Assurez-vous que les formats sont corrects',
                'Consultez l\'aide pour les formats acceptés'
            ]
        ],

        'PDF_BUILDER_PERMISSION_ERROR' => [
            'title' => 'Erreur de permissions',
            'description' => 'Vous n\'avez pas les droits nécessaires pour effectuer cette action.',
            'suggestions' => [
                'Contactez un administrateur pour obtenir les permissions',
                'Vérifiez que vous êtes connecté avec le bon compte',
                'Assurez-vous que votre session n\'a pas expiré'
            ]
        ],

        'PDF_BUILDER_MEMORY_ERROR' => [
            'title' => 'Mémoire insuffisante',
            'description' => 'La mémoire disponible n\'est pas suffisante pour traiter cette opération.',
            'suggestions' => [
                'Réduisez la taille ou la complexité du document',
                'Fermez les autres onglets/applications',
                'Augmentez la limite mémoire si possible'
            ]
        ],

        'PDF_BUILDER_CANVAS_INIT_ERROR' => [
            'title' => 'Erreur d\'initialisation du canvas',
            'description' => 'Le canvas n\'a pas pu être initialisé correctement.',
            'suggestions' => [
                'Vérifiez que JavaScript est activé',
                'Essayez avec un navigateur plus récent',
                'Désactivez temporairement les extensions du navigateur'
            ]
        ],

        'PDF_BUILDER_UPLOAD_SIZE_ERROR' => [
            'title' => 'Fichier trop volumineux',
            'description' => 'La taille du fichier dépasse la limite autorisée.',
            'suggestions' => [
                'Réduisez la taille du fichier',
                'Compressez l\'image si possible',
                'Utilisez un format plus léger'
            ]
        ]
    ];

    // ==========================================
    // MESSAGES DE SUCCÈS
    // ==========================================

    private static $success_messages = [
        'TEMPLATE_SAVED' => 'Le template a été sauvegardé avec succès.',
        'TEMPLATE_LOADED' => 'Le template a été chargé avec succès.',
        'TEMPLATE_DELETED' => 'Le template a été supprimé avec succès.',
        'ELEMENT_ADDED' => 'L\'élément a été ajouté avec succès.',
        'ELEMENT_UPDATED' => 'L\'élément a été mis à jour avec succès.',
        'ELEMENT_DELETED' => 'L\'élément a été supprimé avec succès.',
        'CANVAS_EXPORTED' => 'Le canvas a été exporté avec succès.',
        'SETTINGS_SAVED' => 'Les paramètres ont été sauvegardés avec succès.',
        'FILE_UPLOADED' => 'Le fichier a été téléchargé avec succès.',
        'CONFIG_UPDATED' => 'La configuration a été mise à jour avec succès.'
    ];

    // ==========================================
    // MESSAGES D'AVERTISSEMENT
    // ==========================================

    private static $warning_messages = [
        'UNSAVED_CHANGES' => 'Vous avez des modifications non sauvegardées.',
        'LARGE_FILE' => 'Le fichier est volumineux et peut ralentir les performances.',
        'MEMORY_LOW' => 'Mémoire disponible faible. Les performances peuvent être affectées.',
        'BROWSER_COMPATIBILITY' => 'Votre navigateur peut ne pas supporter toutes les fonctionnalités.',
        'AUTO_SAVE_DISABLED' => 'La sauvegarde automatique est désactivée.',
        'EXPORT_QUALITY_LOW' => 'La qualité d\'export est faible. L\'image peut paraître pixelisée.'
    ];

    // ==========================================
    // MÉTHODES D'ACCÈS
    // ==========================================

    /**
     * Obtenir tous les codes d'erreur
     */
    public static function get_error_codes() {
        return self::$error_codes;
    }

    /**
     * Obtenir un code d'erreur spécifique
     */
    public static function get_error_code($key) {
        return self::$error_codes[$key] ?? null;
    }

    /**
     * Obtenir tous les messages d'erreur
     */
    public static function get_error_messages() {
        return self::$error_messages;
    }

    /**
     * Obtenir un message d'erreur spécifique
     */
    public static function get_error_message($code) {
        return self::$error_messages[$code] ?? 'Une erreur inconnue s\'est produite.';
    }

    /**
     * Obtenir les détails d'une erreur
     */
    public static function get_detailed_error($code) {
        return self::$detailed_error_messages[$code] ?? [
            'title' => 'Erreur',
            'description' => self::get_error_message($code),
            'suggestions' => ['Contactez le support technique']
        ];
    }

    /**
     * Obtenir tous les messages de succès
     */
    public static function get_success_messages() {
        return self::$success_messages;
    }

    /**
     * Obtenir un message de succès spécifique
     */
    public static function get_success_message($key) {
        return self::$success_messages[$key] ?? 'Opération réussie.';
    }

    /**
     * Obtenir tous les messages d'avertissement
     */
    public static function get_warning_messages() {
        return self::$warning_messages;
    }

    /**
     * Obtenir un message d'avertissement spécifique
     */
    public static function get_warning_message($key) {
        return self::$warning_messages[$key] ?? 'Avertissement.';
    }

    /**
     * Créer une réponse d'erreur standardisée
     */
    public static function create_error_response($code, $additional_data = []) {
        $details = self::get_detailed_error($code);

        return array_merge([
            'success' => false,
            'error' => true,
            'code' => $code,
            'message' => $details['description'],
            'title' => $details['title'],
            'suggestions' => $details['suggestions']
        ], $additional_data);
    }

    /**
     * Créer une réponse de succès standardisée
     */
    public static function create_success_response($message_key, $additional_data = []) {
        return array_merge([
            'success' => true,
            'error' => false,
            'message' => self::get_success_message($message_key)
        ], $additional_data);
    }

    /**
     * Créer une réponse d'avertissement standardisée
     */
    public static function create_warning_response($message_key, $additional_data = []) {
        return array_merge([
            'success' => true,
            'warning' => true,
            'message' => self::get_warning_message($message_key)
        ], $additional_data);
    }
}



