# Script pour extraire les blocs <style> des fichiers PHP et les déplacer vers admin.css
# Usage: .\extract-inline-styles.ps1

$cssFile = "i:\wp-pdf-builder-pro-V2\src\css\pdf-builder-admin.css"
$workspaceRoot = "i:\wp-pdf-builder-pro-V2"

# Classes WordPress/natives ou états JS dynamiques à ne pas préfixer
$wpClasses = @('button', 'button-primary', 'button-secondary', 'button-link', 'form-table',
    'notice', 'notice-info', 'notice-success', 'notice-warning', 'notice-error',
    'dashicons', 'widefat', 'wp-list-table', 'submitbox', 'description',
    'updated', 'error', 'success', 'spinner', 'card', 'hidden',
    # États dynamiques JS (ne pas renommer car ajoutés/retirés par JS)
    'active', 'inactive', 'locked', 'unlocked', 'free', 'show',
    'collapsed', 'primary', 'secondary', 'test', 'spin', 'saving', 'saved',
    'warning', 'ul-disc', 'no-preview', 'close-modal', 'category')

# Fichiers à traiter avec leurs chemins réels
$phpFiles = @(
    "plugin\pages\admin-editor.php",
    "plugin\pages\admin-system-check.php",
    "plugin\pages\settings.php"
)

function Prefix-CssBlock {
    param([string]$cssContent, [string[]]$classesToPrefix)
    $result = $cssContent
    foreach ($cls in $classesToPrefix) {
        # Remplacer .classname dans le CSS (en évitant .pdfb-classname déjà préfixé)
        $result = [regex]::Replace($result, "(?<!\w)\.(?!pdfb-)$([regex]::Escape($cls))(?=[^-\w]|$)", ".pdfb-$cls")
    }
    return $result
}

function Get-NonPrefixedClasses {
    param([string]$cssContent)
    $matches = [regex]::Matches($cssContent, '(?<![\w-])\.(?!pdfb-)([a-z][a-z0-9-]+)(?=[^-\w])')
    $classes = @()
    foreach ($m in $matches) {
        $c = $m.Groups[1].Value
        if ($wpClasses -notcontains $c -and $c -notmatch '^(button|form|wp|dashicons|notice|updated|hidden|spinner|card|description)') {
            $classes += $c
        }
    }
    return ($classes | Sort-Object -Unique)
}

function Replace-ClassInHtml {
    param([string]$html, [string]$oldClass, [string]$newClass)
    # Remplacer dans class="...", dans les attributs PHP echo, etc.
    # Pattern: le mot exact $oldClass entouré d'espaces ou de guillemets
    $escaped = [regex]::Escape($oldClass)
    # Dans les attributs class HTML
    $result = [regex]::Replace($html, "(?<=class=[`"'][^`"']*)\b$escaped\b(?=[^`"']*[`"'])", $newClass)
    # Dans les chaînes PHP/JS avec concat de classes
    $result = [regex]::Replace($result, "(?<=['`"])\s*$escaped\s*(?=['`"]|\s)", " $newClass ")
    return $result
}

$totalFilesProcessed = 0
$totalBlocksRemoved = 0
$totalCssAdded = ""

foreach ($relPath in $phpFiles) {
    $fullPath = Join-Path $workspaceRoot $relPath
    $fileName = Split-Path $fullPath -Leaf

    if (-not (Test-Path $fullPath)) {
        Write-Warning "SKIP (introuvable): $fileName"
        continue
    }

    $content = Get-Content $fullPath -Raw -Encoding UTF8
    $styleMatches = [regex]::Matches($content, '(?s)<style>(.*?)</style>')

    if ($styleMatches.Count -eq 0) {
        Write-Host "SKIP (aucun bloc style): $fileName"
        continue
    }

    Write-Host "`n=== Traitement: $fileName ($($styleMatches.Count) bloc(s)) ===" -ForegroundColor Cyan

    $cssToAdd = "`n/* === Extrait de $fileName === */`n"
    $modifiedContent = $content

    foreach ($match in $styleMatches) {
        $rawCss = $match.Groups[1].Value
        $nonPrefixed = Get-NonPrefixedClasses -cssContent $rawCss

        if ($nonPrefixed.Count -gt 0) {
            Write-Host "  Classes à préfixer: $($nonPrefixed -join ', ')" -ForegroundColor Yellow
        }

        # Préfixer les classes dans le CSS
        $prefixedCss = Prefix-CssBlock -cssContent $rawCss -classesToPrefix $nonPrefixed

        $cssToAdd += $prefixedCss + "`n"

        # Mettre à jour les références de classes dans le HTML
        foreach ($cls in $nonPrefixed) {
            $modifiedContent = Replace-ClassInHtml -html $modifiedContent -oldClass $cls -newClass "pdfb-$cls"
            Write-Host "  .$cls -> .pdfb-$cls" -ForegroundColor Green
        }

        # Supprimer le bloc <style>...</style>
        $modifiedContent = $modifiedContent.Replace($match.Value, "")
    }

    # Nettoyage des lignes vides multiples
    $modifiedContent = [regex]::Replace($modifiedContent, '(\r?\n){3,}', "`r`n`r`n")

    # Écrire le fichier PHP modifié
    [System.IO.File]::WriteAllText($fullPath, $modifiedContent, [System.Text.Encoding]::UTF8)
    Write-Host "  ✅ PHP mis à jour: $fileName" -ForegroundColor Green

    $totalCssAdded += $cssToAdd
    $totalFilesProcessed++
    $totalBlocksRemoved += $styleMatches.Count
}

# Ajouter tout le CSS collecté à admin.css
if ($totalCssAdded -ne "") {
    $adminCss = Get-Content $cssFile -Raw -Encoding UTF8
    $newAdminCss = $adminCss.TrimEnd() + "`r`n`r`n" + $totalCssAdded.Trim() + "`r`n"
    [System.IO.File]::WriteAllText($cssFile, $newAdminCss, [System.Text.Encoding]::UTF8)
    Write-Host "`n✅ CSS ajouté à admin.css" -ForegroundColor Green
}

Write-Host "`n=== RÉSUMÉ ===" -ForegroundColor Cyan
Write-Host "Fichiers traités: $totalFilesProcessed"
Write-Host "Blocs <style> supprimés: $totalBlocksRemoved"
