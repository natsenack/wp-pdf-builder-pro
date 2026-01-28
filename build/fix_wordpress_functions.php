<?php
/**
 * Script PHP pour corriger les fonctions WordPress dans les namespaces
 */

$wordpressFunctions = [
    "add_action", "add_filter", "add_menu_page", "add_submenu_page", "add_settings_section", "add_settings_field",
    "wp_enqueue_script", "wp_enqueue_style", "wp_localize_script", "wp_create_nonce", "wp_verify_nonce",
    "current_user_can", "wp_die", "wp_send_json", "wp_send_json_success", "wp_send_json_error",
    "get_option", "update_option", "delete_option", "add_option",
    "wp_kses", "wp_kses_post", "esc_html", "esc_attr", "esc_url", "esc_textarea",
    "sanitize_text_field", "sanitize_email", "sanitize_textarea_field", "is_email",
    "get_current_user_id", "wp_get_current_user", "get_userdata",
    "get_post", "get_posts", "wp_insert_post", "wp_update_post", "wp_delete_post",
    "update_post_meta", "get_post_meta", "delete_post_meta",
    "wp_upload_dir", "wp_mkdir_p", "wp_handle_upload",
    "plugin_dir_path", "plugin_dir_url", "plugins_url",
    "admin_url", "home_url", "site_url",
    "get_bloginfo", "get_theme_mod",
    "wp_redirect", "wp_safe_redirect",
    "is_admin", "is_user_logged_in",
    "checked", "selected", "disabled",
    "do_action", "apply_filters",
    "wp_nonce_field", "settings_fields",
    "get_transient", "set_transient", "delete_transient",
    "wp_cache_get", "wp_cache_set", "wp_cache_delete",
    "did_action", "doing_action",
    "wp_unslash", "stripslashes_deep",
    "absint", "intval", "floatval",
    "wp_parse_args", "wp_parse_id_list",
    "get_terms", "wp_set_post_terms", "wp_get_post_terms",
    "get_the_title", "get_the_content", "get_the_excerpt",
    "have_posts", "the_post", "wp_reset_postdata",
    "get_query_var", "set_query_var",
    "wp_insert_user", "wp_update_user", "get_user_by",
    "wp_logout", "wp_set_auth_cookie", "wp_clear_auth_cookie",
    "wp_hash", "wp_generate_password",
    "wp_mail", "get_bloginfo",
    "wp_remote_get", "wp_remote_post", "wp_remote_request",
    "is_wp_error", "WP_Error",
    "get_locale", "load_textdomain",
    "__", "_e", "_x", "_ex", "_n", "_nx",
    "date_i18n", "current_time",
    "wp_schedule_event", "wp_unschedule_event", "wp_next_scheduled",
    "wp_cron", "spawn_cron",
    "get_site_option", "update_site_option", "delete_site_option",
    "switch_to_blog", "restore_current_blog",
    "get_sites", "wp_insert_site", "wp_update_site", "wp_delete_site"
];

function fixWordPressFunctions($filePath) {
    global $wordpressFunctions;
    $content = file_get_contents($filePath);

    // Check if file has namespace
    if (!preg_match('/namespace\s+[^;]+;/', $content)) {
        return false;
    }

    $originalContent = $content;
    $changes = 0;

    foreach ($wordpressFunctions as $function) {
        // Use a more precise regex to match function calls
        // This pattern looks for word boundaries around the function name followed by opening parenthesis
        $pattern = '/\b' . preg_quote($function, '/') . '\s*\(/';

        // Use preg_replace_callback to check context
        $content = preg_replace_callback($pattern, function($match) use ($content, $function) {
            $pos = strpos($content, $match[0]);

            // Check if already prefixed
            if ($pos > 0 && $content[$pos - 1] === '\\') {
                return $match[0];
            }

            // Check if inside string
            $before = substr($content, 0, $pos);
            $singleQuotes = substr_count($before, "'") - substr_count($before, "\\'");
            $doubleQuotes = substr_count($before, '"') - substr_count($before, '\\"');

            if ($singleQuotes % 2 !== 0 || $doubleQuotes % 2 !== 0) {
                return $match[0]; // Inside string
            }

            // Check for comments
            $lines = explode("\n", $before);
            $currentLine = end($lines);

            if (preg_match('/^\s*\/\//', $currentLine)) {
                return $match[0]; // Single line comment
            }

            // Check for multi-line comments
            $commentStart = strrpos($before, '/*');
            $commentEnd = strrpos($before, '*/');
            if ($commentStart !== false && ($commentEnd === false || $commentStart > $commentEnd)) {
                return $match[0]; // Inside multi-line comment
            }

            // Valid function call - add backslash
            return '\\' . $function . str_replace($function, '', $match[0]);
        }, $content);
    }

    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo "Corrige: $filePath\n";
        return true;
    }

    return false;
}

// Find all PHP files with namespaces
$directory = new RecursiveDirectoryIterator('I:\wp-pdf-builder-pro-V2\plugin\src');
$iterator = new RecursiveIteratorIterator($directory);
$phpFiles = [];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (preg_match('/namespace\s+[^;]+;/', $content)) {
            $phpFiles[] = $file->getPathname();
        }
    }
}

echo "Fichiers PHP avec namespaces trouves: " . count($phpFiles) . "\n";

$fixedCount = 0;
foreach ($phpFiles as $file) {
    if (fixWordPressFunctions($file)) {
        $fixedCount++;
    }
}

echo "==========================================\n";
echo "CORRECTION TERMINEE\n";
echo "Fichiers corriges: $fixedCount\n";
echo "==========================================\n";
?>