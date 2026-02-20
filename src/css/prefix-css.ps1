$content = Get-Content "pdf-builder-react.min.css" -Raw
$replacements = @{
    '.preview-format' = '.pdfb-preview-format'
    '.preview-size' = '.pdfb-preview-size'
    '.progress-label' = '.pdfb-progress-label'
    '.progress-bar' = '.pdfb-progress-bar'
    '.progress-fill' = '.pdfb-progress-fill'
    '.progress-value' = '.pdfb-progress-value'
    '.performance-preview-container' = '.pdfb-performance-preview-container'
    '.performance-metrics' = '.pdfb-performance-metrics'
    '.metric-item' = '.pdfb-metric-item'
    '.metric-label' = '.pdfb-metric-label'
    '.metric-value' = '.pdfb-metric-value'
    '.metric-unit' = '.pdfb-metric-unit'
    '.performance-status' = '.pdfb-performance-status'
    '.templates-status-grid' = '.pdfb-templates-status-grid'
    '.template-status-card' = '.pdfb-template-status-card'
    '.premium-badge' = '.pdfb-premium-badge'
    '.custom-status-indicator' = '.pdfb-custom-status-indicator'
    '.template-selector' = '.pdfb-template-selector'
    '.template-select' = '.pdfb-template-select'
    '.template-preview' = '.pdfb-template-preview'
}

foreach ($key in $replacements.Keys) {
    $content = $content -replace [regex]::Escape($key), $replacements[$key]
}

Set-Content "pdf-builder-react.min.css" -Value $content -Encoding UTF8
Write-Host "✅ All CSS classes prefixed with pdfb-" -ForegroundColor Green
