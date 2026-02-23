#!/usr/bin/env pwsh
# Fix settings-modals.php concatenation escaping issues

$path = "I:\wp-pdf-builder-pro-V2\plugin\templates\admin\settings-parts\settings-modals.php"
$c = [System.IO.File]::ReadAllText($path, [System.Text.Encoding]::UTF8)
$orig = $c

# Fix attribute context variables
$c = $c.Replace('. $premium_class . ', '. esc_attr($premium_class) . ')
$c = $c.Replace(". `$premium_class .", ". esc_attr(`$premium_class) .")

# For value attribute
$c = $c.Replace("value=`"' . `$option['value'] . '`"", "value=`"' . esc_attr(`$option['value']) . '`"")

# For checked and disabled inside input tags
$c = $c.Replace("' . `$checked . ' ' . `$disabled", "' . esc_attr(`$checked) . ' ' . esc_attr(`$disabled)")

# For label, desc, icon (text content)
$c = $c.Replace("'. `$option['label'] . '", "'. esc_html(`$option['label']) . '")
$c = $c.Replace("'. `$option['desc'] . '", "'. esc_html(`$option['desc']) . '")
$c = $c.Replace("'. `$option['icon'] . ' ' . `$option['label']", "'. esc_html(`$option['icon']) . ' ' . esc_html(`$option['label'])")

# For style attributes
$c = $c.Replace("'. `$opacity_style . ' ' . `$pointer_style", "'. esc_attr(`$opacity_style) . ' ' . esc_attr(`$pointer_style)")
$c = $c.Replace("'. `$opacity_style .'", "'. esc_attr(`$opacity_style) .'")
$c = $c.Replace("'. `$pointer_style", "'. esc_attr(`$pointer_style)")

if ($c -ne $orig) {
    [System.IO.File]::WriteAllText($path, $c, [System.Text.Encoding]::UTF8)
    Write-Host "FIXED: settings-modals.php"
} else {
    Write-Host "UNCHANGED: settings-modals.php"
}
