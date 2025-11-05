@echo off
REM ═══════════════════════════════════════════════════════════════
REM  Script d'aide - Génération des aperçus après édition
REM  Usage: edit-template.bat <template-name>
REM  Exemple: edit-template.bat corporate
REM ═══════════════════════════════════════════════════════════════

setlocal enabledelayedexpansion

if "%1"=="" (
    echo 📋 Utilisation: edit-template.bat ^<template^>
    echo.
    echo Templates disponibles:
    echo  - corporate
    echo  - classic
    echo  - minimal
    echo  - modern
    echo.
    exit /b 1
)

set TEMPLATE=%1
set PLUGIN_PATH=%CD%\plugin
set BUILTIN_PATH=%PLUGIN_PATH%\templates\builtin

echo 🚀 PROCESSUS D'ÉDITION DE TEMPLATE
echo ═══════════════════════════════════════════════════════════════
echo.
echo 📄 Template: %TEMPLATE%
echo 📂 Chemin: %BUILTIN_PATH%\%TEMPLATE%.json
echo.

REM 1. Vérifier que le fichier existe
if not exist "%BUILTIN_PATH%\%TEMPLATE%.json" (
    echo ❌ Erreur: Template %TEMPLATE%.json non trouvé
    exit /b 1
)

echo 1️⃣  FICHIERS À ÉDITER:
echo    📝 %BUILTIN_PATH%\%TEMPLATE%.json
echo.

echo 2️⃣  GUIDE DE RÉFÉRENCE:
echo    📋 Voir: TEMPLATES_WORK_GUIDE.md
echo.

echo 3️⃣  APERÇU ACTUEL:
echo    🖼️  %PLUGIN_PATH%\assets\images\templates\%TEMPLATE%-preview.svg
echo.

echo ═══════════════════════════════════════════════════════════════
echo.
echo 📝 ÉTAPES:
echo.
echo   1. Ouvrir et éditer: %BUILTIN_PATH%\%TEMPLATE%.json
echo   2. Sauvegarder le fichier
echo   3. Appuyer sur Entrée pour régénérer l'aperçu
echo.
echo ═══════════════════════════════════════════════════════════════
echo.

pause

REM 2. Régénérer l'aperçu
echo 🔄 Régénération de l'aperçu...
echo.
cd /d %PLUGIN_PATH%
php generate-svg-preview.php %TEMPLATE%

if errorlevel 1 (
    echo ❌ Erreur lors de la génération
    pause
    exit /b 1
)

echo.
echo ✅ Aperçu régénéré avec succès!
echo.
echo 📊 Vérification visuelle:
echo    Comparer l'ancien et nouvel aperçu dans:
echo    %PLUGIN_PATH%\assets\images\templates\%TEMPLATE%-preview.svg
echo.

echo 🚀 Pour déployer:
echo    Exécuter: cd build ^&^& .\deploy-simple.ps1
echo.

pause
