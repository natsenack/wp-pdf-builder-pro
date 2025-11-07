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
}
$config = new TempConfig();
?>
