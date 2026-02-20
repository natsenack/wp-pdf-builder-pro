$file = "i:\wp-pdf-builder-pro-V2\src\css\pdf-builder-react.min.css"
$content = Get-Content $file -Raw

$replacements = @{
    '\.demo-element\.' = '.pdfb-demo-element.'
    '\.corner-indicator\.' = '.pdfb-corner-indicator.'
    '\.grid-line\.' = '.pdfb-grid-line.'
    '\.guide-line\.' = '.pdfb-guide-line.'
    '\.mini-element\.' = '.pdfb-mini-element.'
    '\.mini-handle\.' = '.pdfb-mini-handle.'
    '\.mode-icon\.' = '.pdfb-mode-icon.'
    '\.feature-tag\.' = '.pdfb-feature-tag.'
    '\.template-preview \.' = '.pdfb-template-preview .'
}

foreach ($pattern in $replacements.Keys) {
    $content = $content -replace $pattern, $replacements[$pattern]
}

Set-Content $file -Value $content -Encoding UTF8 -NoNewline
Write-Host "✅ Done" -ForegroundColor Green

# Verify no non-prefixed classes remain
$remaining = Select-String -InputObject $content -Pattern "^\.[^p][a-z0-9]" -AllMatches
if ($remaining) {
    Write-Host "⚠️  Still found non-prefixed:" -ForegroundColor Yellow
    $remaining.Matches | ForEach-Object { Write-Host $_.Value }
} else {
    Write-Host "✅ No non-prefixed classes remaining!" -ForegroundColor Green
}
