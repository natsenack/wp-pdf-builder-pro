<?php
/**
 * Page des Paramètres - PDF Builder Pro
 * VERSION TEST - FOOTER DEBUG
 */
if (!defined('ABSPATH')) {
    exit('Direct access denied');
}
if (!is_user_logged_in() || !current_user_can('read')) {
    wp_die('Must be logged in');
}
?>
<div class="wrap">
    <h1>Settings</h1>
    <div style="background: #000; color: #0f0; padding: 50px; margin: 50px 0; min-height: 500px;">
        <p>CONTENT AREA</p>
        <p style="margin-top: 200px;">Footer should be BELOW this box</p>
    </div>
</div>
<?php
wp_footer();
class TempConfig {
    private $option_name = 'pdf_builder_settings';
    public function get($key, $default = '') {
        $settings = get_option($this->option_name, []);
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
    public function set_multiple($settings) {
        $current_settings = get_option($this->option_name, []);
        $updated_settings = array_merge($current_settings, $settings);
        update_option($this->option_name, $updated_settings);
    }
}
$config = new TempConfig();

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$admin_notices = array();

// Traitement simple des paramètres
if (isset($_POST['submit']) && isset($_POST['pdf_builder_settings_nonce'])) {
    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
        $settings = [
            'debug_mode' => isset($_POST['debug_mode']),
            'cache_enabled' => isset($_POST['cache_enabled']),
        ];
        $config->set_multiple($settings);
        
        if ($isAjax) {
            wp_send_json_success(['message' => 'Paramètres sauvegardés.']);
            exit;
        } else {
            $admin_notices[] = '<div class="notice notice-success"><p>Paramètres sauvegardés.</p></div>';
        }
    }
}

if ($isAjax) {
    exit;
}
?>
