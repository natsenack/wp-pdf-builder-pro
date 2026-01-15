# OBSOLETE: This script is replaced by Webpack bundling
# Webpack now handles React bundling and outputs to plugin/assets/js/pdf-builder-react-wrapper.min.js
# Simple PDF Builder React bundle generator
# Copies the built React file to the dist directory

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "PDF Builder React - Bundle Copy" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

$sourceFile = "assets/js/pdf-builder-react.js"
$outputDir = "plugin/resources/assets/js/dist"
$outputFile = "$outputDir/pdf-builder-react.js"

# Create output directory
if (-not (Test-Path $outputDir)) {
    New-Item -ItemType Directory -Path $outputDir -Force | Out-Null
    Write-Host "✓ Created output directory: $outputDir`n" -ForegroundColor Green
}

# Copy the file
if (Test-Path $sourceFile) {
    $content = Get-Content $sourceFile -Raw
    
    # Verify it's a valid JavaScript file
    if ($content.Length -gt 100) {
        Copy-Item -Path $sourceFile -Destination $outputFile -Force
        $fileSize = (Get-Item $outputFile).Length / 1KB
        
        Write-Host "✓ Bundle copied successfully" -ForegroundColor Green
        Write-Host "  Source: $sourceFile" -ForegroundColor Gray
        Write-Host "  Output: $outputFile" -ForegroundColor Gray
        Write-Host "  Size: $($fileSize.ToString('F2')) KB`n" -ForegroundColor Gray
        
        # Quick validation
        if ($content -match 'export.*from|export\s+default') {
            Write-Host "⚠ Warning: Source contains ES6 exports" -ForegroundColor Yellow
            Write-Host "  Make sure this file is properly transpiled before deployment`n" -ForegroundColor Yellow
        }
    } else {
        Write-Host "❌ Source file is empty or invalid" -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "❌ Source file not found: $sourceFile" -ForegroundColor Red
    exit 1
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Done!" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Cyan
