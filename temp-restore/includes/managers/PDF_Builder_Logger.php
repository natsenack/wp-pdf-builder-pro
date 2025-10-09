<?php
/**
 * Logger Avancé - PDF Builder Pro
 *
 * Système de logging multi-niveaux avec :
 * - Rotation automatique des fichiers
 * - Niveaux de log configurables
 * - Contexte enrichi
 * - Performance optimisée
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Logger
 */
class PDF_Builder_Logger {

    /**
     * Instance singleton
     * @var PDF_Builder_Logger
     */
    private static $instance = null;

    /**
     * Niveaux de log
     */
    const EMERGENCY = 0;
    const ALERT     = 1;
    const CRITICAL  = 2;
    const ERROR     = 3;
    const WARNING   = 4;
    const NOTICE    = 5;
    const INFO      = 6;
    const DEBUG     = 7;

    /**
     * Noms des niveaux
     * @var array
     */
    private static $level_names = [
        self::EMERGENCY => 'EMERGENCY',
        self::ALERT     => 'ALERT',
        self::CRITICAL  => 'CRITICAL',
        self::ERROR     => 'ERROR',
        self::WARNING   => 'WARNING',
        self::NOTICE    => 'NOTICE',
        self::INFO      => 'INFO',
        self::DEBUG     => 'DEBUG'
    ];

    /**
     * Niveau de log minimum
     * @var int
     */
    private $min_level;

    /**
     * Gestionnaire de fichiers
     * @var resource
     */
    private $file_handle;

    /**
     * Chemin du fichier de log
     * @var string
     */
    private $log_file;

    /**
     * Taille maximale du fichier
     * @var int
     */
    private $max_file_size;

    /**
     * Nombre maximum de fichiers
     * @var int
     */
    private $max_files;

    /**
     * Buffer de logs
     * @var array
     */
    private $buffer = [];

    /**
     * Taille du buffer
     * @var int
     */
    private $buffer_size = 10;

    /**
     * Gestionnaire de cache
     * @var PDF_Builder_Cache_Manager
     */
    private $cache_manager;

    /**
     * Constructeur privé
     */
    private function __construct() {
        $this->init_config();
        $this->init_log_file();

        // Initialiser le cache manager seulement si la classe existe
        if (class_exists('PDF_Builder_Cache_Manager')) {
            $this->cache_manager = PDF_Builder_Cache_Manager::getInstance();
        } else {
            $this->cache_manager = null;
        }
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Logger
     */
    public static function getInstance(): PDF_Builder_Logger {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialisation de la configuration
     *
     * @return void
     */
    private function init_config(): void {
        $this->min_level = $this->get_level_from_name(
            function_exists('get_option') ? get_option('pdf_builder_log_level', 'warning') : 'warning'
        );
        $this->max_file_size = function_exists('get_option') ? get_option('pdf_builder_log_max_size', 10 * 1024 * 1024) : 10 * 1024 * 1024;
        $this->max_files = function_exists('get_option') ? get_option('pdf_builder_log_max_files', 30) : 30;
        $this->buffer_size = function_exists('get_option') ? get_option('pdf_builder_log_buffer_size', 10) : 10;
    }

    /**
     * Initialisation du fichier de log
     *
     * @return void
     */
    private function init_log_file(): void {
        if (function_exists('wp_upload_dir')) {
            $upload_dir = wp_upload_dir();
            $log_dir = $upload_dir['basedir'] . '/pdf-builder-logs/';
        } else {
            // Fallback pour les tests hors WordPress
            $log_dir = sys_get_temp_dir() . '/pdf-builder-logs/';
        }

        if (!file_exists($log_dir)) {
            if (function_exists('wp_mkdir_p')) {
                wp_mkdir_p($log_dir);
            } else {
                mkdir($log_dir, 0755, true);
            }
        }

        // Créer le fichier .htaccess pour la sécurité (uniquement si WordPress est chargé)
        if (function_exists('wp_upload_dir')) {
            $htaccess_file = $log_dir . '.htaccess';
            if (!file_exists($htaccess_file)) {
                file_put_contents($htaccess_file, "Deny from all\n");
            }
        }

        $this->log_file = $log_dir . 'pdf-builder-' . date('Y-m-d') . '.log';
        $this->rotate_logs_if_needed();
        $this->open_log_file();
    }

    /**
     * Ouvrir le fichier de log
     *
     * @return void
     */
    private function open_log_file(): void {
        if ($this->file_handle) {
            fclose($this->file_handle);
        }

        $this->file_handle = fopen($this->log_file, 'a');

        if (!$this->file_handle) {
            error_log('PDF Builder Pro: Unable to open log file: ' . $this->log_file);
        }
    }

    /**
     * Rotation des logs si nécessaire
     *
     * @return void
     */
    private function rotate_logs_if_needed(): void {
        if (!file_exists($this->log_file)) {
            return;
        }

        if (filesize($this->log_file) < $this->max_file_size) {
            return;
        }

        // Fermer le fichier actuel
        if ($this->file_handle) {
            fclose($this->file_handle);
        }

        // Rotation des fichiers existants
        $log_dir = dirname($this->log_file);
        $base_name = basename($this->log_file, '.log');

        for ($i = $this->max_files - 1; $i >= 1; $i--) {
            $old_file = $log_dir . '/' . $base_name . '.' . $i . '.log';
            $new_file = $log_dir . '/' . $base_name . '.' . ($i + 1) . '.log';

            if (file_exists($old_file)) {
                rename($old_file, $new_file);
            }
        }

        // Renommer le fichier actuel
        $rotated_file = $log_dir . '/' . $base_name . '.1.log';
        rename($this->log_file, $rotated_file);

        // Créer un nouveau fichier
        $this->open_log_file();
    }

    /**
     * Log d'urgence
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency(string $message, array $context = []): void {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Log d'alerte
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert(string $message, array $context = []): void {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * Log critique
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical(string $message, array $context = []): void {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Log d'erreur
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error(string $message, array $context = []): void {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * Log d'avertissement
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning(string $message, array $context = []): void {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Log de notice
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice(string $message, array $context = []): void {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Log d'information
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info(string $message, array $context = []): void {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Log de debug
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug(string $message, array $context = []): void {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Log principal
     *
     * @param int $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log(int $level, string $message, array $context = []): void {
        if ($level > $this->min_level) {
            return;
        }

        $log_entry = $this->format_log_entry($level, $message, $context);

        // Ajouter au buffer
        $this->buffer[] = $log_entry;

        // Écrire si le buffer est plein ou si c'est un niveau critique
        if (count($this->buffer) >= $this->buffer_size || $level <= self::ERROR) {
            $this->flush_buffer();
        }
    }

    /**
     * Formater une entrée de log
     *
     * @param int $level
     * @param string $message
     * @param array $context
     * @return string
     */
    private function format_log_entry(int $level, string $message, array $context = []): string {
        $timestamp = function_exists('current_time') ? current_time('Y-m-d H:i:s') : date('Y-m-d H:i:s');
        $level_name = self::$level_names[$level] ?? 'UNKNOWN';
        $user_id = function_exists('get_current_user_id') ? get_current_user_id() : 0;
        $user_info = $user_id ? "User:{$user_id}" : 'System';

        $context_str = '';
        if (!empty($context)) {
            $context_str = ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        return sprintf(
            "[%s] %s [%s] %s%s\n",
            $timestamp,
            $level_name,
            $user_info,
            $message,
            $context_str
        );
    }

    /**
     * Vider le buffer
     *
     * @return void
     */
    private function flush_buffer(): void {
        if (empty($this->buffer)) {
            return;
        }

        $content = implode('', $this->buffer);
        $this->buffer = [];

        if ($this->file_handle) {
            fwrite($this->file_handle, $content);
            fflush($this->file_handle);
        } else {
            error_log('PDF Builder Pro: ' . trim($content));
        }
    }

    /**
     * Obtenir le niveau de log depuis le nom
     *
     * @param string $level_name
     * @return int
     */
    private function get_level_from_name(string $level_name): int {
        $level_name = strtoupper($level_name);
        $levels = array_flip(self::$level_names);
        return $levels[$level_name] ?? self::WARNING;
    }

    /**
     * Nettoyer les anciens logs
     *
     * @return void
     */
    public function cleanup_old_logs(): void {
        $log_dir = dirname($this->log_file);
        $files = glob($log_dir . '/pdf-builder-*.log');

        $retention_days = function_exists('get_option') ? get_option('pdf_builder_log_retention_days', 90) : 90;
        $cutoff_time = time() - ($retention_days * 24 * 60 * 60);

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff_time) {
                unlink($file);
            }
        }
    }

    /**
     * Obtenir les statistiques de log
     *
     * @return array
     */
    public function get_stats(): array {
        $log_dir = dirname($this->log_file);
        $files = glob($log_dir . '/pdf-builder-*.log');

        $total_size = 0;
        $file_count = count($files);

        foreach ($files as $file) {
            $total_size += filesize($file);
        }

        return [
            'current_file' => basename($this->log_file),
            'total_files' => $file_count,
            'total_size' => $total_size,
            'total_size_human' => size_format($total_size),
            'min_level' => self::$level_names[$this->min_level] ?? 'UNKNOWN',
            'buffer_size' => count($this->buffer)
        ];
    }

    /**
     * Lire les dernières entrées de log
     *
     * @param int $lines
     * @return array
     */
    public function get_recent_logs(int $lines = 50): array {
        if (!file_exists($this->log_file)) {
            return [];
        }

        $logs = [];
        $file = fopen($this->log_file, 'r');

        if ($file) {
            $buffer = [];
            while (($line = fgets($file)) !== false) {
                $buffer[] = trim($line);
                if (count($buffer) > $lines) {
                    array_shift($buffer);
                }
            }
            fclose($file);
            $logs = $buffer;
        }

        return $logs;
    }

    /**
     * Changer le niveau de log
     *
     * @param string $level
     * @return void
     */
    public function set_level(string $level): void {
        $this->min_level = $this->get_level_from_name($level);
        update_option('pdf_builder_log_level', $level);
    }

    /**
     * Fermer le logger
     *
     * @return void
     */
    public function close(): void {
        $this->flush_buffer();

        if ($this->file_handle) {
            fclose($this->file_handle);
            $this->file_handle = null;
        }
    }

    /**
     * Destructeur
     */
    public function __destruct() {
        $this->close();
    }

    /**
     * Initialisation du logger
     *
     * @return void
     */
    public function init(): void {
        // Nettoyer les anciens logs une fois par jour (seulement si cache manager disponible)
        if ($this->cache_manager) {
            $last_cleanup = $this->cache_manager->get('log_last_cleanup');
            if (!$last_cleanup || (time() - $last_cleanup) > 86400) {
                $this->cleanup_old_logs();
                $this->cache_manager->set('log_last_cleanup', time(), 86400);
            }
        } else {
            // Fallback: nettoyer les logs à chaque initialisation si pas de cache
            $this->cleanup_old_logs();
        }

        $this->info('Logger initialized', $this->get_stats());
    }
}