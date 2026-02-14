# Script pour préfixer les classes CSS dans templates-page.php
$file = "I:\wp-pdf-builder-pro-V2\plugin\templates\admin\templates-page.php"
$content = Get-Content $file -Raw

Write-Host "`n=== PRÉFIXAGE DES CLASSES TEMPLATES-PAGE.PHP ===" -ForegroundColor Cyan

# Classes à préfixer
$patterns = @(
    @{ Old = 'class="filter-btn'; New = 'class="pdfb-filter-btn' },
    @{ Old = 'class="template-card '; New = 'class="pdfb-template-card ' },
    @{ Old = 'class="template-type-badge'; New = 'class="pdfb-template-type-badge' },
    @{ Old = 'class="template-gallery-modal'; New = 'class="pdfb-template-gallery-modal' },
    @{ Old = 'class="template-modal-content'; New = 'class="pdfb-template-modal-content' },
    @{ Old = 'class="template-modal-header'; New = 'class="pdfb-template-modal-header' },
    @{ Old = 'class="template-modal-body'; New = 'class="pdfb-template-modal-body' },
    @{ Old = 'class="template-modal-footer'; New = 'class="pdfb-template-modal-footer' },
    @{ Old = 'class="template-modal-overlay'; New = 'class="pdfb-template-modal-overlay' },
    @{ Old = 'class="gallery-filter-btn'; New = 'class="pdfb-gallery-filter-btn' },
    @{ Old = 'class="modal-overlay'; New = 'class="pdfb-modal-overlay' },
    @{ Old = 'class="modal-content'; New = 'class="pdfb-modal-content' },
    @{ Old = 'class="modal-header'; New = 'class="pdfb-modal-header' },
    @{ Old = 'class="modal-close'; New = 'class="pdfb-modal-close' },
    @{ Old = 'class="modal-body'; New = 'class="pdfb-modal-body' },
    @{ Old = 'class="template-settings-footer'; New = 'class="pdfb-template-settings-footer' },
    @{ Old = 'class="template-settings-icon'; New = 'class="pdfb-template-settings-icon' }
)

$count = 0
foreach ($pattern in $patterns) {
    $old = $pattern.Old
    $new = $pattern.New
    
    $beforeCount = ([regex]::Matches($content, [regex]::Escape($old))).Count
    if ($beforeCount -gt 0) {
        $content = $content.Replace($old, $new)
        $count += $beforeCount
        Write-Host "  ✅ $old → $new ($beforeCount occurrences)" -ForegroundColor Green
    }
}

Set-Content $file -Value $content -NoNewline -Encoding UTF8
Write-Host "`n✨ Terminé! $count classes préfixées" -ForegroundColor Cyan
