$files = Get-ChildItem "i:\wp-pdf-builder-pro\plugin\resources\assets\js\*.js" -Exclude "dist/*"

foreach ($file in $files) {
    if ($file.Name -notlike "*dist*") {
        $content = Get-Content $file.FullName -Raw
        
        # Remove console.log calls (but keep console.error and console.warn if needed)
        $content = $content -replace 'console\.log\([^)]+\);?', ''
        
        # Remove debugLog calls
        $content = $content -replace 'debugLog\([^)]+\);?', ''
        
        # Remove debugError calls  
        $content = $content -replace 'debugError\([^)]+\);?', ''
        
        # Remove debugWarn calls
        $content = $content -replace 'debugWarn\([^)]+\);?', ''
        
        # Remove isDebugEnabled function
        $content = $content -replace 'function isDebugEnabled\(\) \{[^}]*\}', ''
        
        # Remove debugLog function definition
        $content = $content -replace 'function debugLog\([^}]*\}', ''
        
        # Remove debugError function definition
        $content = $content -replace 'function debugError\([^}]*\}', ''
        
        # Remove debugWarn function definition
        $content = $content -replace 'function debugWarn\([^}]*\}', ''
        
        # Clean up extra blank lines
        $content = $content -replace '\n\s*\n\s*\n', "`n`n"
        
        Set-Content $file.FullName $content
        Write-Host "Processed: $($file.Name)"
    }
}
