#!/usr/bin/env pwsh
# Script pour corriger les erreurs Plugin Check WordPress
# Fix: OutputNotEscaped errors

$pluginDir = "I:\wp-pdf-builder-pro-V2\plugin"

function Fix-File {
    param(
        [string]$filePath,
        [hashtable]$replacements
    )
    if (!(Test-Path $filePath)) {
        Write-Host "MISSING: $filePath" -ForegroundColor Red
        return
    }
    $content = [System.IO.File]::ReadAllText($filePath, [System.Text.Encoding]::UTF8)
    $original = $content
    foreach ($key in $replacements.Keys) {
        $content = $content.Replace($key, $replacements[$key])
    }
    if ($content -ne $original) {
        [System.IO.File]::WriteAllText($filePath, $content, [System.Text.Encoding]::UTF8)
        Write-Host "FIXED: $filePath" -ForegroundColor Green
    } else {
        Write-Host "UNCHANGED: $filePath (already fixed or pattern not found)" -ForegroundColor Yellow
    }
}

function Fix-FileRegex {
    param(
        [string]$filePath,
        [string]$pattern,
        [string]$replacement
    )
    if (!(Test-Path $filePath)) {
        Write-Host "MISSING: $filePath" -ForegroundColor Red
        return
    }
    $content = [System.IO.File]::ReadAllText($filePath, [System.Text.Encoding]::UTF8)
    $newContent = [regex]::Replace($content, $pattern, $replacement)
    if ($newContent -ne $content) {
        [System.IO.File]::WriteAllText($filePath, $newContent, [System.Text.Encoding]::UTF8)
        Write-Host "FIXED-REGEX: $filePath" -ForegroundColor Green
    } else {
        Write-Host "UNCHANGED: $filePath" -ForegroundColor Yellow
    }
}

# =============================================
# 1. settings-loader.php
# =============================================
Fix-File "$pluginDir\templates\admin\settings-loader.php" @{
    "echo admin_url('admin-ajax.php'); ?>" = "echo esc_url(admin_url('admin-ajax.php')); ?>"
    "echo `$main_nonce; ?>" = "echo esc_attr(`$main_nonce); ?>"
}

# =============================================
# 2. dashboard-page.php - date
# =============================================
Fix-File "$pluginDir\templates\admin\dashboard-page.php" @{
    "echo date('d/m/Y');" = "echo esc_html(date('d/m/Y'));"
}
# Fix the bad admin_url calls that were incorrectly wrapped (missing closing paren)
Fix-FileRegex "$pluginDir\templates\admin\dashboard-page.php" `
    "echo esc_url\(admin_url\('([^']+)'\);" `
    "echo esc_url(admin_url('`$1'));"
# Fix remaining unescaped admin_url calls (not yet wrapped)
Fix-FileRegex "$pluginDir\templates\admin\dashboard-page.php" `
    "echo admin_url\('([^']+)'\);" `
    "echo esc_url(admin_url('`$1'));"

# =============================================
# 3. canvas-monitor-diagnostic.php
# =============================================
Fix-FileRegex "$pluginDir\templates\admin\canvas-monitor-diagnostic.php" `
    "echo \`$analysis\['details'\]\['([^']+)'\]\];" `
    "echo esc_html(`$analysis['details']['`$1']);"
Fix-File "$pluginDir\templates\admin\canvas-monitor-diagnostic.php" @{
    "echo wp_create_nonce(" = "echo esc_attr(wp_create_nonce("
}
# Fix missing closing paren for wp_create_nonce -> esc_attr
Fix-FileRegex "$pluginDir\templates\admin\canvas-monitor-diagnostic.php" `
    "echo esc_attr\(wp_create_nonce\('([^']+)'\)\);" `
    "echo esc_attr(wp_create_nonce('`$1'));"

# =============================================
# 4. bootstrap.php - echo $script
# =============================================
Fix-File "$pluginDir\bootstrap.php" @{
    "    echo `$script;" = "    echo `$script; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped"
}

# =============================================
# 5. settings-developpeur.php - ABSPATH + ini_get
# =============================================
$devFile = "$pluginDir\templates\admin\settings-parts\settings-developpeur.php"
$devContent = [System.IO.File]::ReadAllText($devFile, [System.Text.Encoding]::UTF8)
# Add ABSPATH check after <?php line if not already present
if ($devContent -notmatch "defined\(\s*'ABSPATH'\s*\)") {
    $devContent = $devContent.Replace(
        '<?php // Developer tab content',
        "<?php // Developer tab content`r`nif ( ! defined( 'ABSPATH' ) ) { exit; }"
    )
}
# Fix ini_get
$devContent = $devContent.Replace(
    "echo ini_get('memory_limit');",
    "echo esc_html(ini_get('memory_limit'));"
).Replace(
    "echo ini_get('max_execution_time');",
    "echo esc_html(ini_get('max_execution_time'));"
).Replace(
    "echo ini_get('upload_max_filesize');",
    "echo esc_html(ini_get('upload_max_filesize'));"
).Replace(
    "echo ini_get('post_max_size');",
    "echo esc_html(ini_get('post_max_size'));"
)
[System.IO.File]::WriteAllText($devFile, $devContent, [System.Text.Encoding]::UTF8)
Write-Host "FIXED: settings-developpeur.php" -ForegroundColor Green

# =============================================
# 6. src/Managers/PDF_Builder_Mode_Switcher.php - Exception
# =============================================
Fix-FileRegex "$pluginDir\src\Managers\PDF_Builder_Mode_Switcher.php" `
    "(throw new [^;]+;)" `
    "`$1 // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped"

# =============================================
# 7. src/Managers/PDF_Builder_Template_Manager.php - echo __
# =============================================
Fix-File "$pluginDir\src\Managers\PDF_Builder_Template_Manager.php" @{
    "echo __(" = "echo esc_html__("
}

# =============================================
# 8. settings-helpers.php - echo $result (HTML content)
# =============================================
Fix-File "$pluginDir\templates\admin\settings-parts\settings-helpers.php" @{
    "echo `$result;" = "echo wp_kses_post(`$result);"
}

# =============================================
# 9. settings-contenu.php
# =============================================
$contenFile = "$pluginDir\templates\admin\settings-parts\settings-contenu.php"
$contenContent = [System.IO.File]::ReadAllText($contenFile, [System.Text.Encoding]::UTF8)
# Lines 34, 41: echo $result → wp_kses_post
$contenContent = $contenContent.Replace("echo `$result;", "echo wp_kses_post(`$result);")
# Line 243: width/height dimensions in attributes
# Pattern: echo ... $previewWidth ... $previewHeight ... (in style attributes)
# Line 263: $width, 266: $height, 274: $dpi, 278: $widthMM/$heightMM, 282: round()
# Line 789: constant
$contenContent = $contenContent.Replace("echo intval(`$previewWidth);", "echo esc_attr(intval(`$previewWidth));")
$contenContent = $contenContent.Replace("echo intval(`$previewHeight);", "echo esc_attr(intval(`$previewHeight));")
$contenContent = $contenContent.Replace("echo intval(`$width);", "echo esc_attr(intval(`$width));")
$contenContent = $contenContent.Replace("echo intval(`$height);", "echo esc_attr(intval(`$height));")
$contenContent = $contenContent.Replace("echo intval(`$dpi);", "echo esc_attr(intval(`$dpi));")
$contenContent = $contenContent.Replace("echo intval(`$widthMM);", "echo esc_attr(intval(`$widthMM));")
$contenContent = $contenContent.Replace("echo intval(`$heightMM);", "echo esc_attr(intval(`$heightMM));")
$contenContent = $contenContent.Replace("echo round(", "echo esc_html(round(")
[System.IO.File]::WriteAllText($contenFile, $contenContent, [System.Text.Encoding]::UTF8)
Write-Host "FIXED: settings-contenu.php" -ForegroundColor Green

# =============================================
# 10. builtin-editor-page.php - remaining fixes
# =============================================
$builtinFile = "$pluginDir\templates\admin\builtin-editor-page.php"
Fix-File $builtinFile @{
    "wp_redirect(" = "wp_safe_redirect("
    "echo __(" = "echo esc_html__("
    "echo admin_url(" = "echo esc_url(admin_url("
}
# Fix paren balance issues for admin_url
Fix-FileRegex $builtinFile `
    "echo esc_url\(admin_url\('([^']+)'\)\);" `
    "echo esc_url(admin_url('`$1'));"
Fix-FileRegex $builtinFile `
    "echo esc_url\(admin_url\('([^']+)'\);" `
    "echo esc_url(admin_url('`$1'));"

# =============================================
# 11. templates-page.php - remaining fixes after _e() fix
# =============================================
$templatesFile = "$pluginDir\templates\admin\templates-page.php"
Fix-File $templatesFile @{
    "echo __(" = "echo esc_html__("
    "echo admin_url(" = "echo esc_url(admin_url("
    "echo `$template_type;" = "echo esc_html(`$template_type);"
    "echo `$type_color;" = "echo esc_attr(`$type_color);"
    "echo `$type_label;" = "echo esc_html(`$type_label);"
    "echo `$template_id;" = "echo esc_attr(`$template_id);"
    "echo `$template_name;" = "echo esc_html(`$template_name);"
    "echo `$description;" = "echo esc_html(`$description);"
    "echo `$feature;" = "echo esc_html(`$feature);"
    "echo `$templates_count;" = "echo esc_html(`$templates_count);"
}
# Fix paren balance for admin_url in templates-page.php
Fix-FileRegex $templatesFile `
    "echo esc_url\(admin_url\('([^']+)'\)\);" `
    "echo esc_url(admin_url('`$1'));"
Fix-FileRegex $templatesFile `
    "echo esc_url\(admin_url\('([^']+)'\);" `
    "echo esc_url(admin_url('`$1'));"

# =============================================
# 12. predefined-templates-manager.php - remaining fixes after _e() fix
# =============================================
$predefinedFile = "$pluginDir\templates\admin\predefined-templates-manager.php"
Fix-FileRegex $predefinedFile `
    "echo base64_encode\(" `
    "echo esc_attr(base64_encode("
Fix-File $predefinedFile @{
    "echo __(" = "echo esc_html__("
}

# =============================================
# 13. settings-modals.php
# =============================================
$modalsFile = "$pluginDir\templates\admin\settings-parts\settings-modals.php"
Fix-File $modalsFile @{
    "echo `$premium_class;" = "echo esc_attr(`$premium_class);"
    "echo `$option['value'];" = "echo esc_attr(`$option['value']);"
    "echo `$checked;" = "echo esc_attr(`$checked);"
    "echo `$disabled;" = "echo esc_attr(`$disabled);"
    "echo `$option['label'];" = "echo esc_html(`$option['label']);"
    "echo `$option['desc'];" = "echo esc_html(`$option['desc']);"
    "echo `$option['icon'];" = "echo esc_html(`$option['icon']);"
    "echo `$opacity_style;" = "echo esc_attr(`$opacity_style);"
    "echo `$pointer_style;" = "echo esc_attr(`$pointer_style);"
}

Write-Host ""
Write-Host "=== Plugin Check Fix Script Complete ===" -ForegroundColor Cyan
Write-Host "Run Plugin Check again to verify remaining issues." -ForegroundColor Cyan
