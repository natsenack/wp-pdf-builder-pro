@echo off
echo Déploiement des fichiers de débogage PDF Preview...
echo.

REM Configuration FTP
set FTP_HOST=votre-serveur-ftp.com
set FTP_USER=votre-utilisateur-ftp
set FTP_PASS=votre-mot-de-passe

echo Création du fichier de commandes FTP...
echo open %FTP_HOST% > ftp_commands.txt
echo %FTP_USER% >> ftp_commands.txt
echo %FTP_PASS% >> ftp_commands.txt
echo cd /public_html/wp-content/plugins >> ftp_commands.txt
echo mkdir woo-pdf-invoice-builder-debug >> ftp_commands.txt
echo cd woo-pdf-invoice-builder-debug >> ftp_commands.txt
echo put "G:\wp-pdf-builder-pro\debug_instructions.txt" >> ftp_commands.txt
echo put "G:\wp-pdf-builder-pro\test_pdf_preview.php" >> ftp_commands.txt
echo quit >> ftp_commands.txt

echo Exécution du transfert FTP...
ftp -s:ftp_commands.txt

echo Nettoyage...
del ftp_commands.txt

echo.
echo Fichiers déployés. Instructions :
echo 1. Accédez à votre site WordPress
echo 2. Allez dans wp-content/plugins/woo-pdf-invoice-builder-debug/
echo 3. Lisez debug_instructions.txt pour les étapes de diagnostic
echo 4. Exécutez test_pdf_preview.php pour tester la génération d'URL
echo.
pause