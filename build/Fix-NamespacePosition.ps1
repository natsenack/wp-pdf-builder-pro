# Fix PHP Namespace Declaration Position

param(
    [string]$Path = "..\plugin\src"
)

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "CORRECTION POSITION NAMESPACE PHP"
Write-Host "==========================================" -ForegroundColor Cyan

# Function to fix namespace position in PHP file
function Fix-NamespacePosition {
    param([string]$FilePath)

    try {
        $content = Get-Content $FilePath -Raw

        # Skip if no namespace
        if ($content -notmatch 'namespace\s+[^;]+;') {
            return $false
        }

        # Extract namespace declaration
        $namespaceMatch = [regex]::Match($content, 'namespace\s+[^;]+;')
        $namespaceDecl = $namespaceMatch.Value

        # Create new content starting with <?php namespace...
        $newContent = "<?php $namespaceDecl"

        # Add the rest of the content after the namespace
        $afterNamespace = $content.Substring($namespaceMatch.Index + $namespaceMatch.Length)

        # Remove any leading whitespace/newlines after namespace
        $afterNamespace = $afterNamespace -replace '^\s*', ''

        $newContent += "`n$afterNamespace"

        # Write back
        Set-Content $FilePath $newContent -Encoding UTF8
        Write-Host "Corrige position namespace: $FilePath" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "Erreur: $($_.Exception.Message)" -ForegroundColor Red
    }

    return $false
}

# Find all PHP files with syntax errors
$phpFiles = Get-ChildItem -Path $Path -Recurse -Filter "*.php" | Where-Object {
    $filePath = $_.FullName
    try {
        $result = & php -l $filePath 2>&1
        $result -match "Fatal error|Parse error"
    } catch {
        $true
    }
}

Write-Host "Fichiers avec erreurs: $($phpFiles.Count)" -ForegroundColor Yellow

$fixedCount = 0
foreach ($file in $phpFiles) {
    if (Fix-NamespacePosition -FilePath $file.FullName) {
        $fixedCount++
    }
}

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "Fichiers corriges: $fixedCount"
Write-Host "==========================================" -ForegroundColor Cyan