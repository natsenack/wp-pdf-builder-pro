<?php

namespace WP_PDF_Builder_Pro\Managers;

/**
 * Gestionnaire d'Exécutions Shell Sécurisées - PDF Builder Pro
 *
 * Sécurise les appels shell_exec() avec validation et chemins absolus
 */

class PdfBuilderSecureShellManager
{
    /**
     * Commandes autorisées avec leurs chemins validés
     */
    private static $allowed_commands = [
        'wkhtmltopdf' => [
            'command' => 'wkhtmltopdf',
            'validate_path' => true,
            'allowed_args' => ['--page-size', '--margin-top', '--margin-bottom', '--margin-left', '--margin-right', '--disable-smart-shrinking', '--enable-local-file-access']
        ],
        'node' => [
            'command' => 'node',
            'validate_path' => true,
            'allowed_args' => [] // Node peut exécuter n'importe quel script JS
        ],
        'which' => [
            'command' => 'which',
            'validate_path' => false,
            'allowed_args' => [] // which est sûr
        ]
    ];
/**
     * Cache des chemins validés
     */
    private static $path_cache = [];

    /**
     * Logger pour les exécutions
     */
    private static function logExecution($command, $output, $success = true, $security_level = 'info')
    {
        $log_message = sprintf("[SECURE_SHELL] %s - Command: %s - Output: %s - Level: %s", $success ? 'SUCCESS' : 'FAILED', $command, is_string($output) ? substr($output, 0, 200) : 'N/A', $security_level);
// Log aussi dans le logger du plugin si disponible
        if (class_exists('PDF_Builder_Logger')) {
            $method = $success ? 'log' : 'error';
            PDF_Builder_Logger::$method($log_message);
        }

        // Log de sécurité pour les commandes à haut risque
        if ($security_level === 'high') {
            self::logSecurityEvent($command, $output, $success);
        }
    }

    /**
     * Log des événements de sécurité
     */
    private static function logSecurityEvent($command, $output, $success)
    {
        $security_log = sprintf("[SECURITY] Shell command executed - Time: %s - Command: %s - Success: %s - IP: %s - User: %s", date('Y-m-d H:i:s'), $command, $success ? 'YES' : 'NO', $_SERVER['REMOTE_ADDR'] ?? 'CLI', get_current_user_id());
// Log dans un fichier séparé pour la sécurité
        $security_log_file = WP_CONTENT_DIR . '/pdf-builder-security.log';
        $log_entry = date('Y-m-d H:i:s') . ' - ' . $security_log . "\n";
// Limiter la taille du fichier de log (max 1MB)
        if (file_exists($security_log_file) && filesize($security_log_file) > 1024 * 1024) {
            $content = file_get_contents($security_log_file);
            $lines = explode("\n", $content);
// Garder seulement les 1000 dernières lignes
            $lines = array_slice($lines, -1000);
            file_put_contents($security_log_file, implode("\n", $lines));
        }

        file_put_contents($security_log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Exécute une commande shell de manière sécurisée
     */
    public static function executeSecureCommand($command_name, $args = [])
    {
        // Vérifier si la commande est autorisée
        if (!isset(self::$allowed_commands[$command_name])) {
            self::logExecution($command_name, 'Command not allowed', false);
            return false;
        }

        $command_config = self::$allowed_commands[$command_name];
// Valider les arguments si nécessaire
        if (!empty($command_config['allowed_args']) && !empty($args)) {
            foreach ($args as $arg) {
                if (!in_array($arg, $command_config['allowed_args'])) {
                    self::logExecution($command_name, "Invalid argument: $arg", false);
                    return false;
                }
            }
        }

        // Obtenir le chemin absolu si nécessaire
        if ($command_config['validate_path']) {
            $full_path = self::getSecurePath($command_name);
            if (!$full_path) {
                self::logExecution($command_name, 'Path not found or insecure', false);
                return false;
            }
            $command = $full_path;
        } else {
            $command = $command_config['command'];
        }

        // Ajouter les arguments
        if (!empty($args)) {
            $command .= ' ' . implode(' ', array_map('escapeshellarg', $args));
        }

        // Exécuter avec timeout et limites
        try {
            $old_timeout = ini_get('max_execution_time');
            set_time_limit(30);
// 30 secondes max pour les commandes shell

            $output = shell_exec($command . ' 2>&1');
            set_time_limit($old_timeout);
            $success = $output !== null;
            self::logExecution($command, $output, $success);
            return $success ? $output : false;
        } catch (Exception $e) {
            self::logExecution($command, $e->getMessage(), false);
            return false;
        }
    }

    /**
     * Obtient un chemin absolu sécurisé pour une commande
     */
    private static function getSecurePath($command_name)
    {
        if (isset(self::$path_cache[$command_name])) {
            return self::$path_cache[$command_name];
        }

        // Utiliser which pour trouver le chemin
        $which_output = shell_exec('which ' . escapeshellarg($command_name) . ' 2>/dev/null');
        if (empty($which_output)) {
            return false;
        }

        $path = trim($which_output);
// Valider que le chemin est absolu et dans un répertoire système sûr
        if (!self::isSecurePath($path)) {
            self::logExecution($command_name, "Insecure path detected: $path", false, 'high');
            return false;
        }

        // Vérifier que le fichier existe et est exécutable
        if (!is_executable($path)) {
            self::logExecution($command_name, "Path not executable: $path", false, 'high');
            return false;
        }

        self::$path_cache[$command_name] = $path;
        return $path;
    }

    /**
     * Vérifie si un chemin est sécurisé
     */
    private static function isSecurePath($path)
    {
        // Le chemin doit être absolu
        if (strpos($path, '/') !== 0 && strpos($path, '\\') !== 0) {
            return false;
        }

        // Éviter les chemins relatifs ou avec ..
        if (strpos($path, '..') !== false || strpos($path, './') !== false || strpos($path, '.\\') !== false) {
            return false;
        }

        // Liste des répertoires système autorisés (étendue)
        $allowed_dirs = [
            '/usr/bin/',
            '/usr/local/bin/',
            '/usr/sbin/',
            '/usr/local/sbin/',
            '/bin/',
            '/sbin/',
            '/opt/',
            '/home/', // Pour les installations utilisateur
            'C:/Program Files/', // Windows
            'C:/Program Files (x86)/', // Windows
            'C:/Windows/System32/', // Windows
            'C:/Windows/SysWOW64/', // Windows
        ];
// Vérifier si le chemin commence par un répertoire autorisé
        $is_allowed = false;
        foreach ($allowed_dirs as $allowed_dir) {
            if (stripos($path, $allowed_dir) === 0) {
                $is_allowed = true;
                break;
            }
        }

        if (!$is_allowed) {
// Log de sécurité pour les chemins non autorisés
            self::logSecurityEvent("Unauthorized path access: $path", '', false);
        }

        return $is_allowed;
    }

    /**
     * Vérifie si une commande est disponible
     */
    public static function isCommandAvailable($command_name)
    {
        $output = self::executeSecureCommand('which', [$command_name]);
        return !empty($output) && trim($output) !== '';
    }

    /**
     * Exécute wkhtmltopdf de manière sécurisée
     */
    public static function executeWkhtmltopdf($html_file, $pdf_path)
    {
        // Valider les chemins des fichiers
        if (!self::isSecureFilePath($html_file) || !self::isSecureFilePath($pdf_path)) {
            return false;
        }

        $args = [
            '--page-size', 'A4',
            '--margin-top', '15mm',
            '--margin-bottom', '15mm',
            '--margin-left', '15mm',
            '--margin-right', '15mm',
            '--disable-smart-shrinking',
            '--enable-local-file-access',
            $html_file,
            $pdf_path
        ];
        $output = self::executeSecureCommand('wkhtmltopdf', $args);
        return $output !== false && file_exists($pdf_path) && filesize($pdf_path) > 0;
    }

    /**
     * Exécute Node.js de manière sécurisée
     */
    public static function executeNode($script_path, $args = [])
    {
        // Valider que le script existe et est dans un répertoire autorisé
        if (!file_exists($script_path) || !self::isSecureFilePath($script_path)) {
            return false;
        }

        $node_args = array_merge([$script_path], $args);
        return self::executeSecureCommand('node', $node_args);
    }

    /**
     * Vérifie si un chemin de fichier est sécurisé
     */
    private static function isSecureFilePath($path)
    {
        // Le chemin doit être absolu
        if (strpos($path, '/') !== 0 && strpos($path, '\\') !== 0) {
            return false;
        }

        // Éviter les chemins avec ..
        if (strpos($path, '..') !== false) {
            return false;
        }

        // Liste des extensions autorisées pour les fichiers temporaires
        $allowed_extensions = ['.html', '.pdf', '.js'];
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed_extensions)) {
            return false;
        }

        return true;
    }
}
