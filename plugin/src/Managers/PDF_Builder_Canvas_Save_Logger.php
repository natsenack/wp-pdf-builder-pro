<?php
/**
 * PDF Builder Pro - Canvas Save Logger
 * 
 * Système de logging dédié pour la sauvegarde des données canvas
 * Trace les éléments, les propriétés, et les résultats de sauvegarde
 *
 * @package PDF_Builder_Pro
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_Canvas_Save_Logger {

    /**
     * Instance unique du logger
     */
    private static $instance = null;

    /**
     * Fichier de log dédié
     */
    private $log_file;

    /**
     * Niveaux de log
     */
    private $log_levels = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
    ];

    /**
     * Niveau minimum de log à enregistrer
     */
    private $min_level = 'DEBUG';

    /**
     * Constructeur privé (Singleton)
     */
    private function __construct() {
        // Créer le fichier de log dans le répertoire cache du plugin
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/pdf-builder-pro-cache/logs';
        
        if (!is_dir($cache_dir)) {
            wp_mkdir_p($cache_dir);
        }
        
        $this->log_file = $cache_dir . '/canvas-save.log';
        
        // Déterminer le niveau minimum à partir des options
        $level = get_option('pdf_builder_canvas_log_level', 'DEBUG');
        if (isset($this->log_levels[$level])) {
            $this->min_level = $level;
        }
    }

    /**
     * Obtenir l'instance unique
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Enregistrer un message de log
     */
    private function log($level, $message, $data = null) {
        // Vérifier si ce niveau doit être enregistré
        if ($this->log_levels[$level] < $this->log_levels[$this->min_level]) {
            return;
        }

        $timestamp = current_time('Y-m-d H:i:s');
        $log_entry = "[$timestamp] [$level] $message";
        
        if ($data !== null) {
            $log_entry .= "\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
        
        $log_entry .= "\n" . str_repeat('-', 80) . "\n";
        
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Logger le début de sauvegarde
     */
    public function log_save_start($template_id, $template_name) {
        $this->log('INFO', 'Canvas Save Started', [
            'template_id' => $template_id,
            'template_name' => $template_name,
            'user_id' => get_current_user_id(),
            'user_login' => wp_get_current_user()->user_login,
        ]);
    }

    /**
     * Logger les éléments reçus
     */
    public function log_elements_received($elements, $count) {
        $this->log('DEBUG', "Received $count element(s) from frontend", [
            'element_count' => $count,
            'first_element' => !empty($elements) ? $elements[0] : null,
            'elements_summary' => array_map(function ($el) {
                return [
                    'id' => $el['id'] ?? 'N/A',
                    'type' => $el['type'] ?? 'N/A',
                    'x' => $el['x'] ?? 0,
                    'y' => $el['y'] ?? 0,
                    'width' => $el['width'] ?? 0,
                    'height' => $el['height'] ?? 0,
                ];
            }, array_slice($elements, 0, 5)), // Afficher max 5 premiers
        ]);
    }

    /**
     * Logger les propriétés du canvas
     */
    public function log_canvas_properties($canvas) {
        $this->log('DEBUG', 'Canvas Properties Received', [
            'zoom' => $canvas['zoom'] ?? 100,
            'width' => $canvas['width'] ?? 794,
            'height' => $canvas['height'] ?? 1123,
            'show_grid' => $canvas['show_grid'] ?? false,
            'show_margins' => $canvas['show_margins'] ?? false,
            'pan_x' => $canvas['pan_x'] ?? 0,
            'pan_y' => $canvas['pan_y'] ?? 0,
        ]);
    }

    /**
     * Logger la validation
     */
    public function log_validation($elements, $canvas) {
        $validation_errors = [];
        
        // Valider les éléments
        if (!is_array($elements)) {
            $validation_errors[] = 'Elements is not an array';
        } else {
            foreach ($elements as $i => $element) {
                if (empty($element['id'])) {
                    $validation_errors[] = "Element $i: Missing 'id'";
                }
                if (empty($element['type'])) {
                    $validation_errors[] = "Element $i: Missing 'type'";
                }
                if (!isset($element['x']) || !is_numeric($element['x'])) {
                    $validation_errors[] = "Element $i: Invalid 'x' coordinate";
                }
                if (!isset($element['y']) || !is_numeric($element['y'])) {
                    $validation_errors[] = "Element $i: Invalid 'y' coordinate";
                }
            }
        }

        // Valider le canvas
        if (!is_array($canvas)) {
            $validation_errors[] = 'Canvas is not an array';
        } else {
            if (!isset($canvas['zoom']) || !is_numeric($canvas['zoom'])) {
                $validation_errors[] = 'Canvas: Invalid zoom value';
            } elseif ($canvas['zoom'] < 10 || $canvas['zoom'] > 500) {
                $validation_errors[] = 'Canvas: Zoom out of range (10-500)';
            }
            
            if (!isset($canvas['width']) || !is_numeric($canvas['width'])) {
                $validation_errors[] = 'Canvas: Invalid width';
            }
            if (!isset($canvas['height']) || !is_numeric($canvas['height'])) {
                $validation_errors[] = 'Canvas: Invalid height';
            }
        }

        if (!empty($validation_errors)) {
            $this->log('WARNING', 'Validation Issues Found', [
                'error_count' => count($validation_errors),
                'errors' => $validation_errors,
            ]);
            return false;
        }

        $this->log('DEBUG', 'Validation Passed', [
            'element_count' => count($elements),
            'canvas_zoom' => $canvas['zoom'],
        ]);
        return true;
    }

    /**
     * Logger une erreur de sauvegarde
     */
    public function log_save_error($error_message, $error_data = null) {
        $this->log('ERROR', 'Canvas Save Error: ' . $error_message, [
            'error' => $error_message,
            'additional_data' => $error_data,
            'php_error' => error_get_last(),
        ]);
    }

    /**
     * Logger le succès de la sauvegarde
     */
    public function log_save_success($template_id, $element_count) {
        $this->log('INFO', 'Canvas Save Successful', [
            'template_id' => $template_id,
            'elements_saved' => $element_count,
            'memory_used' => memory_get_usage(true),
            'execution_time' => microtime(true),
        ]);
    }

    /**
     * Nettoyer les anciens logs (garde les 7 derniers jours)
     */
    public function cleanup_old_logs($days = 7) {
        if (!file_exists($this->log_file)) {
            return;
        }

        $lines = file($this->log_file, FILE_IGNORE_NEW_LINES);
        if (empty($lines)) {
            return;
        }

        $cutoff_date = strtotime("-$days days");
        $new_lines = [];

        foreach ($lines as $line) {
            // Vérifier si la ligne commence par un timestamp
            if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                $log_time = strtotime($matches[1]);
                if ($log_time > $cutoff_date) {
                    $new_lines[] = $line;
                }
            } elseif (!empty($line)) {
                // Conserver les lignes qui font partie d'une entrée récente
                $new_lines[] = $line;
            }
        }

        file_put_contents($this->log_file, implode("\n", $new_lines) . "\n", LOCK_EX);
        $this->log('INFO', 'Old logs cleaned', ['lines_removed' => count($lines) - count($new_lines)]);
    }

    /**
     * Obtenir les logs récents
     */
    public function get_recent_logs($limit = 50) {
        if (!file_exists($this->log_file)) {
            return [];
        }

        $lines = file($this->log_file, FILE_IGNORE_NEW_LINES);
        return array_slice($lines, -($limit * 10), $limit * 10); // Estimer 10 lignes par entrée
    }

    /**
     * Réinitialiser le fichier de log
     */
    public function clear_logs() {
        if (file_exists($this->log_file)) {
            file_put_contents($this->log_file, '');
        }
    }
}
