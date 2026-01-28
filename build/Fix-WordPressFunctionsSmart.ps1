# Correction intelligente des fonctions WordPress
# Utilise une analyse syntaxique pour éviter les remplacements dans les chaînes/commentaires

param(
    [string]$Path = "..\plugin\src"
)

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "PDF BUILDER PRO - CORRECTION WORDPRESS INTELLIGENTE"
Write-Host "==========================================" -ForegroundColor Cyan

# Liste des fonctions WordPress communes
$wordpressFunctions = @(
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
)

# Function to intelligently fix WordPress functions
function Fix-WordPressFunctionsSmart {
    param([string]$FilePath]

    try {
        $content = Get-Content $FilePath -Raw
        $originalContent = $content

        # Skip if no namespace
        if ($content -notmatch 'namespace\s+[^;]+;') {
            return $false
        }

        $changes = 0

        # For each WordPress function, find actual function calls
        foreach ($function in $wordpressFunctions) {
            # Pattern to match function calls: word boundary + function name + opening parenthesis
            # But exclude if preceded by backslash, quote, or inside comments
            $pattern = '(?<!\\)(?<![\'"])(?<!//.*)(?<!/\*.*)\b' + [regex]::Escape($function) + '\s*\('

            # Use a more sophisticated approach: find positions and check context
            $matches = [regex]::Matches($content, $pattern)

            foreach ($match in $matches) {
                $index = $match.Index
                $length = $match.Length

                # Check if we're inside a string or comment by looking backwards
                $beforeMatch = $content.Substring(0, $index)

                # Count unescaped quotes before this position
                $singleQuotes = ($beforeMatch -replace '\\.', '' | Select-String -Pattern "'" -AllMatches).Matches.Count
                $doubleQuotes = ($beforeMatch -replace '\\.', '' | Select-String -Pattern '"' -AllMatches).Matches.Count

                # If we have odd number of quotes, we're inside a string
                if (($singleQuotes % 2 -eq 1) -or ($doubleQuotes % 2 -eq 1)) {
                    continue
                }

                # Check for single-line comments
                $lastLineStart = $beforeMatch.LastIndexOf("`n")
                if ($lastLineStart -eq -1) { $lastLineStart = 0 }
                $currentLine = $beforeMatch.Substring($lastLineStart)
                if ($currentLine -match '^\s*//') {
                    continue
                }

                # Check for multi-line comments
                $commentStart = $beforeMatch.LastIndexOf("/*")
                $commentEnd = $beforeMatch.LastIndexOf("*/")
                if ($commentStart -gt $commentEnd) {
                    continue
                }

                # If we get here, it's a valid function call to prefix
                $replacement = "\" + $function"
                $content = $content.Remove($index, $length).Insert($index, $replacement)
                $changes++
            }
        }

        # Write back if changes were made
        if ($content -ne $originalContent) {
            Set-Content $FilePath $content -Encoding UTF8
            Write-Host "Corrige: $FilePath ($changes fonctions)" -ForegroundColor Green
            return $true
        }
    }
    catch {
        Write-Host "Erreur: $($_.Exception.Message)" -ForegroundColor Red
    }

    return $false
}

# Find all PHP files with namespaces
$phpFiles = Get-ChildItem -Path $Path -Recurse -Filter "*.php" | Where-Object {
    $filePath = $_.FullName
    $content = Get-Content $filePath -Raw
    $content -match 'namespace\s+[^;]+;'
}

Write-Host "Fichiers PHP avec namespaces trouves: $($phpFiles.Count)" -ForegroundColor Yellow

$fixedCount = 0
foreach ($file in $phpFiles) {
    if (Fix-WordPressFunctionsSmart -FilePath $file.FullName) {
        $fixedCount++
    }
}

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "CORRECTION TERMINEE"
Write-Host "Fichiers corriges: $fixedCount"
Write-Host "==========================================" -ForegroundColor Cyan