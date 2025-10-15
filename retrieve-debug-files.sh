# Script pour récupérer les fichiers de debug du serveur
# À exécuter après avoir reproduit l'erreur côté serveur

echo "=== RÉCUPÉRATION DES FICHIERS DE DEBUG ==="
echo ""

# Récupérer debug_received_json.txt
echo "Récupération de debug_received_json.txt..."
curl -s "https://threeaxe.fr/wp-content/plugins/wp-pdf-builder-pro/debug_received_json.txt" -o debug_received_json_server.txt

if [ -f debug_received_json_server.txt ]; then
    echo "✅ Fichier debug_received_json.txt récupéré"
    echo "Contenu (premières lignes):"
    head -20 debug_received_json_server.txt
    echo ""
else
    echo "❌ Impossible de récupérer debug_received_json.txt"
fi

# Récupérer debug_failed_json.txt
echo "Récupération de debug_failed_json.txt..."
curl -s "https://threeaxe.fr/wp-content/plugins/wp-pdf-builder-pro/debug_failed_json.txt" -o debug_failed_json_server.txt

if [ -f debug_failed_json_server.txt ]; then
    echo "✅ Fichier debug_failed_json.txt récupéré"
    echo "Contenu (premières lignes):"
    head -20 debug_failed_json_server.txt
    echo ""
else
    echo "❌ Impossible de récupérer debug_failed_json.txt"
fi

echo "=== ANALYSE DES DONNÉES ==="
echo ""

if [ -f debug_failed_json_server.txt ]; then
    echo "Analysant les erreurs JSON..."
    # Analyser le type d'erreur
    if grep -q "Syntax error" debug_failed_json_server.txt; then
        echo "❌ Erreur de syntaxe détectée"
    fi
    if grep -q "UTF-8" debug_failed_json_server.txt; then
        echo "⚠️  Possible problème d'encodage UTF-8"
    fi
    if grep -q "Control character" debug_failed_json_server.txt; then
        echo "⚠️  Caractères de contrôle détectés"
    fi
fi

echo "=== FIN DE L'ANALYSE ==="