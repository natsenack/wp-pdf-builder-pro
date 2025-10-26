# Script pour uploader manuellement les bundles corrigés
$ftpServer = "65.108.242.181"
$ftpUser = "nats"
$ftpPass = Get-Content "tools/ftp-config.env" | Where-Object { $_ -match "FTP_PASSWORD" } | ForEach-Object { $_.Split('=')[1] }
$remotePath = "/wp-content/plugins/wp-pdf-builder-pro/assets/js/dist"

$files = @(
    "assets/js/dist/pdf-builder-admin-debug.js",
    "assets/js/dist/338.dfcff9c4196dc98cb244.js",
    "assets/js/dist/runtime.5a3a6b88d4542257f277.js"
)

foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Host "📤 Uploading $file..."
        $webclient = New-Object System.Net.WebClient
        $webclient.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
        $remoteFile = "$remotePath/" + (Split-Path $file -Leaf)
        $webclient.UploadFile("ftp://$ftpServer$remoteFile", $file)
        Write-Host "✅ Uploaded $file"
    } else {
        Write-Host "❌ File not found: $file"
    }
}

Write-Host "🎉 Upload terminé!"