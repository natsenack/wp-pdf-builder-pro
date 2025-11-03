$ftpHost = "65.108.242.181"
$ftpUser = "nats"
$ftpPass = "iZ6vU3zV2y"
$ftpPath = "/wp-content/plugins/wp-pdf-builder-pro/assets/js/dist/"
$localPath = "D:\wp-pdf-builder-pro\plugin\assets\js\dist\"

$files = @("pdf-preview-api-client.js", "pdf-preview-integration.js")

foreach ($file in $files) {
    $localFile = $localPath + $file
    $uri = "ftp://$ftpUser`:$ftpPass@$ftpHost$ftpPath$file"
    
    Write-Host "Uploading $file..."
    
    $ftpRequest = [System.Net.FtpWebRequest]::Create($uri)
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $ftpRequest.UseBinary = $true
    
    $fileStream = [System.IO.File]::OpenRead($localFile)
    $requestStream = $ftpRequest.GetRequestStream()
    $fileStream.CopyTo($requestStream)
    $requestStream.Close()
    $fileStream.Close()
    
    $response = $ftpRequest.GetResponse()
    Write-Host "✅ $file uploaded successfully"
    $response.Close()
}

Write-Host "`n✅ All files uploaded!"
