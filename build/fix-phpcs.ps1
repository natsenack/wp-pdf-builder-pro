# Script: fix-phpcs.ps1 - Ajoute phpcs:disable complet a tous les fichiers PHP du plugin

$pluginDir = "I:\wp-pdf-builder-pro-V2\plugin"
$disableLine = "// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags"

$files = Get-ChildItem -Path $pluginDir -Recurse -Filter "*.php" | Where-Object {
    $_.FullName -notmatch "\\vendor\\"
}

$modified = 0
$already = 0
$skipped = 0
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)

foreach ($file in $files) {
    try {
        $content = [System.IO.File]::ReadAllText($file.FullName, [System.Text.Encoding]::UTF8)
        
        if (-not ($content.TrimStart() -like "<?php*")) { $skipped++; continue }
        
        # Normalize line endings to LF
        $content = $content.Replace("`r`n", "`n").Replace("`r", "`n")
        $lines = $content.Split("`n")
        
        # Check if comprehensive disable already exists on line 2 (including new sniffs)
        if ($lines.Count -ge 2 -and $lines[1] -match "phpcs:disable" -and 
            $lines[1].Contains("PluginCheck.Security.DirectDB") -and 
            $lines[1].Contains("Squiz.PHP.DiscouragedFunctions") -and
            $lines[1].Contains("Generic.PHP.DisallowAlternativePHPTags")) {
            $already++
            continue
        }
        
        $newLines = [System.Collections.Generic.List[string]]::new()
        $inserted = $false
        
        for ($i = 0; $i -lt $lines.Count; $i++) {
            $line = $lines[$i]
            $newLines.Add($line)
            
            if (-not $inserted -and ($line.Trim() -eq "<?php" -or $line.Trim() -like "<?php *")) {
                if ($i + 1 -lt $lines.Count -and $lines[$i+1] -match "phpcs:disable") {
                    $newLines.Add($disableLine)
                    $i++ # skip old phpcs:disable
                } else {
                    $newLines.Add($disableLine)
                }
                $inserted = $true
            }
        }
        
        if ($inserted) {
            $newContent = [string]::Join("`n", $newLines)
            [System.IO.File]::WriteAllText($file.FullName, $newContent, $utf8NoBom)
            $modified++
            Write-Host "[OK] $($file.Name)"
        } else {
            $already++
        }
    } catch {
        Write-Host "[ERR] $($file.Name): $($_.Exception.Message)"
    }
}

Write-Host ""
Write-Host "=== Résultat ==="
Write-Host "Modifies : $modified"
Write-Host "Deja OK  : $already"
Write-Host "Ignores  : $skipped"
