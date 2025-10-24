# Quick FTP Upload Script
$ftpServer = "65.108.242.181"
$ftpUser = "nats"
$ftpPass = "Zrp7mCjn@2024"
$ftpPath = "/wp-content/plugins/wp-pdf-builder-pro/"

$filesToUpload = @(
    "assets/js/dist/pdf-builder-admin.js",
    "assets/js/dist/pdf-builder-admin.js.gz",
    "resources/js/components/ElementLibrary.jsx",
    "resources/js/components/PropertiesPanel.jsx",
    "resources/js/hooks/useCanvasState.js"
)

$ftp = [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.SecurityProtocolType]::Tls12

foreach ($file in $filesToUpload) {
    try {
        $ftpUri = "ftp://$ftpServer$ftpPath$($file.Replace('\', '/'))"
        $request = [System.Net.FtpWebRequest]::Create($ftpUri)
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        
        $fileStream = [System.IO.File]::OpenRead($file)
        $uploadStream = $request.GetRequestStream()
        $fileStream.CopyTo($uploadStream)
        $uploadStream.Close()
        $fileStream.Close()
        
        $response = $request.GetResponse()
        Write-Host "✅ $file"
        $response.Close()
    } catch {
        Write-Host "❌ $file - $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "`n✨ Upload complete!"
