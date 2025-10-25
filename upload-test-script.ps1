# Script temporaire pour uploader adaptive-layout-test.js
$ftpHost = "65.108.242.181"
$ftpUser = "nats"
$ftpConfig = Get-Content "tools\ftp-config.env"
$ftpPass = ($ftpConfig | Where-Object { $_ -match "FTP_PASSWORD=(.+)" } | ForEach-Object { $matches[1] })

$webclient = New-Object System.Net.WebClient
$webclient.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)

Write-Host "Uploading adaptive-layout-test.js..."
$webclient.UploadFile("ftp://$ftpHost/wp-content/plugins/wp-pdf-builder-pro/adaptive-layout-test.js", "adaptive-layout-test.js")
Write-Host "✅ Fichier déployé avec succès"