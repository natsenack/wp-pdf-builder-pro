<?php
/**
 * PDF Builder Pro - Settings Page
 */
if (!defined('ABSPATH')) exit('Direct access denied');
if (!is_user_logged_in() || !current_user_can('read')) wp_die('Must be logged in');

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

if (isset($_POST['submit']) && isset($_POST['pdf_builder_settings_nonce'])) {
    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
        $settings = ['debug_mode' => isset($_POST['debug_mode'])];
        $config->set_multiple($settings);
        if ($isAjax) { wp_send_json_success(['message' => 'Saved']); exit; }
    }
}
if ($isAjax) exit;
$settings = get_option('pdf_builder_settings', []);
?>
<div class="wrap">
    <h1><?php _e('PDF Builder Pro - Settings', 'pdf-builder-pro'); ?></h1>
    
    <div class="nav-tab-wrapper">
        <a href="#general" class="nav-tab nav-tab-active">General</a>
        <a href="#advanced" class="nav-tab">Advanced</a>
        <a href="#canvas" class="nav-tab">Canvas</a>
    </div>
    
    <form method="post">
        <div id="general" class="tab-content" style="display:block; padding:20px; background:#fff; border:1px solid #ccc;">
            <h2>General Settings</h2>
            <table class="form-table">
                <tr><th>Debug</th><td><input type="checkbox" name="debug_mode" <?php checked($settings['debug_mode'] ?? false); ?> /></td></tr>
            </table>
        </div>
        
        <div id="advanced" class="tab-content" style="display:none; padding:20px; background:#fff; border:1px solid #ccc;">
            <h2>Advanced</h2>
            <table class="form-table">
                <tr><th>Cache</th><td><input type="checkbox" name="cache_enabled" /></td></tr>
            </table>
        </div>
        
        <div id="canvas" class="tab-content" style="display:none; padding:20px; background:#fff; border:1px solid #ccc;">
            <h2>Canvas</h2>
            <table class="form-table">
                <tr><th>Width</th><td><input type="number" name="canvas_width" value="794" /></td></tr>
            </table>
        </div>
        
        <p class="submit">
            <input type="submit" name="submit" class="button button-primary" value="Save Settings" />
            <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_settings_nonce'); ?>
        </p>
    </form>
    
    <div style="background: #000; color: #0f0; padding: 50px; margin: 50px 0; min-height: 500px; text-align: center;">
        <p>TEST BOX - Footer should be BELOW</p>
    </div>
</div>

<?php
wp_footer();

// Tab switching JavaScript
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.nav-tab').forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            var tabId = this.getAttribute('href').substring(1);
            document.querySelectorAll('.tab-content').forEach(function(el) { el.style.display = 'none'; });
            document.getElementById(tabId).style.display = 'block';
            document.querySelectorAll('.nav-tab').forEach(function(el) { el.classList.remove('nav-tab-active'); });
            this.classList.add('nav-tab-active');
        });
    });
});
</script>
