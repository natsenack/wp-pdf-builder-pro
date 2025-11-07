<?php
if (!defined("ABSPATH")) exit("Direct access denied");
if (!is_user_logged_in() || !current_user_can("read")) wp_die("Must be logged in");

class TempConfig {
    private $option_name = "pdf_builder_settings";
    public function get($key, $default = "") {
        $settings = get_option($this->option_name, []);
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
    public function set_multiple($settings) {
        $current_settings = get_option($this->option_name, []);
        update_option($this->option_name, array_merge($current_settings, $settings));
    }
}

$config = new TempConfig();
$isAjax = isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest";
$admin_notices = [];

if (isset($_POST["submit"]) && isset($_POST["pdf_builder_settings_nonce"])) {
    if (wp_verify_nonce($_POST["pdf_builder_settings_nonce"], "pdf_builder_settings")) {
        $settings = ["debug_mode" => isset($_POST["debug_mode"]), "cache_enabled" => isset($_POST["cache_enabled"])];
        $config->set_multiple($settings);
        if ($isAjax) { wp_send_json_success(["message" => "Saved."]); exit; }
        else { $admin_notices[] = "<div class=\"notice notice-success\"><p>Saved.</p></div>"; }
    }
}

if ($isAjax) exit;
$settings = get_option("pdf_builder_settings", []);
?>
<div class="wrap">
    <h1>Settings</h1>
    
    <div class="nav-tab-wrapper">
        <a href="#general" class="nav-tab nav-tab-active">General</a>
        <a href="#advanced" class="nav-tab">Advanced</a>
    </div>
    
    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        var navTabs = document.querySelectorAll('.nav-tab');
        navTabs.forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                var tabHref = this.getAttribute('href');
                var targetId = tabHref.substring(1);
                var targetElement = document.getElementById(targetId);
                
                if (!targetElement) return;
                
                // Hide all tabs
                document.querySelectorAll('.tab-content').forEach(function(el) {
                    el.style.display = 'none';
                });
                
                // Remove active class from all nav tabs
                document.querySelectorAll('.nav-tab').forEach(function(el) {
                    el.classList.remove('nav-tab-active');
                });
                
                // Show selected tab
                targetElement.style.display = 'block';
                this.classList.add('nav-tab-active');
            });
        });
    });
    </script>
    
    <form method="post">
        <div id="general" class="tab-content active" style="display:block; padding:20px; background:#f5f5f5;">
            <h2>General Settings</h2>
            <table class="form-table">
                <tr>
                    <th>Debug Mode</th>
                    <td><input type="checkbox" name="debug_mode" value="1" <?php checked($settings["debug_mode"] ?? false); ?> /></td>
                </tr>
            </table>
        </div>
        
        <div id="advanced" class="tab-content" style="display:none; padding:20px; background:#f5f5f5;">
            <h2>Advanced Settings</h2>
            <table class="form-table">
                <tr>
                    <th>Cache</th>
                    <td><input type="checkbox" name="cache_enabled" value="1" <?php checked($settings["cache_enabled"] ?? false); ?> /></td>
                </tr>
            </table>
        </div>
        
        <p class="submit">
            <input type="submit" name="submit" class="button button-primary" value="Save" />
            <?php wp_nonce_field("pdf_builder_settings", "pdf_builder_settings_nonce"); ?>
        </p>
    </form>
    
    <div style="background: #000; color: #0f0; padding: 50px; margin: 50px 0; min-height: 1500px; text-align: center;">
        <p>TEST BOX - Footer should be below</p>
    </div>
</div>

<?php wp_footer(); ?>
