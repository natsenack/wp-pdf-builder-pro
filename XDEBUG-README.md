# Configuration Xdebug pour VS Code

## ⚠️ IMPORTANT : Installation manuelle requise

PHP 8.4.12 est très récent et n'a pas encore de version stable de Xdebug compatible. Vous devez installer Xdebug manuellement.

## Installation de Xdebug

### Pour Windows avec PHP 8.4:

1. **Allez sur https://xdebug.org/download**
2. **Trouvez la version appropriée pour PHP 8.4 :**
   - Cherchez "PHP 8.4 TS VS17 (64 bit)" ou similaire
   - Si aucune version 8.4 n'est disponible, essayez PHP 8.3
3. **Téléchargez la DLL** (.dll file)
4. **Installez la DLL :**
   - Copiez le fichier `.dll` dans `C:\php\ext\`
   - Renommez-le en `php_xdebug.dll` si nécessaire

5. **Activez Xdebug :**
   - Ouvrez votre `php.ini` (`C:\php\php.ini`)
   - Copiez le contenu du fichier `xdebug.ini` de ce projet
   - Modifiez la ligne `zend_extension=C:\php\ext\php_xdebug.dll` pour pointer vers votre DLL
   - Collez tout à la fin du `php.ini`

6. **Redémarrez votre serveur web** (Apache/Nginx) ou PHP-FPM

7. **Vérifiez l'installation :**
   ```bash
   php -m | findstr xdebug
   ```
   Devrait afficher "xdebug"

## Configuration WordPress

Pour déboguer les requêtes AJAX WordPress, assurez-vous que :
- Les cookies sont activés
- L'IDEKEY est défini à "VSCODE"
- Le port 9003 n'est pas bloqué par un firewall

## Dépannage

- **Port occupé :** Changez le port dans `xdebug.ini` et `launch.json`
- **Ne se déclenche pas :** Vérifiez que `xdebug.start_with_request=yes`
- **Timeout :** Augmentez `xdebug.connect_timeout_ms` dans `xdebug.ini`
- **Version incompatible :** Essayez une version de Xdebug pour PHP 8.3

## Test de l'installation

Utilisez le script `test_xdebug.php` dans ce projet pour vérifier l'installation :
```bash
php test_xdebug.php
```
- **Timeout :** Augmentez `xdebug.connect_timeout_ms` dans `xdebug.ini`