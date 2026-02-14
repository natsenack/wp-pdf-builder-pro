# Script to add pdfb- prefix to all CSS classes in the plugin
# Usage: .\build\add-css-prefix.ps1

$ErrorActionPreference = "Stop"

# Define CSS files to process (excluding already compiled and settings-systeme.css which is done)
$cssFiles = @(
    "src\css\ContextMenu.css",
    "src\css\main.css",
    "src\css\notifications.css",
    "src\css\pdf-builder-admin.css",
    "src\css\pdf-builder-react.min.css",
    "src\css\SaveIndicator.css",
    "src\css\SaveTooltip.css",
    "src\css\settings-tabs.css",
    "src\js\react\styles\editor.css",
    "plugin\templates\admin\css\predefined-templates.css"
)

$prefix = "pdfb-"
$totalClasses = 0
$totalFiles = 0

Write-Host "🔍 Analysing CSS files for class names..." -ForegroundColor Cyan
Write-Host ""

foreach ($file in $cssFiles) {
    $fullPath = Join-Path "I:\wp-pdf-builder-pro-V2" $file
    
    if (-not (Test-Path $fullPath)) {
        Write-Host "⚠️  File not found: $file" -ForegroundColor Yellow
        continue
    }
    
    $content = Get-Content $fullPath -Raw
    
    # Find all CSS class selectors (starting with .)
    # Pattern: match . followed by word characters, hyphens, underscores (but not already prefixed)
    $pattern = '(?<![a-zA-Z0-9-])\.(?!pdfb-)([a-zA-Z][a-zA-Z0-9_-]*)'
    $matches = [regex]::Matches($content, $pattern)
    
    if ($matches.Count -gt 0) {
        $totalFiles++
        $uniqueClasses = $matches | ForEach-Object { $_.Groups[1].Value } | Select-Object -Unique
        $classCount = $uniqueClasses.Count
        $totalClasses += $classCount
        
        Write-Host "📄 $file" -ForegroundColor Green
        Write-Host "   Classes found: $classCount" -ForegroundColor Gray
        
        # Show first 5 classes as example
        $examples = $uniqueClasses | Select-Object -First 5
        foreach ($class in $examples) {
            Write-Host "   - .$class → .pdfb-$class" -ForegroundColor DarkGray
        }
        
        if ($uniqueClasses.Count -gt 5) {
            Write-Host "   ... and $($uniqueClasses.Count - 5) more" -ForegroundColor DarkGray
        }
        Write-Host ""
    }
}

Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
Write-Host "📊 Summary:" -ForegroundColor Yellow
Write-Host "   Files to process: $totalFiles" -ForegroundColor White
Write-Host "   Total unique classes: $totalClasses" -ForegroundColor White
Write-Host ""
Write-Host "⚠️  WARNING: This will also require updating:" -ForegroundColor Yellow
Write-Host "   - All PHP template files" -ForegroundColor Gray
Write-Host "   - All TypeScript/JavaScript React components" -ForegroundColor Gray
Write-Host "   - All inline styles and className attributes" -ForegroundColor Gray
Write-Host ""
Write-Host "Would you like to proceed with automatic prefixing? (Y/N)" -ForegroundColor Cyan
$response = Read-Host

if ($response -eq "Y" -or $response -eq "y") {
    Write-Host ""
    Write-Host "🚀 Starting automatic prefixing..." -ForegroundColor Green
    Write-Host ""
    
    foreach ($file in $cssFiles) {
        $fullPath = Join-Path "I:\wp-pdf-builder-pro-V2" $file
        
        if (-not (Test-Path $fullPath)) {
            continue
        }
        
        $content = Get-Content $fullPath -Raw
        $originalContent = $content
        
        # Add prefix to all class selectors
        # This pattern matches:
        # - . followed by class name (not already prefixed with pdfb-)
        # - Handles multiple selectors (.class1, .class2)
        # - Handles descendant selectors (.parent .child)
        # - Handles pseudo-classes (.class:hover)
        # - Handles attribute selectors (.class[attr])
        
        $pattern = '(?<![a-zA-Z0-9-])\.(?!pdfb-)([a-zA-Z][a-zA-Z0-9_-]*)(?=[\s,:\.\[\{>+~]|$)'
        $content = [regex]::Replace($content, $pattern, ".pdfb-`$1")
        
        if ($content -ne $originalContent) {
            Set-Content -Path $fullPath -Value $content -NoNewline
            Write-Host "✅ Updated: $file" -ForegroundColor Green
        }
    }
    
    Write-Host ""
    Write-Host "✨ CSS files updated successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "⚠️  IMPORTANT NEXT STEPS:" -ForegroundColor Yellow
    Write-Host "1. Run: npm run build" -ForegroundColor White
    Write-Host "2. Update all PHP/JS files that use these classes" -ForegroundColor White
    Write-Host "3. Test thoroughly in the browser" -ForegroundColor White
    
} else {
    Write-Host ""
    Write-Host "❌ Operation cancelled." -ForegroundColor Red
}
