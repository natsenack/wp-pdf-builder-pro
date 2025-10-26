# Test FTP avec mot de passe correctement formé

$FtpHost = "65.108.242.181"
$FtpUser = "ftp"
$FtpPass = 't-3=,DGq%Z8('
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

Write-Host "🔍 TEST FTP - Avec correction du mot de passe" -ForegroundColor Cyan
Write-Host "=" * 50

# Test avec USER et PASS en deux lignes séparées (format FTP correct)
Write-Host "`n1️⃣  Test format FTP correct (USER, puis PASS séparément)..." -ForegroundColor Yellow

$testScript = @"
open $FtpHost
USER $FtpUser
PASS $FtpPass
pwd
bye
"@

$testScriptPath = "ftp-test-correct-temp.txt"
$testScript | Out-File -FilePath $testScriptPath -Encoding ASCII -Force

Write-Host "   Fichier FTP créé avec contenu:" -ForegroundColor Gray
$testScript | ForEach-Object { Write-Host "   > $_" -ForegroundColor Gray }

$testResult = & ftp -i -n -s:$testScriptPath 2>&1

Write-Host "`n📋 Résultat:" -ForegroundColor Cyan
$testResult | ForEach-Object { Write-Host "   $_" -ForegroundColor White }

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅ Authentification réussie!" -ForegroundColor Green
} else {
    Write-Host "`n❌ Échec (code: $LASTEXITCODE)" -ForegroundColor Red
}

Remove-Item $testScriptPath -ErrorAction SilentlyContinue

# Test 2: Lister le contenu du dossier
Write-Host "`n2️⃣  Test: Lister les fichiers du dossier plugin..." -ForegroundColor Yellow

$listScript = @"
open $FtpHost
USER $FtpUser
PASS $FtpPass
cd $FtpPath
ls
bye
"@

$listScriptPath = "ftp-test-list-temp.txt"
$listScript | Out-File -FilePath $listScriptPath -Encoding ASCII -Force

$listResult = & ftp -i -n -s:$listScriptPath 2>&1

Write-Host "`n📋 Contenu du dossier plugin:" -ForegroundColor Cyan
$listResult | ForEach-Object { Write-Host "   $_" -ForegroundColor White }

Remove-Item $listScriptPath -ErrorAction SilentlyContinue

Write-Host "`n" + "=" * 50
