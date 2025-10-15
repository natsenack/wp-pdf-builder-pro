<?php
/**
 * Script de réparation des données canvas pour l'aperçu PDF
 * PDF Builder Pro - Réparation automatique des templates
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// Inclure les dépendances WordPress
require_once '../../../wp-load.php';
require_once '../../../wp-admin/includes/plugin.php';

class PDF_Canvas_Repair {

    private $wpdb;
    private $table_templates;
    private $repairs_made = 0;
    private $errors_found = 0;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_templates = $wpdb->prefix . 'pdf_builder_templates';
    }

    /**
     * Point d'entrée principal pour la réparation
     */
    public function run_repair() {
        echo "<h1>🔧 Réparation des Données Canvas - Aperçu PDF</h1>";
        echo "<p><strong>Date:</strong> " . date('d/m/Y H:i:s') . "</p>";
        echo "<hr>";

        // Vérifier la table
        if (!$this->check_table_exists()) {
            echo "<div style='color: red;'>❌ Table templates introuvable</div>";
            return;
        }

        // Récupérer tous les templates avec données canvas
        $templates = $this->get_templates_with_canvas();

        if (empty($templates)) {
            echo "<div style='color: orange;'>⚠️ Aucun template avec données canvas trouvé</div>";
            return;
        }

        echo "<div style='color: blue;'>📊 Analyse de " . count($templates) . " templates...</div>";
        echo "<br>";

        foreach ($templates as $template) {
            $this->repair_template($template);
        }

        // Résumé final
        echo "<hr>";
        echo "<h2>📋 Résumé de la réparation</h2>";
        echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
        echo "<strong>Réparations effectuées:</strong> {$this->repairs_made}<br>";
        echo "<strong>Erreurs trouvées:</strong> {$this->errors_found}<br>";
        echo "<strong>Templates traités:</strong> " . count($templates) . "<br>";
        echo "</div>";
    }

    /**
     * Vérifie si la table templates existe
     */
    private function check_table_exists() {
        return $this->wpdb->get_var("SHOW TABLES LIKE '{$this->table_templates}'") === $this->table_templates;
    }

    /**
     * Récupère tous les templates avec données canvas
     */
    private function get_templates_with_canvas() {
        return $this->wpdb->get_results("
            SELECT id, name, template_data
            FROM {$this->table_templates}
            WHERE template_data IS NOT NULL AND template_data != ''
            ORDER BY id
        ", ARRAY_A);
    }

    /**
     * Répare un template spécifique
     */
    private function repair_template($template) {
        echo "<h3>🔍 Analyse Template: {$template['name']} (ID: {$template['id']})</h3>";

        $original_data = $template['template_data'];
        $repaired_data = $original_data;
        $changes_made = false;

        // Test 1: Validation JSON
        $canvas_data = json_decode($original_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "<div style='color: red;'>❌ JSON invalide: " . json_last_error_msg() . "</div>";
            $this->errors_found++;

            // Tentative de réparation JSON
            $repaired_data = $this->repair_json_string($original_data);
            if ($repaired_data !== $original_data) {
                $canvas_data = json_decode($repaired_data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    echo "<div style='color: green;'>✅ JSON réparé automatiquement</div>";
                    $changes_made = true;
                    $this->repairs_made++;
                }
            }
        } else {
            echo "<div style='color: green;'>✅ JSON valide</div>";
        }

        // Si on a des données valides, analyser la structure
        if (isset($canvas_data) && is_array($canvas_data)) {
            $repair_result = $this->repair_canvas_structure($canvas_data);
            if ($repair_result['changed']) {
                $canvas_data = $repair_result['data'];
                $changes_made = true;
                $this->repairs_made += $repair_result['repairs'];
            }
        }

        // Sauvegarder les changements si nécessaire
        if ($changes_made) {
            $new_json = json_encode($canvas_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if ($this->save_template_data($template['id'], $new_json)) {
                echo "<div style='color: green;'>💾 Changements sauvegardés</div>";
            } else {
                echo "<div style='color: red;'>❌ Erreur sauvegarde</div>";
                $this->errors_found++;
            }
        } else {
            echo "<div style='color: blue;'>ℹ️ Aucune réparation nécessaire</div>";
        }

        echo "<br>";
    }

    /**
     * Tente de réparer une chaîne JSON corrompue
     */
    private function repair_json_string($json_string) {
        // Supprimer les caractères de contrôle et espaces parasites
        $json_string = trim($json_string);

        // Corriger les guillemets manquants dans les clés
        $json_string = preg_replace('/([{,]\s*)([a-zA-Z_][a-zA-Z0-9_]*)\s*:/', '$1"$2":', $json_string);

        // Corriger les valeurs string non quotées communes
        $json_string = preg_replace('/:(\s*)(true|false|null)([,}])/', ':"$2"$3', $json_string);

        return $json_string;
    }

    /**
     * Répare la structure des données canvas
     */
    private function repair_canvas_structure($canvas_data) {
        $changes_made = false;
        $repairs_count = 0;

        // Assurer la structure de base avec pages
        if (!isset($canvas_data['pages']) || !is_array($canvas_data['pages'])) {
            if (isset($canvas_data['elements']) && is_array($canvas_data['elements'])) {
                // Convertir l'ancienne structure en nouvelle structure
                $canvas_data = [
                    'pages' => [
                        [
                            'elements' => $canvas_data['elements']
                        ]
                    ]
                ];
                $changes_made = true;
                $repairs_count++;
                echo "<div style='color: orange;'>🔄 Structure convertie vers format pages</div>";
            } else {
                // Créer une structure vide
                $canvas_data = [
                    'pages' => [
                        [
                            'elements' => []
                        ]
                    ]
                ];
                $changes_made = true;
                $repairs_count++;
                echo "<div style='color: orange;'>🆕 Structure canvas créée</div>";
            }
        }

        // Traiter chaque page
        foreach ($canvas_data['pages'] as $page_index => &$page) {
            if (!isset($page['elements']) || !is_array($page['elements'])) {
                $page['elements'] = [];
                $changes_made = true;
                $repairs_count++;
                echo "<div style='color: orange;'>📄 Page " . ($page_index + 1) . ": éléments initialisés</div>";
            }

            // Réparer chaque élément
            foreach ($page['elements'] as &$element) {
                $element_repairs = $this->repair_element($element);
                if ($element_repairs > 0) {
                    $changes_made = true;
                    $repairs_count += $element_repairs;
                }
            }
        }

        return [
            'data' => $canvas_data,
            'changed' => $changes_made,
            'repairs' => $repairs_count
        ];
    }

    /**
     * Répare un élément individuel
     */
    private function repair_element(&$element) {
        $repairs = 0;

        // Assurer que c'est un array
        if (!is_array($element)) {
            $element = ['type' => 'text', 'content' => 'Élément réparé'];
            $repairs++;
            echo "<div style='color: orange;'>🔧 Élément converti en array</div>";
            return $repairs;
        }

        // Assurer un type valide
        if (!isset($element['type']) || empty($element['type'])) {
            $element['type'] = 'text';
            $repairs++;
            echo "<div style='color: orange;'>🏷️ Type d'élément défini par défaut</div>";
        }

        // Assurer des dimensions minimales
        $min_width = 10;
        $min_height = 10;

        if (!isset($element['width']) || $element['width'] < $min_width) {
            $element['width'] = max($element['width'] ?? 100, $min_width);
            $repairs++;
            echo "<div style='color: orange;'>📐 Largeur minimale appliquée</div>";
        }

        if (!isset($element['height']) || $element['height'] < $min_height) {
            $element['height'] = max($element['height'] ?? 50, $min_height);
            $repairs++;
            echo "<div style='color: orange;'>📏 Hauteur minimale appliquée</div>";
        }

        // Assurer des positions dans les limites A4 (595x842 px)
        $max_x = 595 - ($element['width'] ?? 100);
        $max_y = 842 - ($element['height'] ?? 50);

        if (isset($element['x']) && $element['x'] < 0) {
            $element['x'] = 0;
            $repairs++;
            echo "<div style='color: orange;'>📍 Position X corrigée (négative)</div>";
        } elseif (isset($element['x']) && $element['x'] > $max_x) {
            $element['x'] = $max_x;
            $repairs++;
            echo "<div style='color: orange;'>📍 Position X corrigée (trop grande)</div>";
        }

        if (isset($element['y']) && $element['y'] < 0) {
            $element['y'] = 0;
            $repairs++;
            echo "<div style='color: orange;'>📍 Position Y corrigée (négative)</div>";
        } elseif (isset($element['y']) && $element['y'] > $max_y) {
            $element['y'] = $max_y;
            $repairs++;
            echo "<div style='color: orange;'>📍 Position Y corrigée (trop grande)</div>";
        }

        // Assurer des propriétés par défaut selon le type
        $repairs += $this->ensure_default_properties($element);

        return $repairs;
    }

    /**
     * Assure les propriétés par défaut selon le type d'élément
     */
    private function ensure_default_properties(&$element) {
        $repairs = 0;
        $type = $element['type'];

        switch ($type) {
            case 'text':
            case 'multiline_text':
                if (!isset($element['content'])) {
                    $element['content'] = 'Texte';
                    $repairs++;
                }
                if (!isset($element['fontSize'])) {
                    $element['fontSize'] = 12;
                    $repairs++;
                }
                if (!isset($element['color'])) {
                    $element['color'] = '#000000';
                    $repairs++;
                }
                break;

            case 'rectangle':
                if (!isset($element['backgroundColor'])) {
                    $element['backgroundColor'] = 'transparent';
                    $repairs++;
                }
                break;

            case 'image':
                if (!isset($element['src'])) {
                    $element['src'] = '';
                    $repairs++;
                }
                break;

            case 'product_table':
                if (!isset($element['showHeaders'])) {
                    $element['showHeaders'] = true;
                    $repairs++;
                }
                break;
        }

        // Propriétés communes
        $common_props = ['x', 'y', 'width', 'height'];
        foreach ($common_props as $prop) {
            if (!isset($element[$prop])) {
                $defaults = ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 50];
                $element[$prop] = $defaults[$prop];
                $repairs++;
            }
        }

        if ($repairs > 0) {
            echo "<div style='color: orange;'>⚙️ Propriétés par défaut ajoutées pour $type</div>";
        }

        return $repairs;
    }

    /**
     * Sauvegarde les données réparées du template
     */
    private function save_template_data($template_id, $json_data) {
        return $this->wpdb->update(
            $this->table_templates,
            ['template_data' => $json_data],
            ['id' => $template_id],
            ['%s'],
            ['%d']
        ) !== false;
    }
}

// Exécuter la réparation
if (isset($_GET['run']) && $_GET['run'] === 'repair') {
    $repair = new PDF_Canvas_Repair();
    $repair->run_repair();
} else {
    echo "<!DOCTYPE html>
<html>
<head>
    <title>Réparation Canvas - PDF Builder Pro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .warning { background: #fff3e0; border: 1px solid #ff9800; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .btn { padding: 10px 20px; background: #2196f3; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #1976d2; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Réparation des Données Canvas</h1>
        <p>Cet outil va analyser et réparer automatiquement les données canvas de tous vos templates PDF.</p>

        <div class='warning'>
            <strong>⚠️ Attention:</strong> Cette opération va modifier les données de vos templates.
            Il est recommandé de faire une sauvegarde de la base de données avant de continuer.
        </div>

        <p><strong>Réparations effectuées:</strong></p>
        <ul>
            <li>Correction des données JSON corrompues</li>
            <li>Conversion vers la nouvelle structure de pages</li>
            <li>Ajout des propriétés manquantes</li>
            <li>Correction des positions hors limites A4</li>
            <li>Application des dimensions minimales</li>
        </ul>

        <a href='?run=repair' class='btn'>🚀 Lancer la réparation</a>
        <a href='pdf-preview-diagnostic.php' class='btn' style='background: #666;'>← Retour au diagnostic</a>
    </div>
</body>
</html>";
}
?>