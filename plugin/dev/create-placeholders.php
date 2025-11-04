<?php
/**
 * Script pour créer des images placeholder PNG pour les templates prédéfinis
 */

// Fonction pour créer une image placeholder
function create_placeholder_image($filename, $template_name, $width = 200, $height = 150) {
    // Créer une image
    $image = imagecreatetruecolor($width, $height);

    // Couleurs
    $bg_color = imagecolorallocate($image, 248, 249, 250); // Gris clair
    $text_color = imagecolorallocate($image, 108, 117, 125); // Gris foncé
    $border_color = imagecolorallocate($image, 222, 226, 230); // Gris bordure

    // Remplir le fond
    imagefill($image, 0, 0, $bg_color);

    // Bordure
    imagerectangle($image, 0, 0, $width-1, $height-1, $border_color);

    // Texte
    $font_size = 12;
    $font = 5; // Police GD built-in (petite)

    // Centrer le texte
    $text = "Template: $template_name";
    $text_width = imagefontwidth($font) * strlen($text);
    $text_height = imagefontheight($font);

    $x = ($width - $text_width) / 2;
    $y = ($height - $text_height) / 2;

    imagestring($image, $font, $x, $y, $text, $text_color);

    // Sauvegarder
    $output_path = __DIR__ . "/../templates/predefined/{$filename}-preview.png";
    imagepng($image, $output_path);
    imagedestroy($image);

    return $output_path;
}

// Templates à traiter
$templates = [
    'classic' => 'Classic',
    'modern' => 'Modern',
    'corporate' => 'Corporate',
    'minimal' => 'Minimal',
    'invoice' => 'Invoice',
    'quote' => 'Quote'
];

echo "Création des images placeholder...\n\n";

foreach ($templates as $filename => $template_name) {
    $path = create_placeholder_image($filename, $template_name);
    echo "✅ Créé: $path\n";
}

echo "\nTerminé !\n";
?>