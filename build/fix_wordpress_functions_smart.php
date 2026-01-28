<?php
/**
 * Script PHP intelligent pour corriger les fonctions WordPress dans les namespaces
 * Utilise l'analyseur de tokens PHP pour une détection précise
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
    $tokens = token_get_all($content);
    $output = '';
    $changes = 0;

    for ($i = 0; $i < count($tokens); $i++) {
        $token = $tokens[$i];

        if (is_array($token)) {
            $tokenType = $token[0];
            $tokenValue = $token[1];

            // Look for T_STRING tokens that match WordPress functions
            if ($tokenType === T_STRING && in_array($tokenValue, $wordpressFunctions)) {
                // Check if next token is opening parenthesis (function call)
                $nextToken = isset($tokens[$i + 1]) ? $tokens[$i + 1] : null;
                if ($nextToken === '(' || (is_array($nextToken) && $nextToken[0] === T_WHITESPACE && isset($tokens[$i + 2]) && $tokens[$i + 2] === '(')) {

                    // Check if already prefixed with backslash
                    $prevToken = isset($tokens[$i - 1]) ? $tokens[$i - 1] : null;
                    if ($prevToken !== '\\' && (!is_array($prevToken) || $prevToken[0] !== T_NS_SEPARATOR)) {
// Check if we're inside a string or comment by counting delimiters
                    $inString = false;
                    $inComment = false;
                    $stringChar = '';
                    $commentType = '';

                    // Scan backwards to check context by counting string/comment delimiters
                    for ($j = $i - 1; $j >= 0; $j--) {
                        $checkToken = $tokens[$j];

                        if (is_array($checkToken)) {
                            // Skip whitespace and other non-significant tokens for context checking
                            if ($checkToken[0] === T_WHITESPACE || $checkToken[0] === T_STRING || $checkToken[0] === T_LNUMBER || $checkToken[0] === T_VARIABLE) {
                                continue;
                            }

                            if ($checkToken[0] === T_COMMENT || $checkToken[0] === T_DOC_COMMENT) {
                                // Check if this comment contains our position
                                $commentStart = $checkToken[2]; // line number
                                $commentContent = $checkToken[1];
                                $commentLines = substr_count($commentContent, "\n") + 1;
                                $commentEndLine = $commentStart + $commentLines - 1;

                                // Get current line number
                                $currentLine = 0;
                                for ($k = 0; $k <= $i; $k++) {
                                    if (is_array($tokens[$k]) && isset($tokens[$k][2])) {
                                        $currentLine = $tokens[$k][2];
                                    }
                                }

                                if ($currentLine >= $commentStart && $currentLine <= $commentEndLine) {
                                    $inComment = true;
                                    break;
                                }
                            }
                        } elseif ($checkToken === '"' || $checkToken === "'") {
                            // Count string delimiters - if odd number, we're inside a string
                            $stringCount = 0;
                            for ($k = 0; $k <= $j; $k++) {
                                $t = $tokens[$k];
                                if ($t === $checkToken) {
                                    $stringCount++;
                                }
                            }
                            if ($stringCount % 2 === 1) {
                                $inString = true;
                                break;
                            }
                            }
                        }

                        if (!$inString && !$inComment) {
                            // Valid function call - add backslash
                            $output .= '\\' . $tokenValue;
                            $changes++;
                            continue;
                        }
                    }
                }
            }

            $output .= $tokenValue;
        } else {
            $output .= $token;
        }
    }

    if ($changes > 0) {
        file_put_contents($filePath, $output);
        echo "Corrige: $filePath ($changes changements)\n";
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