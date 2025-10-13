@echo off
echo ========================================
echo DEPLOIEMENT PDF BUILDER PRO DEBUG
echo ========================================
echo.

REM Configuration FTP - MODIFIEZ CES VALEURS !
set FTP_HOST=votre-serveur-ftp.com
set FTP_USER=votre-utilisateur-ftp
set FTP_PASS=votre-mot-de-passe
set REMOTE_PLUGIN_PATH=/wp-content/plugins/pdf-builder-pro

echo Création des commandes FTP...
echo open %FTP_HOST% > deploy_pdf_debug.txt
echo %FTP_USER% >> deploy_pdf_debug.txt
echo %FTP_PASS% >> deploy_pdf_debug.txt
echo cd %REMOTE_PLUGIN_PATH% >> deploy_pdf_debug.txt

echo Upload du fichier modifié...
echo put "G:\wp-pdf-builder-pro\includes\classes\class-pdf-builder-admin.php" >> deploy_pdf_debug.txt
echo put "G:\wp-pdf-builder-pro\debug_pdf_preview.php" >> deploy_pdf_debug.txt

echo quit >> deploy_pdf_debug.txt

echo.
echo Exécution du transfert FTP...
ftp -s:deploy_pdf_debug.txt

echo.
echo Nettoyage...
del deploy_pdf_debug.txt

echo.
echo ========================================
echo DEPLOIEMENT TERMINÉ !
echo ========================================
echo.
echo Fichiers uploadés :
echo - class-pdf-builder-admin.php (avec logs détaillés)
echo - debug_pdf_preview.php (script de diagnostic)
echo.
echo Testez maintenant :
echo 1. Bouton "👁️ Aperçu PDF" dans une commande WooCommerce
echo 2. Console du navigateur (F12)
echo 3. wp-content/plugins/pdf-builder-pro/debug_pdf_preview.php
echo 4. Logs PHP dans wp-content/debug.log
echo.
pause