@echo off
REM Script batch pour déploiement FTP simple de PDF Builder Pro
REM Lance le script PowerShell avec les bons paramètres

echo ========================================
echo DEPLOIEMENT FTP SIMPLE - PDF Builder Pro
echo ========================================
echo.

REM Vérifier si PowerShell est disponible
powershell -Command "Write-Host 'PowerShell OK'" >nul 2>&1
if errorlevel 1 (
    echo ERREUR: PowerShell n'est pas disponible sur ce système
    pause
    exit /b 1
)

REM Vérifier si le script PowerShell existe
if not exist "deploy-ftp-simple.ps1" (
    echo ERREUR: Le script deploy-ftp-simple.ps1 n'est pas trouvé
    echo Assurez-vous d'être dans le dossier build/
    pause
    exit /b 1
)

echo Démarrage du déploiement...
echo.

REM Exécuter le script PowerShell
powershell -ExecutionPolicy Bypass -File "deploy-ftp-simple.ps1" %*

echo.
echo ========================================
pause
