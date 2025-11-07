<?php
/**
 * Page des Paramètres - PDF Builder Pro
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

if (!is_user_logged_in() || !current_user_can('read')) {
    wp_die(__('Vous devez être connecté pour accéder à cette page.', 'pdf-builder-pro'));
}

// Configuration
class TempConfig {
    private $option_name = 'pdf_builder_settings';

    public function get($key, $default = '') {
        $settings = get_option($this->option_name, []);
        return isset($settings[$key]) ? $settings[$key] : $default;
    }

    public function set_multiple($settings) {
        $current_settings = get_option($this->option_name, []);
        update_option($this->option_name, array_merge($current_settings, $settings));
    }
}

$config = new TempConfig();
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Traiter les soumissions de formulaire
$admin_notices = [];

if (isset($_POST['submit']) && isset($_POST['pdf_builder_settings_nonce'])) {
    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
        $settings = [
            'debug_mode' => isset($_POST['debug_mode']),
            'cache_enabled' => isset($_POST['cache_enabled']),
            'canvas_width' => intval($_POST['canvas_width'] ?? 794),
        ];
        $config->set_multiple($settings);
        
        if ($isAjax) {
            wp_send_json_success(['message' => __('Paramètres sauvegardés.', 'pdf-builder-pro')]);
            exit;
        } else {
            $admin_notices[] = '<div class="notice notice-success"><p>' . __('Paramètres sauvegardés avec succès.', 'pdf-builder-pro') . '</p></div>';
        }
    }
}

if ($isAjax) {
    exit;
}

$settings = get_option('pdf_builder_settings', []);
?>

<div class="wrap">
    <h1><?php _e('⚙️ Paramètres - PDF Builder Pro', 'pdf-builder-pro'); ?></h1>
    
    <?php
    foreach ($admin_notices as $notice) {
        echo $notice;
    }
    ?>
    
    <div class="nav-tab-wrapper wp-clearfix">
        <a href="#general" class="nav-tab nav-tab-active">Général</a>
        <a href="#advanced" class="nav-tab">Avancé</a>
        <a href="#canvas" class="nav-tab">Canvas</a>
    </div>
    
    <form method="post" class="settings-form">
        <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_settings_nonce'); ?>
        
        <div id="general" class="tab-content" style="display: block; padding: 20px; background: #fff; border: 1px solid #ccc;">
            <h2>Paramètres Généraux</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="debug_mode">Mode Debug</label></th>
                    <td>
                        <input type="checkbox" id="debug_mode" name="debug_mode" <?php checked($settings['debug_mode'] ?? false); ?> />
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="advanced" class="tab-content" style="display: none; padding: 20px; background: #fff; border: 1px solid #ccc;">
            <h2>Paramètres Avancés</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="cache_enabled">Cache activé</label></th>
                    <td>
                        <input type="checkbox" id="cache_enabled" name="cache_enabled" <?php checked($settings['cache_enabled'] ?? false); ?> />
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="canvas" class="tab-content" style="display: none; padding: 20px; background: #fff; border: 1px solid #ccc;">
            <h2>Canvas</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="canvas_width">Largeur Canvas (px)</label></th>
                    <td>
                        <input type="number" id="canvas_width" name="canvas_width" value="<?php echo esc_attr($settings['canvas_width'] ?? 794); ?>" />
                    </td>
                </tr>
            </table>
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
    }
    
    .nav-tab {
        background: #f5f5f5;
        border: 1px solid #ccc;
        border-bottom: none;
        color: #0073aa;
        cursor: pointer;
        margin-right: 5px;
        padding: 10px 15px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s;
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
        margin-top: 0;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.nav-tab');
    const contents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Hide all tabs
            contents.forEach(function(content) {
                content.style.display = 'none';
            });
            
            // Remove active class from all tabs
            tabs.forEach(function(t) {
                t.classList.remove('nav-tab-active');
            });
            
            // Show selected tab
            const tabId = this.getAttribute('href').substring(1);
            const selectedContent = document.getElementById(tabId);
            if (selectedContent) {
                selectedContent.style.display = 'block';
            }
            
            // Add active class to clicked tab
            this.classList.add('nav-tab-active');
        });
    });
});
</script>

<?php wp_footer(); ?>
