<?php
/**
 * Script de correction du Security Validator sur le serveur
 */

// Chemin du fichier à corriger
$target_file = '/var/www/nats/data/www/threeaxe.fr/wp-content/plugins/wp-pdf-builder-pro/src/Core/PDF_Builder_Security_Validator.php';

// Contenu corrigé du fichier
$content = '<?php

/**
 * PDF Builder Pro - Validateur de sécurité avancé
 * Centralise toutes les vérifications de sécurité et validation des données
 */

namespace PDF_Builder\Core;

// Empêcher l\'accès direct
if (!defined(\'ABSPATH\')) {
    exit; // Exit if accessed directly
}

class PDF_Builder_Security_Validator
{
    private static $instance = null;

    // Seuils de sécurité
    const MAX_REQUESTS_PER_MINUTE = 60;
    const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    const MAX_TEMPLATE_SIZE = 5 * 1024 * 1024; // 5MB
    const ALLOWED_MIME_TYPES = [\'application/json\', \'image/jpeg\', \'image/png\', \'image/gif\'];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Ne pas initialiser automatiquement les hooks ici
        // L\'initialisation sera faite via la méthode init()
    }

    /**
     * Initialise les hooks WordPress (à appeler après le chargement de WordPress)
     */
    public function init() {
        $this->init_hooks();
    }

    private function init_hooks() {
        // Rate limiting
        add_action(\'wp_ajax_pdf_builder_rate_limit_check\', [$this, \'check_rate_limit\']);

        // Validation des uploads
        add_filter(\'wp_handle_upload_prefilter\', [$this, \'validate_upload\']);

        // Sanitisation des données
        add_filter(\'pdf_builder_sanitize_template_data\', [$this, \'sanitize_template_data\']);
        add_filter(\'pdf_builder_sanitize_settings\', [$this, \'sanitize_settings\']);
    }

    /**
     * Valide une requête AJAX complète
     */
    public function validate_ajax_request($required_capability = \'manage_options\') {
        // Vérifier le referrer
        if (!$this->validate_referrer()) {
            $this->log_security_violation(\'Invalid referrer\', $_SERVER);
            wp_die(\'Sécurité: Referrer invalide\', 403);
        }

        // Vérifier le nonce
        if (!isset($_POST[\'nonce\']) || !wp_verify_nonce($_POST[\'nonce\'], \'pdf_builder_ajax\')) {
            $this->log_security_violation(\'Invalid nonce\', $_POST);
            wp_die(\'Sécurité: Nonce invalide\', 403);
        }

        // Vérifier les permissions
        if (!current_user_can($required_capability)) {
            $this->log_security_violation(\'Insufficient permissions\', [
                \'user_id\' => get_current_user_id(),
                \'required_cap\' => $required_capability
            ]);
            wp_die(\'Sécurité: Permissions insuffisantes\', 403);
        }

        // Vérifier le rate limiting
        if (!$this->check_rate_limit_for_user()) {
            $this->log_security_violation(\'Rate limit exceeded\', [
                \'user_id\' => get_current_user_id(),
                \'ip\' => $this->get_client_ip()
            ]);
            wp_die(\'Sécurité: Trop de requêtes\', 429);
        }

        return true;
    }

    /**
     * Valide le referrer HTTP
     */
    private function validate_referrer() {
        if (!isset($_SERVER[\'HTTP_REFERER\'])) {
            return false;
        }

        $referrer = $_SERVER[\'HTTP_REFERER\'];
        $site_url = get_site_url();

        // Vérifier que le referrer vient du même domaine
        if (strpos($referrer, $site_url) !== 0) {
            return false;
        }

        return true;
    }

    /**
     * Vérifie le rate limiting pour l\'utilisateur
     */
    private function check_rate_limit_for_user() {
        $user_id = get_current_user_id();
        $ip = $this->get_client_ip();
        $cache_key = \'pdf_builder_rate_limit_\' . md5($user_id . \'_\' . $ip);

        $requests = get_transient($cache_key);
        if ($requests === false) {
            $requests = 0;
        }

        if ($requests >= self::MAX_REQUESTS_PER_MINUTE) {
            return false;
        }

        set_transient($cache_key, $requests + 1, 60); // 1 minute
        return true;
    }

    /**
     * Valide un upload de fichier
     */
    public function validate_upload($file) {
        // Vérifier la taille du fichier
        if ($file[\'size\'] > self::MAX_FILE_SIZE) {
            $file[\'error\'] = \'Fichier trop volumineux (max 10MB)\';
            return $file;
        }

        // Vérifier le type MIME
        $file_type = wp_check_filetype($file[\'name\']);
        if (!in_array($file_type[\'type\'], self::ALLOWED_MIME_TYPES)) {
            $file[\'error\'] = \'Type de fichier non autorisé\';
            return $file;
        }

        // Vérifier le contenu du fichier (pour les images)
        if (strpos($file_type[\'type\'], \'image/\') === 0) {
            if (!$this->validate_image_content($file[\'tmp_name\'])) {
                $file[\'error\'] = \'Contenu du fichier invalide\';
                return $file;
            }
        }

        return $file;
    }

    /**
     * Valide le contenu d\'une image
     */
    private function validate_image_content($file_path) {
        $image_info = getimagesize($file_path);
        return $image_info !== false;
    }

    /**
     * Sanitise les données d\'un template
     */
    public function sanitize_template_data($data) {
        if (!is_array($data)) {
            return [];
        }

        $sanitized = [];

        // Valider et sanitiser les éléments
        if (isset($data[\'elements\']) && is_array($data[\'elements\'])) {
            $sanitized[\'elements\'] = array_map([$this, \'sanitize_element\'], $data[\'elements\']);
        }

        // Valider et sanitiser le canvas
        if (isset($data[\'canvas\']) && is_array($data[\'canvas\'])) {
            $sanitized[\'canvas\'] = $this->sanitize_canvas($data[\'canvas\']);
        }

        // Valider la taille totale
        $json_size = strlen(wp_json_encode($sanitized));
        if ($json_size > self::MAX_TEMPLATE_SIZE) {
            throw new Exception(\'Template trop volumineux\');
        }

        return $sanitized;
    }

    /**
     * Sanitise un élément du template
     */
    private function sanitize_element($element) {
        if (!is_array($element)) {
            return [];
        }

        $sanitized = [];

        // Propriétés de base
        $allowed_props = [
            \'id\', \'type\', \'content\', \'x\', \'y\', \'width\', \'height\',
            \'fontSize\', \'fontWeight\', \'color\', \'textAlign\', \'visible\',
            \'locked\', \'zIndex\', \'rotation\', \'opacity\'
        ];

        foreach ($allowed_props as $prop) {
            if (isset($element[$prop])) {
                $sanitized[$prop] = $this->sanitize_value($element[$prop], $prop);
            }
        }

        return $sanitized;
    }

    /**
     * Sanitise les paramètres du canvas
     */
    private function sanitize_canvas($canvas) {
        $sanitized = [];

        $allowed_props = [\'width\', \'height\', \'backgroundColor\', \'dpi\'];

        foreach ($allowed_props as $prop) {
            if (isset($canvas[$prop])) {
                $sanitized[$prop] = $this->sanitize_value($canvas[$prop], $prop);
            }
        }

        return $sanitized;
    }

    /**
     * Sanitise une valeur selon son type
     */
    private function sanitize_value($value, $property) {
        switch ($property) {
            case \'id\':
            case \'content\':
            case \'color\':
            case \'backgroundColor\':
            case \'textAlign\':
                return sanitize_text_field($value);

            case \'x\':
            case \'y\':
            case \'width\':
            case \'height\':
            case \'fontSize\':
            case \'rotation\':
            case \'opacity\':
            case \'zIndex\':
                return floatval($value);

            case \'fontWeight\':
                return in_array($value, [\'normal\', \'bold\', \'lighter\', \'bolder\']) ? $value : \'normal\';

            case \'visible\':
            case \'locked\':
                return (bool) $value;

            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Sanitise les paramètres du plugin
     */
    public function sanitize_settings($settings) {
        if (!is_array($settings)) {
            return [];
        }

        $sanitized = [];

        foreach ($settings as $key => $value) {
            $sanitized[$key] = $this->sanitize_setting_value($key, $value);
        }

        return $sanitized;
    }

    /**
     * Sanitise une valeur de paramètre selon sa clé
     */
    private function sanitize_setting_value($key, $value) {
        // Paramètres booléens
        if (in_array($key, [\'cache_enabled\', \'auto_backup\', \'debug_enabled\'])) {
            return $value ? \'1\' : \'0\';
        }

        // Paramètres numériques
        if (in_array($key, [\'cache_ttl\', \'backup_retention\', \'log_retention\'])) {
            return intval($value);
        }

        // Paramètres texte
        if (in_array($key, [\'company_name\', \'company_address\', \'admin_email\'])) {
            return sanitize_text_field($value);
        }

        // Paramètres email
        if ($key === \'notification_email\') {
            return sanitize_email($value);
        }

        // Par défaut, sanitiser comme texte
        return sanitize_text_field($value);
    }

    /**
     * Valide une adresse email
     */
    public function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valide un numéro de téléphone
     */
    public function validate_phone($phone) {
        // Regex simple pour les numéros de téléphone français
        return preg_match(\'/^(\+33|0)[1-9](\d{2}){4}$/\', $phone);
    }

    /**
     * Obtient l\'IP du client de manière sécurisée
     */
    private function get_client_ip() {
        $ip_headers = [
            \'HTTP_CF_CONNECTING_IP\',
            \'HTTP_CLIENT_IP\',
            \'HTTP_X_FORWARDED_FOR\',
            \'HTTP_X_FORWARDED\',
            \'HTTP_X_CLUSTER_CLIENT_IP\',
            \'HTTP_FORWARDED_FOR\',
            \'HTTP_FORWARDED\',
            \'REMOTE_ADDR\'
        ];

        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];

                // Gérer X-Forwarded-For avec plusieurs IPs
                if (strpos($ip, \',\') !== false) {
                    $ip = trim(explode(\',\', $ip)[0]);
                }

                // Valider l\'IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return \'127.0.0.1\'; // Fallback
    }

    /**
     * Log une violation de sécurité
     */
    private function log_security_violation($message, $context = []) {
        if (class_exists(\'PDF_Builder_Logger\')) {
            PDF_Builder_Logger::get_instance()->warning(\'Security violation: \' . $message, array_merge($context, [
                \'ip\' => $this->get_client_ip(),
                \'user_agent\' => $_SERVER[\'HTTP_USER_AGENT\'] ?? \'unknown\',
                \'timestamp\' => current_time(\'timestamp\')
            ]));
        } else {
            error_log(\'[PDF Builder Security] \' . $message . \' | Context: \' . json_encode($context));
        }
    }

    /**
     * Génère un token CSRF unique pour les formulaires
     */
    public function generate_csrf_token() {
        $token = wp_generate_password(32, false);
        $token_hash = wp_hash($token);

        // Stocker le hash en session/transient
        $user_id = get_current_user_id();
        set_transient(\'pdf_builder_csrf_\' . $user_id, $token_hash, HOUR_IN_SECONDS);

        return $token;
    }

    /**
     * Valide un token CSRF
     */
    public function validate_csrf_token($token) {
        $user_id = get_current_user_id();
        $stored_hash = get_transient(\'pdf_builder_csrf_\' . $user_id);

        if (!$stored_hash) {
            return false;
        }

        $token_hash = wp_hash($token);
        return hash_equals($stored_hash, $token_hash);
    }

    /**
     * Nettoie les anciens tokens CSRF
     */
    public function cleanup_csrf_tokens() {
        global $wpdb;

        // Supprimer les transients expirés
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE \'_transient_pdf_builder_csrf_%\' AND option_value < UNIX_TIMESTAMP() - 3600");
    }

    /**
     * Vérifie si l\'utilisateur a accès à une ressource
     */
    public function user_can_access_resource($resource_id, $resource_type = \'template\') {
        $user_id = get_current_user_id();

        // Admin peut tout accéder
        if (user_can($user_id, \'manage_options\')) {
            return true;
        }

        switch ($resource_type) {
            case \'template\':
                return $this->user_can_access_template($user_id, $resource_id);
            case \'backup\':
                return $this->user_can_access_backup($user_id, $resource_id);
            default:
                return false;
        }
    }

    /**
     * Vérifie l\'accès à un template
     */
    private function user_can_access_template($user_id, $template_id) {
        global $wpdb;
        $table = $wpdb->prefix . \'pdf_builder_templates\';

        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT user_id, is_public FROM {$table} WHERE id = %d", $template_id)
        );

        if (!$template) {
            return false;
        }

        // Template public ou appartient à l\'utilisateur
        return $template->is_public || $template->user_id == $user_id;
    }

    /**
     * Vérifie l\'accès à une sauvegarde
     */
    private function user_can_access_backup($user_id, $backup_id) {
        // Pour l\'instant, seuls les admins peuvent accéder aux sauvegardes
        return user_can($user_id, \'manage_options\');
    }

    // Méthodes existantes pour compatibilité
    public static function sanitizeHtmlContent($content)
    {
        if (empty($content)) {
            return \'\';
        }

        // Vérifier si WordPress est chargé
        if (!function_exists(\'wp_kses\')) {
            return $content; // Retourner le contenu non filtré si WordPress n\'est pas disponible
        }

        // Liste des tags HTML autorisés pour les PDFs
        $allowed_tags = [
            \'p\' => [
                \'style\' => [],
                \'class\' => [],
                \'id\' => []
            ],
            \'br\' => [],
            \'strong\' => [
                \'style\' => [],
                \'class\' => []
            ],
            \'em\' => [
                \'style\' => [],
                \'class\' => []
            ],
            \'u\' => [
                \'style\' => [],
                \'class\' => []
            ],
            \'h1\' => [
                \'style\' => [],
                \'class\' => [],
                \'id\' => []
            ],
            \'h2\' => [
                \'style\' => [],
                \'class\' => [],
                \'id\' => []
            ],
            \'h3\' => [
                \'style\' => [],
                \'class\' => [],
                \'id\' => []
            ],
            \'h4\' => [
                \'style\' => [],
                \'class\' => [],
                \'id\' => []
            ],
            \'h5\' => [
                \'style\' => [],
                \'class\' => [],
                \'id\' => []
            ],
            \'h6\' => [
                \'style\' => [],
                \'class\' => [],
                \'id\' => []
            ],
            \'table\' => [
                \'style\' => [],
                \'class\' => [],
                \'border\' => [],
                \'cellpadding\' => [],
                \'cellspacing\' => []
            ],
            \'tr\' => [
                \'style\' => [],
                \'class\' => []
            ],
            \'td\' => [
                \'style\' => [],
                \'class\' => [],
                \'colspan\' => [],
                \'rowspan\' => []
            ],
            \'th\' => [
                \'style\' => [],
                \'class\' => [],
                \'colspan\' => [],
                \'rowspan\' => []
            ],
            \'thead\' => [
                \'style\' => [],
                \'class\' => []
            ],
            \'tbody\' => [
                \'style\' => [],
                \'class\' => []
            ],
            \'img\' => [
                \'src\' => [],
                \'alt\' => [],
                \'style\' => [],
                \'class\' => [],
                \'width\' => [],
                \'height\' => []
            ],
            \'div\' => [
                \'style\' => [],
                \'class\' => [],
                \'id\' => []
            ],
            \'span\' => [
                \'style\' => [],
                \'class\' => [],
                \'id\' => []
            ],
            \'ul\' => [
                \'style\' => [],
                \'class\' => []
            ],
            \'ol\' => [
                \'style\' => [],
                \'class\' => []
            ],
            \'li\' => [
                \'style\' => [],
                \'class\' => []
            ]
        ];

        // Utilisation de wp_kses pour sanitisation
        $sanitized = wp_kses($content, $allowed_tags);

        // Log des modifications pour audit (seulement si WordPress est complètement chargé)
        if ($sanitized !== $content && function_exists(\'get_current_user_id\')) {
            self::logSecurityEvent(
                \'html_sanitized\',
                [
                \'original_length\' => strlen($content),
                \'sanitized_length\' => strlen($sanitized),
                \'user_id\' => get_current_user_id(),
                \'ip\' => self::getClientIp()
                ]
            );
        }

        return $sanitized;
    }

    public static function validateJsonData($json_data)
    {
        if (empty($json_data)) {
            return false;
        }

        // Décodage JSON sécurisé
        $data = json_decode($json_data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Log seulement si WordPress est disponible
            if (function_exists(\'get_current_user_id\')) {
                self::logSecurityEvent(
                    \'invalid_json\',
                    [
                    \'error\' => json_last_error_msg(),
                    \'user_id\' => get_current_user_id(),
                    \'ip\' => self::getClientIp()
                    ]
                );
            }
            return false;
        }

        // Validation récursive des données
        return self::sanitizeArrayData($data);
    }

    private static function sanitizeArrayData($data)
    {
        if (!is_array($data)) {
            // Utiliser les fonctions WordPress si disponibles, sinon faire une sanitisation basique
            if (function_exists(\'sanitize_text_field\')) {
                return is_string($data) ? sanitize_text_field($data) : $data;
            } else {
                return is_string($data) ? htmlspecialchars($data, ENT_QUOTES, \'UTF-8\') : $data;
            }
        }

        $sanitized = [];
        foreach ($data as $key => $value) {
            // Utiliser sanitize_key si disponible, sinon nettoyer manuellement
            if (function_exists(\'sanitize_key\')) {
                $clean_key = sanitize_key($key);
            } else {
                $clean_key = preg_replace(\'/[^a-zA-Z0-9_\-]/\', \'\', $key);
            }
            $sanitized[$clean_key] = self::sanitizeArrayData($value);
        }

        return $sanitized;
    }

    public static function validateNonce($nonce, $action)
    {
        // Vérifier si WordPress est chargé
        if (!function_exists(\'wp_verify_nonce\')) {
            return false; // Retourner false si WordPress n\'est pas disponible
        }

        $valid = wp_verify_nonce($nonce, $action);

        if (!$valid) {
            self::logSecurityEvent(
                \'invalid_nonce\',
                [
                \'action\' => $action,
                \'user_id\' => get_current_user_id(),
                \'ip\' => self::getClientIp()
                ]
            );
        }

        return $valid;
    }

    private static function getClientIp()
    {
        $ip_headers = [
            \'HTTP_CF_CONNECTING_IP\',
            \'HTTP_CLIENT_IP\',
            \'HTTP_X_FORWARDED_FOR\',
            \'HTTP_X_FORWARDED\',
            \'HTTP_X_CLUSTER_CLIENT_IP\',
            \'HTTP_FORWARDED_FOR\',
            \'HTTP_FORWARDED\',
            \'REMOTE_ADDR\'
        ];

        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Gestion des IPs multiples (dernière IP dans la chaîne)
                if (strpos($ip, \',\') !== false) {
                    $ip_parts = explode(\',\', $ip);
                    $ip = trim(end($ip_parts));
                }
                // Validation de l\'IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return \'unknown\';
    }

    private static function logSecurityEvent($event, $data = [])
    {
        // Utiliser current_time si WordPress est disponible, sinon utiliser date()
        if (function_exists(\'current_time\')) {
            $timestamp = current_time(\'mysql\');
        } else {
            $timestamp = date(\'Y-m-d H:i:s\');
        }

        $log_data = array_merge(
            [
            \'timestamp\' => $timestamp,
            \'event\' => $event,
            \'user_agent\' => isset($_SERVER[\'HTTP_USER_AGENT\']) ? $_SERVER[\'HTTP_USER_AGENT\'] : \'unknown\'
            ],
            $data
        );

        // Log dans le fichier de debug WordPress si activé
        if (defined(\'WP_DEBUG\') && WP_DEBUG) {
        }

        // Stockage en base pour audit (optionnel)
        // self::store_security_log($log_data);
    }

    public static function checkPermissions($capability = \'manage_options\')
    {
        // Vérifier si WordPress est chargé
        if (!function_exists(\'current_user_can\')) {
            return false; // Retourner false si WordPress n\'est pas disponible
        }

        if (!current_user_can($capability)) {
            self::logSecurityEvent(
                \'insufficient_permissions\',
                [
                \'required_capability\' => $capability,
                \'user_id\' => get_current_user_id(),
                \'ip\' => self::getClientIp()
                ]
            );
            return false;
        }
        return true;
    }
}

// Fonctions globales pour faciliter l\'utilisation
function pdf_builder_validate_ajax_request($capability = \'manage_options\') {
    return PDF_Builder_Security_Validator::get_instance()->validate_ajax_request($capability);
}

function pdf_builder_sanitize_template_data($data) {
    return PDF_Builder_Security_Validator::get_instance()->sanitize_template_data($data);
}

function pdf_builder_sanitize_settings($settings) {
    return PDF_Builder_Security_Validator::get_instance()->sanitize_settings($settings);
}';

echo "<h1>Correction du Security Validator</h1>\n";

echo "<h2>Écriture du fichier corrigé</h2>\n";

// Créer le répertoire si nécessaire
$dir = dirname($target_file);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
    echo "<p>✅ Répertoire créé: $dir</p>\n";
}

$result = file_put_contents($target_file, $content);
if ($result !== false) {
    echo "<p>✅ Fichier écrit avec succès (" . strlen($content) . " caractères)</p>\n";

    echo "<h2>Test d'inclusion</h2>\n";
    try {
        require_once $target_file;
        echo "<p>✅ Inclusion réussie</p>\n";

        if (class_exists('PDF_Builder_Security_Validator')) {
            echo "<p>✅ Classe PDF_Builder_Security_Validator trouvée</p>\n";

            // Test d'instanciation
            $instance = PDF_Builder_Security_Validator::get_instance();
            echo "<p>✅ Instance créée avec succès</p>\n";

            echo "<h1>🎉 CORRECTION RÉUSSIE !</h1>\n";
            echo "<p>Le Security Validator est maintenant fonctionnel.</p>\n";

        } else {
            echo "<p>❌ Classe non trouvée après inclusion</p>\n";
        }

    } catch (Exception $e) {
        echo "<p>❌ Exception lors de l'inclusion: " . $e->getMessage() . "</p>\n";
    } catch (Error $e) {
        echo "<p>❌ Erreur fatale lors de l'inclusion: " . $e->getMessage() . "</p>\n";
        echo "<p>Ligne: " . $e->getLine() . "</p>\n";
    }

} else {
    echo "<p>❌ Échec de l'écriture du fichier</p>\n";
}

echo "<h2>Informations système</h2>\n";
echo "<p>PHP Version: " . phpversion() . "</p>\n";
echo "<p>Utilisateur: " . get_current_user() . "</p>\n";
?>