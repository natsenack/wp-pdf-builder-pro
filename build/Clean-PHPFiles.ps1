# Clean PHP Files - Remove duplicate content and fix structure

param(
    [string]$Path = "..\plugin\src"
)

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "NETTOYAGE DES FICHIERS PHP"
Write-Host "==========================================" -ForegroundColor Cyan

# Function to clean a PHP file
function Clean-PHPFile {
    param([string]$FilePath)

    try {
        $content = Get-Content $FilePath -Raw

        # Remove BOM if present
        if ($content.StartsWith([char]0xFEFF)) {
            $content = $content.TrimStart([char]0xFEFF)
        }

        $lines = $content -split "`n"
        $cleanLines = @()
        $inPhpTag = $false

        foreach ($line in $lines) {
            # If we encounter <?php and we're already in one, skip
            if ($line -match '^\s*<\?php' -and $inPhpTag) {
                continue
            }

            if ($line -match '^\s*<\?php') {
                $inPhpTag = $true
            }

            $cleanLines += $line
        }

        # Remove duplicate namespace declarations
        $finalLines = @()
        $lastNamespace = ""
        foreach ($line in $cleanLines) {
            if ($line -match '^\s*namespace\s+[^;]+;') {
                if ($line -ne $lastNamespace) {
                    $finalLines += $line
                    $lastNamespace = $line
                }
            } else {
                $finalLines += $line
            }
        }

        # Write back if changed
        $newContent = $finalLines -join "`n"
        if ($newContent -ne $content) {
            Set-Content $FilePath $newContent -Encoding UTF8
            Write-Host "Nettoye: $FilePath" -ForegroundColor Green
            return $true
        }
    }
    catch {
        Write-Host "Erreur: $($_.Exception.Message)" -ForegroundColor Red
    }

    return $false
}

# Find all PHP files
$phpFiles = Get-ChildItem -Path $Path -Recurse -Filter "*.php"

$cleanedCount = 0
foreach ($file in $phpFiles) {
    if (Clean-PHPFile -FilePath $file.FullName) {
        $cleanedCount++
    }
}

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "Fichiers nettoyes: $cleanedCount"
Write-Host "==========================================" -ForegroundColor Cyan