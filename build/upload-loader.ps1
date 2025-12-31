# Upload PDF Builder React Loader to FTP
$url = 'ftp://threeaxe.fr/wp-content/plugins/wp-pdf-builder-pro/assets/js/pdf-builder-react-loader.js'
$file = 'i:\wp-pdf-builder-pro\plugin\assets\js\pdf-builder-react-loader.js'
$user = 'threeaxedev'
$pass = 'threeaxedev@2024'

Write-Host "Uploading $file to $url..."

$ftpReq = [System.Net.FtpWebRequest]::Create($url)
$ftpReq.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
$ftpReq.Credentials = New-Object System.Net.NetworkCredential($user, $pass)
$ftpReq.UseBinary = $true
$ftpReq.UsePassive = $true

$fileContent = [System.IO.File]::ReadAllBytes($file)
$ftpReq.ContentLength = $fileContent.Length

$reqStream = $ftpReq.GetRequestStream()
$reqStream.Write($fileContent, 0, $fileContent.Length)
$reqStream.Close()

$response = $ftpReq.GetResponse()
Write-Host "Upload Response: $($response.StatusDescription)"
$response.Close()
