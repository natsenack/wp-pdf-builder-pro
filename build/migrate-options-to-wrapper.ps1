# Script pour migrer tous les appels get_option/update_option vers les fonctions wrapper pdf_builder_*
# Ce script remplace tous les appels pdf_builder_get_option() et pdf_builder_update_option()

$pluginPath = "i:\wp-pdf-builder-pro-V2\plugin"
$replacements = 0
$skipped = 0

# Fichiers à ignorer (déjà migrés manuellement)
$migratedFiles = @(
    "settings-templates.php",
    "templates-page.php"
)

# Chercher tous les fichiers PHP
Get-ChildItem -Path "$pluginPath" -Include "*.php" -Recurse | ForEach-Object {
    $fileName = $_.Name
    
    # Sauter les fichiers déjà migrés
    if ($migratedFiles -contains $fileName) {
        Write-Host "⏭️  Skipping already migrated: $fileName" -ForegroundColor Yellow
        return
    }
    
    $filePath = $_.FullName
    $content = Get-Content -Path $filePath -Raw -Encoding UTF8
    $originalContent = $content
    
    # Remplacer get_option('pdf_builder_ par pdf_builder_get_option('pdf_builder_
    # Attention : ne pas remplacer si c'est déjà pdf_builder_get_option
    if ($content -match "(?<!pdf_builder_)get_option\('pdf_builder_") {
        $content = $content -replace "(?<!pdf_builder_)get_option\('pdf_builder_", "pdf_builder_get_option('pdf_builder_"
        Write-Host "✅ Migrated get_option in: $fileName" -ForegroundColor Green
        $replacements++
    }
    
    # Remplacer update_option('pdf_builder_ par pdf_builder_update_option('pdf_builder_
    if ($content -match "(?<!pdf_builder_)update_option\('pdf_builder_") {
        $content = $content -replace "(?<!pdf_builder_)update_option\('pdf_builder_", "pdf_builder_update_option('pdf_builder_"
        Write-Host "✅ Migrated update_option in: $fileName" -ForegroundColor Green
        $replacements++
    }
    
    # Sauvegarder si des changements ont été faits
    if ($content -ne $originalContent) {
        Set-Content -Path $filePath -Value $content -Encoding UTF8
        Write-Host "💾 Saved: $fileName" -ForegroundColor Cyan
    }
}

Write-Host "`n" -ForegroundColor Gray
Write-Host "✅ Migration complète !" -ForegroundColor Green
Write-Host "📊 Fichiers modifiés: $replacements" -ForegroundColor Green
