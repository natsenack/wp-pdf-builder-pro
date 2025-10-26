# Test FTP Connection - PDF Builder Pro
# Teste si la connexion FTP fonctionne vraiment

$FtpHost = "65.108.242.181"
$FtpUser = "ftp"
$FtpPass = "t-3=,DGq%Z8("
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

Write-Host "🔍 TEST DE CONNEXION FTP" -ForegroundColor Cyan
Write-Host "=" * 50

# 1. Test basique: connexion FTP
Write-Host "`n1️⃣  Test de connexion basique..." -ForegroundColor Yellow

$testScript = @"
open $FtpHost
$FtpUser
$FtpPass
pwd
bye
"@

$testScriptPath = "ftp-test-basic-temp.txt"
$testScript | Out-File -FilePath $testScriptPath -Encoding ASCII -Force

Write-Host "   Commandes envoyées:" -ForegroundColor Gray
Write-Host "   - open $FtpHost" -ForegroundColor Gray
Write-Host "   - Authentification" -ForegroundColor Gray
Write-Host "   - pwd (afficher répertoire courant)" -ForegroundColor Gray

$testResult = & ftp -i -n -s:$testScriptPath 2>&1

Write-Host "`n📋 Résultat:" -ForegroundColor Cyan
$testResult | ForEach-Object { Write-Host "   $_" -ForegroundColor White }

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅ Connexion FTP réussie!" -ForegroundColor Green
} else {
    Write-Host "`n❌ Échec de connexion FTP (code: $LASTEXITCODE)" -ForegroundColor Red
}

Remove-Item $testScriptPath -ErrorAction SilentlyContinue

# 2. Test: Changer de répertoire et lister le contenu
Write-Host "`n2️⃣  Test de navigation au dossier plugin..." -ForegroundColor Yellow

$navScript = @"
open $FtpHost
$FtpUser
$FtpPass
cd $FtpPath
pwd
ls
bye
"@

$navScriptPath = "ftp-test-nav-temp.txt"
$navScript | Out-File -FilePath $navScriptPath -Encoding ASCII -Force

$navResult = & ftp -i -n -s:$navScriptPath 2>&1

Write-Host "`n📋 Résultat:" -ForegroundColor Cyan
$navResult | ForEach-Object { Write-Host "   $_" -ForegroundColor White }

Remove-Item $navScriptPath -ErrorAction SilentlyContinue

# 3. Test: Créer un fichier test
Write-Host "`n3️⃣  Test d'upload d'un fichier test..." -ForegroundColor Yellow

$testFile = "ftp-test-file-temp.txt"
"Test de déploiement FTP - $(Get-Date)" | Out-File -FilePath $testFile -Encoding ASCII -Force

$uploadScript = @"
open $FtpHost
$FtpUser
$FtpPass
cd $FtpPath
put $testFile test-ftp-file.txt
ls
bye
"@

$uploadScriptPath = "ftp-test-upload-temp.txt"
$uploadScript | Out-File -FilePath $uploadScriptPath -Encoding ASCII -Force

$uploadResult = & ftp -i -n -s:$uploadScriptPath 2>&1

Write-Host "`n📋 Résultat:" -ForegroundColor Cyan
$uploadResult | ForEach-Object { Write-Host "   $_" -ForegroundColor White }

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅ Upload réussi!" -ForegroundColor Green
} else {
    Write-Host "`n❌ Échec d'upload (code: $LASTEXITCODE)" -ForegroundColor Red
}

Remove-Item $testFile -ErrorAction SilentlyContinue
Remove-Item $uploadScriptPath -ErrorAction SilentlyContinue

# 4. Test: Utiliser PowerShell FtpWebRequest (plus fiable)
Write-Host "`n4️⃣  Test avec PowerShell FtpWebRequest (alternative plus fiable)..." -ForegroundColor Yellow

try {
    $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$FtpPath")
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectoryDetails
    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
    $ftpRequest.UseBinary = $true
    $ftpRequest.UsePassive = $true
    
    $response = $ftpRequest.GetResponse()
    $responseStream = $response.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($responseStream)
    $content = $reader.ReadToEnd()
    
    Write-Host "`n📋 Contenu du dossier distantls:" -ForegroundColor Cyan
    $content.Split("`n") | ForEach-Object {
        if ($_ -match '\S') { Write-Host "   $_" -ForegroundColor White }
    }
    
    Write-Host "`n✅ FtpWebRequest fonctionne!" -ForegroundColor Green
} catch {
    Write-Host "`n❌ Erreur FtpWebRequest: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n" + "=" * 50
Write-Host "Test terminé!" -ForegroundColor Cyan
