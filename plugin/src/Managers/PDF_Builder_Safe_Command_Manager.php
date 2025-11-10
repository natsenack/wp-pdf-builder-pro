<?php

namespace WP_PDF_Builder_Pro\Managers;

/**
 * Gestionnaire de Commandes Sécurisées - PDF Builder Pro
 *
 * Remplace eval() par un système de commandes whitelistées sûres
 * pour la page développeur.
 */

class PdfBuilderSafeCommandManager
{
    /**
     * Liste des commandes autorisées
     */
    private static $allowed_commands = [
        'var_dump' => 'var_dump',
        'print_r' => 'print_r',
        'echo' => 'echo',
        'var_export' => 'var_export',
        'gettype' => 'gettype',
        'isset' => 'isset',
        'empty' => 'empty',
        'is_array' => 'is_array',
        'is_object' => 'is_object',
        'is_string' => 'is_string',
        'is_int' => 'is_int',
        'is_bool' => 'is_bool',
        'count' => 'count',
        'strlen' => 'strlen',
        'array_keys' => 'array_keys',
        'array_values' => 'array_values',
        'json_encode' => 'json_encode',
        'json_decode' => 'json_decode',
        'serialize' => 'serialize',
        'unserialize' => 'unserialize',
        'defined' => 'defined',
        'constant' => 'constant',
        'get_defined_constants' => 'get_defined_constants',
        'get_defined_functions' => 'get_defined_functions',
        'get_declared_classes' => 'get_declared_classes',
        'get_included_files' => 'get_included_files',
        'phpversion' => 'phpversion',
        'extension_loaded' => 'extension_loaded',
        'get_loaded_extensions' => 'get_loaded_extensions',
    ];
/**
     * Variables globales autorisées pour l'inspection
     */
    private static $allowed_globals = [
        'wp_version',
        'wp_db_version',
        'wpdb',
        'current_user',
        'post',
        'wp_query',
        'wp_rewrite',
        'wp',
        'wp_roles',
        'wp_locale',
    ];

    /**
     * Exécute une commande sécurisée
     */
    public static function executeSafeCommand($code)
    {
        $code = trim($code);
// Vérifier la longueur
        if (strlen($code) > 1000) {
            return '❌ Commande trop longue (maximum 1000 caractères).';
        }

        // Analyser la commande
        $command_type = self::analyzeCommand($code);
        switch ($command_type) {
            case 'function_call':
                return self::executeFunctionCall($code);
            case 'variable_dump':
                return self::executeVariableDump($code);
            case 'constant_check':
                return self::executeConstantCheck($code);
            case 'info_command':
                return self::executeInfoCommand($code);
            default:
                return '❌ Commande non reconnue ou non autorisée. Utilisez des fonctions comme var_dump(), print_r(), ou des commandes d\'info comme "phpinfo()".';
        }
    }

    /**
     * Analyse le type de commande
     */
    private static function analyzeCommand($code)
    {
        // Vérifier si c'est un appel de fonction autorisé
        if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $code, $matches)) {
            $function_name = $matches[1];
            if (isset(self::$allowed_commands[$function_name])) {
                return 'function_call';
            }
        }

        // Vérifier si c'est un dump de variable globale
        if (preg_match('/^(var_dump|print_r|var_export|echo)\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\)\s*;?\s*$/', $code, $matches)) {
            $function = $matches[1];
            $variable = $matches[2];
            if (in_array($variable, self::$allowed_globals) || strpos($variable, 'wp_') === 0) {
                return 'variable_dump';
            }
        }

        // Vérifier si c'est une vérification de constante
        if (preg_match('/^(defined|constant)\s*\(/', $code)) {
            return 'constant_check';
        }

        // Commandes d'information spéciales
        if (in_array($code, ['phpinfo()', 'phpversion()', 'get_loaded_extensions()'])) {
            return 'info_command';
        }

        return 'unknown';
    }

    /**
     * Exécute un appel de fonction autorisé
     */
    private static function executeFunctionCall($code)
    {
        try {
// Limiter le temps d'exécution
            $old_time_limit = ini_get('max_execution_time');
            set_time_limit(5);
// 5 secondes max

            ob_start();
            $result = eval("return $code;");
            $output = ob_get_clean();
// Restaurer la limite de temps
            set_time_limit($old_time_limit);
            return $output . ($result !== null ? "\n" . var_export($result, true) : '');
        } catch (Throwable $e) {
            return 'Erreur : ' . $e->getMessage();
        }
    }

    /**
     * Exécute un dump de variable globale
     */
    private static function executeVariableDump($code)
    {
        try {
            ob_start();
            eval($code);
            return ob_get_clean();
        } catch (Throwable $e) {
            return 'Erreur : ' . $e->getMessage();
        }
    }

    /**
     * Exécute une vérification de constante
     */
    private static function executeConstantCheck($code)
    {
        try {
            ob_start();
            $result = eval("return $code;");
            $output = ob_get_clean();
            return $output . "\nRésultat : " . var_export($result, true);
        } catch (Throwable $e) {
            return 'Erreur : ' . $e->getMessage();
        }
    }

    /**
     * Exécute une commande d'information
     */
    private static function executeInfoCommand($code)
    {
        try {
            switch ($code) {
                case 'phpinfo()':
                                    ob_start();
                    phpinfo();

                    return ob_get_clean();
                case 'phpversion()':
                    return 'PHP Version : ' . phpversion();
                case 'get_loaded_extensions()':
                    return 'Extensions chargées : ' . implode(', ', get_loaded_extensions());
                default:
                    return 'Commande d\'info non reconnue.';
            }
        } catch (Throwable $e) {
            return 'Erreur : ' . $e->getMessage();
        }
    }

    /**
     * Vérifie si une commande est sûre (pour validation côté client)
     */
    public static function isCommandSafe($code)
    {
        $command_type = self::analyzeCommand($code);
        return $command_type !== 'unknown';
    }

    /**
     * Liste des commandes disponibles
     */
    public static function getAvailableCommands()
    {
        return array_merge(array_keys(self::$allowed_commands), ['phpinfo', 'phpversion', 'get_loaded_extensions'], array_map(function ($var) {
            return "var_dump(\$$var)";
        }, self::$allowed_globals));
    }
}
