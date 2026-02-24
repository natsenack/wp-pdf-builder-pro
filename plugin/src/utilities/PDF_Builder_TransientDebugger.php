<?php
/**
 * Outil de diagnostic pour les transients de mise à jour
 * À charger SEULEMENT en debug mode
 */

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_TransientDebugger {
    /**
     * Ajouter le diagnostic à la page des plugins
     */
    public static function add_debug_output() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (!isset($_GET['page']) || $_GET['page'] !== 'plugins.php') {
            // Toujours afficher en bas de /wp-admin/plugins.php
            $screen = get_current_screen();
            if (!$screen || $screen->id !== 'plugins') {
                return;
            }
        }
        
        echo self::get_debug_info();
    }
    
    /**
     * Générer les infos de debug
     */
    public static function get_debug_info() {
        $output = '';
        
        // Section pour PDF Builder Pro
        $output .= '<div style="margin-top: 40px; padding: 20px; background: #f5f5f5; border: 1px solid #ddd; font-family: monospace;">';
        $output .= '<h3>🔍 PDF Builder Pro - Transient Debug</h3>';
        
        // Transient update_plugins
        $update_plugins_transient = get_site_transient('update_plugins');
        
        $output .= '<p><strong>Site Transient \'update_plugins\' status:</strong></p>';
        if (!$update_plugins_transient) {
            $output .= '<span style="color: red;">❌ MISSING</span>';
        } else {
            $output .= '<span style="color: green;">✅ EXISTS</span>';
            
            $plugin_file = __DIR__ . '/../../pdf-builder-pro.php';
            $basename = plugin_basename($plugin_file);
            
            $output .= '<p><strong>Looking for plugin basename:</strong> <code>' . esc_html($basename) . '</code></p>';
            
            if (isset($update_plugins_transient->response)) {
                $output .= '<p><strong>Response plugins in transient:</strong></p>';
                $output .= '<ul>';
                foreach (array_keys((array) $update_plugins_transient->response) as $plugin_slug) {
                    $match = ($plugin_slug === $basename) ? ' ✅ MATCH!' : '';
                    $output .= '<li><code>' . esc_html($plugin_slug) . '</code>' . $match . '</li>';
                }
                $output .= '</ul>';
                
                if (isset($update_plugins_transient->response[$basename])) {
                    $update = $update_plugins_transient->response[$basename];
                    $output .= '<p><strong>PDF Builder Pro update info:</strong></p>';
                    $output .= '<pre style="background: white; padding: 10px; border: 1px solid #ccc;">';
                    $output .= esc_html(json_encode($update, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    $output .= '</pre>';
                } else {
                    $output .= '<p style="color: red;">❌ PDF Builder Pro NOT in response array</p>';
                }
            }
            
            if (isset($update_plugins_transient->no_update)) {
                $output .= '<p><strong>no_update plugins:</strong> ' . implode(', ', array_keys((array) $update_plugins_transient->no_update)) . '</p>';
            }
            
            $output .= '<p><strong>Last checked:</strong> ' . ($update_plugins_transient->last_checked ?? 'N/A') . ' (age: ' . (time() - $update_plugins_transient->last_checked) . 's)</p>';
        }
        
        // Manuel check button
        $output .= '<p style="margin-top: 20px;">';
        $output .= '<button onclick="fetch(\'' . admin_url('admin-ajax.php') . '?action=pdf_builder_test_update_check\', {';
        $output .= 'method: \'POST\',';
        $output .= 'headers: {\'X-Requested-With\': \'XMLHttpRequest\'},';
        $output .= '}).then(r => r.json()).then(d => alert(JSON.stringify(d, null, 2)))"';
        $output .= ' style="padding: 10px 20px; background: #0073aa; color: white; border: none; cursor: pointer; border-radius: 3px;">🔄 Force Check Remote Version</button>';
        $output .= '</p>';
        
        $output .= '</div>';
        
        return $output;
    }
}

// Ajouter au footer Admin
add_action('admin_footer', ['PDF_Builder_TransientDebugger', 'add_debug_output'], 999);
