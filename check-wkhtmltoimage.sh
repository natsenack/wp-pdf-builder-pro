#!/bin/bash

###############################################################################
# Script de vérification wkhtmltoimage pour PDF Builder Pro
# Usage: ./check-wkhtmltoimage.sh
###############################################################################

echo "=========================================="
echo "  Vérification wkhtmltoimage"
echo "=========================================="
echo ""

# Fonction de vérification avec code couleur
check_command() {
    local cmd=$1
    local name=$2
    
    if command -v "$cmd" &> /dev/null; then
        echo "✅ $name: INSTALLÉ"
        echo "   Chemin: $(which $cmd)"
        echo "   Version: $($cmd --version 2>&1 | head -1)"
        return 0
    else
        echo "❌ $name: NON INSTALLÉ"
        return 1
    fi
    echo ""
}

# Vérifier wkhtmltoimage
echo "1. Vérification binaire wkhtmltoimage"
echo "--------------------------------------"
check_command "wkhtmltoimage" "wkhtmltoimage"
WKHTML_INSTALLED=$?
echo ""

# Vérifier wkhtmltopdf (même package)
echo "2. Vérification wkhtmltopdf (même package)"
echo "------------------------------------------"
check_command "wkhtmltopdf" "wkhtmltopdf"
echo ""

# Vérifier les dépendances système
echo "3. Vérification dépendances système"
echo "------------------------------------"

if [ -f /etc/debian_version ]; then
    echo "Système: Debian/Ubuntu"
    echo ""
    echo "Bibliothèques requises:"
    
    for lib in libxrender1 libfontconfig1 libxext6 libx11-6; do
        if dpkg -l | grep -q "$lib"; then
            echo "  ✅ $lib"
        else
            echo "  ❌ $lib (installer avec: sudo apt-get install $lib)"
        fi
    done
elif [ -f /etc/redhat-release ]; then
    echo "Système: RedHat/CentOS/Fedora"
    echo ""
    echo "Bibliothèques requises:"
    
    for lib in libXrender libXext fontconfig freetype; do
        if rpm -qa | grep -q "$lib"; then
            echo "  ✅ $lib"
        else
            echo "  ❌ $lib (installer avec: sudo yum install $lib)"
        fi
    done
elif [[ "$OSTYPE" == "darwin"* ]]; then
    echo "Système: macOS"
    echo "Les dépendances sont gérées via Homebrew automatiquement"
else
    echo "Système non reconnu, vérification manuelle requise"
fi

echo ""

# Test de génération simple
echo "4. Test de génération d'image"
echo "------------------------------"

if [ $WKHTML_INSTALLED -eq 0 ]; then
    TEST_HTML="/tmp/wkhtml-test-$$.html"
    TEST_PNG="/tmp/wkhtml-test-$$.png"
    
    # Créer un HTML de test
    cat > "$TEST_HTML" <<'EOF'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
        }
        h1 { font-size: 36px; margin: 0; }
        p { font-size: 18px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ wkhtmltoimage fonctionne!</h1>
        <p>PDF Builder Pro - Test de génération</p>
        <p>Date: $(date)</p>
    </div>
</body>
</html>
EOF

    echo "Génération d'une image de test..."
    
    if wkhtmltoimage --format png --width 800 "$TEST_HTML" "$TEST_PNG" 2>/dev/null; then
        if [ -f "$TEST_PNG" ]; then
            SIZE=$(stat -f%z "$TEST_PNG" 2>/dev/null || stat -c%s "$TEST_PNG" 2>/dev/null)
            echo "✅ Image générée avec succès!"
            echo "   Fichier: $TEST_PNG"
            echo "   Taille: $SIZE octets"
            echo ""
            echo "Vous pouvez ouvrir l'image avec:"
            echo "   xdg-open $TEST_PNG  # Linux"
            echo "   open $TEST_PNG      # macOS"
            
            # Nettoyer après 10 secondes
            (sleep 10 && rm -f "$TEST_HTML" "$TEST_PNG") &
        else
            echo "❌ Échec: fichier non créé"
        fi
    else
        echo "❌ Erreur lors de la génération"
        echo "Sortie d'erreur:"
        wkhtmltoimage --format png --width 800 "$TEST_HTML" "$TEST_PNG" 2>&1
    fi
    
    rm -f "$TEST_HTML"
else
    echo "⚠️  Test ignoré car wkhtmltoimage n'est pas installé"
fi

echo ""

# Vérifier les permissions PHP
echo "5. Vérification permissions PHP"
echo "--------------------------------"

if command -v php &> /dev/null; then
    echo "PHP version: $(php -v | head -1)"
    
    # Tester shell_exec
    TEST_OUTPUT=$(php -r "echo shell_exec('which wkhtmltoimage 2>&1');")
    
    if [ -n "$TEST_OUTPUT" ]; then
        echo "✅ PHP peut exécuter wkhtmltoimage"
        echo "   Chemin détecté: $TEST_OUTPUT"
    else
        echo "❌ PHP ne peut pas exécuter wkhtmltoimage"
        echo "   Vérifiez disable_functions dans php.ini"
        echo "   La fonction shell_exec doit être autorisée"
    fi
else
    echo "⚠️  PHP CLI non trouvé"
fi

echo ""

# Résumé et recommandations
echo "=========================================="
echo "  RÉSUMÉ"
echo "=========================================="
echo ""

if [ $WKHTML_INSTALLED -eq 0 ]; then
    echo "✅ wkhtmltoimage est installé et fonctionnel"
    echo ""
    echo "Vous pouvez maintenant utiliser la génération PNG/JPG"
    echo "dans PDF Builder Pro (fonctionnalité premium)"
else
    echo "❌ wkhtmltoimage n'est PAS installé"
    echo ""
    echo "Installation recommandée:"
    echo ""
    
    if [ -f /etc/debian_version ]; then
        echo "  sudo apt-get update"
        echo "  sudo apt-get install -y wkhtmltopdf"
    elif [ -f /etc/redhat-release ]; then
        echo "  sudo yum install -y wkhtmltopdf"
    elif [[ "$OSTYPE" == "darwin"* ]]; then
        echo "  brew install wkhtmltopdf"
    else
        echo "  Consultez: https://wkhtmltopdf.org/downloads.html"
    fi
    
    echo ""
    echo "Ou téléchargez depuis:"
    echo "  https://github.com/wkhtmltopdf/packaging/releases"
fi

echo ""
echo "Documentation complète: docs/WKHTMLTOIMAGE_INSTALLATION.md"
echo "=========================================="
