$ftpServer = "65.108.242.181"
$ftpUser = "nats"
$ftpPass = "iZ6vU3zV2y"
$remotePath = "/wp-content/plugins/wp-pdf-builder-pro/assets/js/dist"
$projectRoot = "d:\wp-pdf-builder-pro"

$files = @(
    "assets/js/dist/pdf-builder-admin.js",
    "assets/js/dist/pdf-builder-admin-debug.js"
)

foreach ($file in $files) {
    $fullPath = Join-Path $projectRoot $file
    if (Test-Path $fullPath) {
        Write-Host "📤 Uploading $file..."
        $webclient = New-Object System.Net.WebClient
        $webclient.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
        $remoteFile = "$remotePath/" + (Split-Path $file -Leaf)
        $uri = "ftp://$ftpServer$remoteFile"
        
        try {
            $webclient.UploadFile($uri, $fullPath)
            Write-Host "✅ Uploaded $(Split-Path $file -Leaf)" -ForegroundColor Green
        } catch {
            Write-Host "❌ Error uploading $file : $_" -ForegroundColor Red
        }
    } else {
        Write-Host "❌ File not found: $fullPath" -ForegroundColor Red
    }
}

Write-Host "🎉 Upload terminé!" -ForegroundColor Green
