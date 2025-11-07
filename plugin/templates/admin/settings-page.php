<?php
/**
 * PDF Builder Pro - Settings Page
 * Complete settings with all tabs
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

if (!is_user_logged_in() || !current_user_can('read')) {
    wp_die(__('You must be logged in', 'pdf-builder-pro'));
}

// Initialize
$notices = [];
$settings = get_option('pdf_builder_settings', []);

// Process form
if (isset($_POST['submit']) && isset($_POST['pdf_builder_settings_nonce'])) {
    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
        $to_save = [
            'debug_mode' => isset($_POST['debug_mode']),
            'log_level' => sanitize_text_field($_POST['log_level'] ?? 'info'),
            'cache_enabled' => isset($_POST['cache_enabled']),
            'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
            'max_template_size' => intval($_POST['max_template_size'] ?? 52428800),
            'max_execution_time' => intval($_POST['max_execution_time'] ?? 300),
            'memory_limit' => sanitize_text_field($_POST['memory_limit'] ?? '256M'),
            'pdf_quality' => sanitize_text_field($_POST['pdf_quality'] ?? 'high'),
            'default_format' => sanitize_text_field($_POST['default_format'] ?? 'A4'),
            'default_orientation' => sanitize_text_field($_POST['default_orientation'] ?? 'portrait'),
        ];
        update_option('pdf_builder_settings', array_merge($settings, $to_save));
        $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres enregistrés avec succès.</p></div>';
        $settings = get_option('pdf_builder_settings', []);
    } else {
        $notices[] = '<div class="notice notice-error"><p><strong>✗</strong> Erreur de sécurité. Veuillez réessayer.</p></div>';
    }
}
?>

<div class="wrap">
    <h1><?php _e('⚙️ PDF Builder Pro Settings', 'pdf-builder-pro'); ?></h1>
    
    <?php foreach ($notices as $notice) echo $notice; ?>
    
    <div class="nav-tab-wrapper wp-clearfix">
        <a href="#general" class="nav-tab nav-tab-active">Général</a>
        <a href="#licence" class="nav-tab">Licence</a>
        <a href="#performance" class="nav-tab">Performance</a>
        <a href="#pdf" class="nav-tab">PDF</a>
        <a href="#securite" class="nav-tab">Sécurité</a>
        <a href="#roles" class="nav-tab">Rôles</a>
        <a href="#notifications" class="nav-tab">Notifications</a>
        <a href="#canvas" class="nav-tab">Canvas</a>
        <a href="#templates" class="nav-tab">Templates</a>
        <a href="#maintenance" class="nav-tab">Maintenance</a>
        <a href="#developpeur" class="nav-tab">Développeur</a>
    </div>
    
    <form method="post" class="settings-form">
        <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_settings_nonce'); ?>
        
        <div id="general" class="tab-content" style="display: block;">
            <h2>Paramètres Généraux</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="log_level">Niveau de log</label></th>
                    <td>
                        <select id="log_level" name="log_level">
                            <option value="debug" <?php selected($settings['log_level'] ?? 'info', 'debug'); ?>>Debug</option>
                            <option value="info" <?php selected($settings['log_level'] ?? 'info', 'info'); ?>>Info</option>
                            <option value="warning" <?php selected($settings['log_level'] ?? 'info', 'warning'); ?>>Avertissement</option>
                            <option value="error" <?php selected($settings['log_level'] ?? 'info', 'error'); ?>>Erreur</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="debug_mode">Mode Debug</label></th>
                    <td>
                        <input type="checkbox" id="debug_mode" name="debug_mode" value="1" <?php checked($settings['debug_mode'] ?? false); ?> />
                        <p class="description">Active les logs détaillés pour le débogage</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cache_enabled">Cache activé</label></th>
                    <td>
                        <input type="checkbox" id="cache_enabled" name="cache_enabled" value="1" <?php checked($settings['cache_enabled'] ?? false); ?> />
                        <p class="description">Améliore les performances en mettant en cache les données</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cache_ttl">TTL du cache (secondes)</label></th>
                    <td>
                        <input type="number" id="cache_ttl" name="cache_ttl" value="<?php echo intval($settings['cache_ttl'] ?? 3600); ?>" min="0" max="86400" />
                        <p class="description">Durée de vie du cache en secondes (défaut: 3600)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_template_size">Taille max template (octets)</label></th>
                    <td>
                        <input type="number" id="max_template_size" name="max_template_size" value="<?php echo intval($settings['max_template_size'] ?? 52428800); ?>" min="1048576" />
                        <p class="description">Taille maximale des fichiers template (défaut: 50MB)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_execution_time">Temps max d'exécution (secondes)</label></th>
                    <td>
                        <input type="number" id="max_execution_time" name="max_execution_time" value="<?php echo intval($settings['max_execution_time'] ?? 300); ?>" min="1" max="3600" />
                        <p class="description">Temps maximum pour générer un PDF (défaut: 300)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="memory_limit">Limite mémoire</label></th>
                    <td>
                        <input type="text" id="memory_limit" name="memory_limit" value="<?php echo esc_attr($settings['memory_limit'] ?? '256M'); ?>" placeholder="256M" />
                        <p class="description">Ex: 256M, 512M, 1G (défaut: 256M)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pdf_quality">Qualité PDF</label></th>
                    <td>
                        <select id="pdf_quality" name="pdf_quality">
                            <option value="low" <?php selected($settings['pdf_quality'] ?? 'high', 'low'); ?>>Faible (fichiers plus petits)</option>
                            <option value="medium" <?php selected($settings['pdf_quality'] ?? 'high', 'medium'); ?>>Moyen</option>
                            <option value="high" <?php selected($settings['pdf_quality'] ?? 'high', 'high'); ?>>Élevée (meilleure qualité)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="default_format">Format PDF par défaut</label></th>
                    <td>
                        <select id="default_format" name="default_format">
                            <option value="A4" <?php selected($settings['default_format'] ?? 'A4', 'A4'); ?>>A4</option>
                            <option value="A3" <?php selected($settings['default_format'] ?? 'A4', 'A3'); ?>>A3</option>
                            <option value="Letter" <?php selected($settings['default_format'] ?? 'A4', 'Letter'); ?>>Letter</option>
                            <option value="Legal" <?php selected($settings['default_format'] ?? 'A4', 'Legal'); ?>>Legal</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="default_orientation">Orientation par défaut</label></th>
                    <td>
                        <select id="default_orientation" name="default_orientation">
                            <option value="portrait" <?php selected($settings['default_orientation'] ?? 'portrait', 'portrait'); ?>>Portrait</option>
                            <option value="landscape" <?php selected($settings['default_orientation'] ?? 'portrait', 'landscape'); ?>>Paysage</option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="licence" class="tab-content" style="display: none;">
            <h2>Licence</h2>
            <p>Configuration de licence...</p>
        </div>
        
        <div id="performance" class="tab-content" style="display: none;">
            <h2>Performance</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="cache_enabled">Cache activé</label></th>
                    <td>
                        <input type="checkbox" id="cache_enabled" name="cache_enabled" <?php checked($settings['cache_enabled'] ?? false); ?> />
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="pdf" class="tab-content" style="display: none;">
            <h2>Paramètres PDF</h2>
            <p>Configuration PDF...</p>
        </div>
        
        <div id="securite" class="tab-content" style="display: none;">
            <h2>Sécurité</h2>
            <p>Options de sécurité...</p>
        </div>
        
        <div id="roles" class="tab-content" style="display: none;">
            <h2>Rôles & Permissions</h2>
            <p>Gestion des rôles...</p>
        </div>
        
        <div id="notifications" class="tab-content" style="display: none;">
            <h2>Notifications</h2>
            <p>Paramètres de notification...</p>
        </div>
        
        <div id="canvas" class="tab-content" style="display: none;">
            <h2>Canvas</h2>
            <p>Configuration Canvas...</p>
        </div>
        
        <div id="templates" class="tab-content" style="display: none;">
            <h2>Templates</h2>
            <p>Gestion des templates...</p>
        </div>
        
        <div id="maintenance" class="tab-content" style="display: none;">
            <h2>Maintenance</h2>
            <p>Outils de maintenance...</p>
        </div>
        
        <div id="developpeur" class="tab-content" style="display: none;">
            <h2>Mode Développeur</h2>
            <p>Options développeur...</p>
        </div>
        
        <p class="submit">
            <button type="submit" name="submit" class="button button-primary">Enregistrer les paramètres</button>
        </p>
    </form>
</div>

<style>
    .nav-tab-wrapper {
        border-bottom: 1px solid #ccc;
        margin: 20px 0;
        white-space: nowrap;
        overflow-x: auto;
    }
    
    .nav-tab {
        background: #f5f5f5;
        border: 1px solid #ccc;
        border-bottom: none;
        color: #0073aa;
        cursor: pointer;
        margin-right: 2px;
        padding: 10px 15px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s;
        white-space: nowrap;
    }
    
    .nav-tab:hover {
        background: #e9e9e9;
    }
    
    .nav-tab-active {
        background: #fff;
        border-bottom: 1px solid #fff;
        color: #000;
        font-weight: bold;
    }
    
    .tab-content {
        background: #fff;
        padding: 20px;
        border: 1px solid #ccc;
        border-top: none;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.nav-tab');
    const contents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            contents.forEach(function(c) { c.style.display = 'none'; });
            tabs.forEach(function(t) { t.classList.remove('nav-tab-active'); });
            
            const id = this.getAttribute('href').substring(1);
            document.getElementById(id).style.display = 'block';
            this.classList.add('nav-tab-active');
        });
    });
});
</script>

<?php wp_footer(); ?>
