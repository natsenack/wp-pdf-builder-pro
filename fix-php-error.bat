@echo off
echo ========================================
echo CORRECTION ERREUR PHP PARSE
echo ========================================
echo.
echo Etape 1: Verification syntaxe locale
php -l "plugin\src\Core\PDF_Builder_Unified_Ajax_Handler.php"
if %errorlevel% neq 0 (
    echo ❌ ERREUR: Syntaxe PHP invalide localement
    pause
    exit /b 1
)
echo ✅ Syntaxe PHP valide localement
echo.
echo Etape 2: Redemarrage des services (si admin)
echo Tentative de redemarrage des services PHP...
net stop "php-cgi" 2>nul
net start "php-cgi" 2>nul
net stop "php" 2>nul
net start "php" 2>nul
echo.
echo Etape 3: Instructions pour le serveur
echo.
echo SUR VOTRE SERVEUR, executez ces commandes :
echo.
echo # Pour Apache + PHP-FPM:
echo sudo systemctl restart php8.1-fpm
echo sudo systemctl restart apache2
echo.
echo # Pour Nginx + PHP-FPM:
echo sudo systemctl restart php8.1-fpm
echo sudo systemctl restart nginx
echo.
echo # Pour vider OPcache manuellement:
echo sudo php -r "opcache_reset(); echo 'OPcache vide\n';"
echo.
echo # Alternative - redemarrer le serveur entier:
echo sudo reboot
echo.
pause