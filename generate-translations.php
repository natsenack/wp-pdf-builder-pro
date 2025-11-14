<?php
/**
 * Script de génération des fichiers de traduction pour PDF Builder Pro
 * Version simplifiée sans WP-CLI
 */

// Configuration
$plugin_dir = __DIR__ . '/plugin';
$languages_dir = $plugin_dir . '/languages';
$text_domain = 'pdf-builder-pro';

echo "🚀 Génération des fichiers de traduction pour PDF Builder Pro\n\n";

// Fonction pour scanner récursivement les fichiers PHP
function scan_php_files($directory) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            // Exclure certains dossiers
            $path = $file->getPathname();
            if (strpos($path, '/vendor/') === false &&
                strpos($path, '/node_modules/') === false &&
                strpos($path, '/.git/') === false) {
                $files[] = $path;
            }
        }
    }

    return $files;
}

// Fonction pour extraire les strings traduisibles d'un fichier
function extract_translatable_strings($file_path) {
    $strings = [];
    $content = file_get_contents($file_path);

    // Patterns pour les fonctions de traduction WordPress
    $patterns = [
        '/__\s*\(\s*[\'"](.*?)[\'"]\s*,\s*[\'"](.*?)[\'"]\s*\)/s', // __('text', 'domain')
        '/__\s*\(\s*[\'"](.*?)[\'"]\s*\)/s', // __('text')
        '/_e\s*\(\s*[\'"](.*?)[\'"]\s*,\s*[\'"](.*?)[\'"]\s*\)/s', // _e('text', 'domain')
        '/_e\s*\(\s*[\'"](.*?)[\'"]\s*\)/s', // _e('text')
        '/_x\s*\(\s*[\'"](.*?)[\'"]\s*,\s*[\'"](.*?)[\'"]\s*,\s*[\'"](.*?)[\'"]\s*\)/s', // _x('text', 'context', 'domain')
        '/_n\s*\(\s*[\'"](.*?)[\'"]\s*,\s*[\'"](.*?)[\'"]\s*,\s*[\'"](.*?)[\'"]\s*\)/s', // _n('singular', 'plural', 'count')
    ];

    foreach ($patterns as $pattern) {
        preg_match_all($pattern, $content, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $string) {
                // Nettoyer la string
                $string = stripslashes($string);
                $strings[] = $string;
            }
        }
    }

    return array_unique($strings);
}

// 1. Scanner tous les fichiers PHP
echo "🔍 Scan des fichiers PHP...\n";
$php_files = scan_php_files($plugin_dir);
echo "📁 " . count($php_files) . " fichiers PHP trouvés\n\n";

// 2. Extraire toutes les strings traduisibles
echo "📝 Extraction des strings traduisibles...\n";
$all_strings = [];

foreach ($php_files as $file) {
    $strings = extract_translatable_strings($file);
    $all_strings = array_merge($all_strings, $strings);
}

$all_strings = array_unique($all_strings);
echo "🔤 " . count($all_strings) . " strings traduisibles trouvées\n\n";

// 3. Générer le fichier .pot
echo "📄 Génération du fichier .pot...\n";
$pot_content = generate_pot_content($all_strings, $text_domain);
$pot_file = $languages_dir . '/pdf-builder-pro.pot';

if (file_put_contents($pot_file, $pot_content)) {
    echo "✅ Fichier .pot généré: $pot_file\n";
} else {
    echo "❌ Erreur lors de la génération du .pot\n";
}

// 4. Mettre à jour les fichiers .po existants
$po_files = glob("$languages_dir/*.po");

foreach ($po_files as $po_file) {
    $locale = basename($po_file, '.po');
    echo "🔄 Mise à jour du fichier $locale.po...\n";

    // Pour une mise à jour simple, on recopie le .pot vers le .po
    // En production, il faudrait utiliser msgmerge
    if (copy($pot_file, $po_file)) {
        echo "✅ Fichier $locale.po mis à jour\n";
    } else {
        echo "❌ Erreur lors de la mise à jour de $locale.po\n";
    }
}

// 5. Compiler les fichiers .mo (simulation simple)
echo "\n⚠️ Note: Les fichiers .mo doivent être compilés avec un outil comme Poedit ou msgfmt\n";
echo "Pour compiler manuellement:\n";
echo "msgfmt languages/pdf-builder-pro-fr_FR.po -o languages/pdf-builder-pro-fr_FR.mo\n\n";

echo "🎉 Extraction des traductions terminée !\n";

// Fonction pour générer le contenu POT
function generate_pot_content($strings, $domain) {
    $content = "# Translation file for PDF Builder Pro
# Copyright (C) 2025 PDF Builder Pro
# This file is distributed under the same license as the PDF Builder Pro package.
msgid \"\"
msgstr \"\"
\"Project-Id-Version: PDF Builder Pro\\n\"
\"Report-Msgid-Bugs-To: https://github.com/natsenack/wp-pdf-builder-pro\\n\"
\"POT-Creation-Date: " . date('Y-m-d H:iO') . "\\n\"
\"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n\"
\"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n\"
\"Language-Team: LANGUAGE <LL@li.org>\\n\"
\"Language: \\n\"
\"MIME-Version: 1.0\\n\"
\"Content-Type: text/plain; charset=UTF-8\\n\"
\"Content-Transfer-Encoding: 8bit\\n\"
\"Plural-Forms: nplurals=2; plural=(n != 1);\\n\"

";

    foreach ($strings as $string) {
        $content .= "\n#: Generated automatically\n";
        $content .= "msgid \"" . addslashes($string) . "\"\n";
        $content .= "msgstr \"\"\n";
    }

    return $content;
}