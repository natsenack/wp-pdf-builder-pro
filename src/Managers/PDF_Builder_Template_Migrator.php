<?php
/**
 * Migration et correction des templates existants
 * Adapte les templates anciens aux nouvelles configurations par défaut
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class PDF_Builder_Template_Migrator {

    /**
     * Corrige les valeurs par défaut incorrectes dans les templates existants
     */
    public static function migrate_template_data($template_data) {
        if (!is_array($template_data) || empty($template_data['elements'])) {
            return $template_data;
        }

        foreach ($template_data['elements'] as &$element) {
            if ($element['type'] === 'product_table') {
                // Corriger les valeurs par défaut pour les propriétés des totaux
                // Utiliser les valeurs par défaut du Canvas, pas les valeurs obsolètes
                
                // Valeurs par défaut correctes du Canvas Elements Manager
                $correct_defaults = [
                    'showSubtotal' => false,   // Pas la valeur true obsolète
                    'showShipping' => true,
                    'showTaxes' => true,
                    'showDiscount' => false,   // Pas la valeur true obsolète
                    'showTotal' => false       // Pas la valeur true obsolète
                ];
                
                // Appliquer les corrections SEULEMENT si non explicitement configuré
                // (on considère que true/false provenant du Canvas est intentionnel)
                foreach ($correct_defaults as $key => $default_value) {
                    // Si la propriété n'existe pas, utiliser la valeur par défaut
                    if (!isset($element[$key])) {
                        $element[$key] = $default_value;
                    }
                    // Si la valeur provient d'une ancienne configuration (avant correction), la corriger
                    // On ne peut pas le détecter facilement, donc on garde la valeur existante
                }
                
                // Générer les headers automatiquement s'ils sont incomplets
                if (!isset($element['headers']) || !is_array($element['headers'])) {
                    $element['headers'] = [];
                }
                
                // Vérifier que les headers correspondent aux colonnes
                $visible_count = 0;
                if (isset($element['columns']) && is_array($element['columns'])) {
                    $visible_count = count(array_filter($element['columns']));
                }
                
                // Régénérer les headers si incomplets
                if (count($element['headers']) !== $visible_count) {
                    $default_headers_map = [
                        'image' => 'Image',
                        'name' => 'Produit',
                        'sku' => 'SKU',
                        'quantity' => 'Qté',
                        'price' => 'Prix',
                        'total' => 'Total'
                    ];
                    
                    $columns = $element['columns'] ?? [];
                    $element['headers'] = [];
                    foreach ($columns as $col_name => $col_visible) {
                        if ($col_visible) {
                            $element['headers'][] = $default_headers_map[$col_name] ?? ucfirst($col_name);
                        }
                    }
                }
            }
        }

        return $template_data;
    }

    /**
     * Migrer tous les templates stockés en base de données
     */
    public static function migrate_all_templates() {
        global $wpdb;
        
        $table = $wpdb->postmeta;
        $meta_key = '_pdf_builder_template';
        
        $query = $wpdb->prepare(
            "SELECT post_id, meta_value FROM $table WHERE meta_key = %s",
            $meta_key
        );
        
        $results = $wpdb->get_results($query);
        
        if (empty($results)) {
            return ['migrated' => 0, 'errors' => []];
        }
        
        $count = 0;
        $errors = [];
        
        foreach ($results as $row) {
            $template_data = json_decode($row->meta_value, true);
            if (!is_array($template_data)) {
                continue;
            }
            
            $migrated = self::migrate_template_data($template_data);
            
            $updated = $wpdb->update(
                $table,
                ['meta_value' => json_encode($migrated)],
                ['post_id' => $row->post_id, 'meta_key' => $meta_key],
                ['%s'],
                ['%d', '%s']
            );
            
            if ($updated !== false) {
                $count++;
            } else {
                $errors[] = 'Erreur lors de la mise à jour du template du post ' . $row->post_id;
            }
        }
        
        return [
            'migrated' => $count,
            'total' => count($results),
            'errors' => $errors
        ];
    }

    /**
     * Migrer les templates stockés comme options (templates globaux)
     */
    public static function migrate_global_templates() {
        global $wpdb;
        
        // Récupérer tous les templates globaux
        $templates = get_option('pdf_builder_templates', []);
        
        if (!is_array($templates) || empty($templates)) {
            return ['migrated' => 0, 'errors' => []];
        }
        
        $count = 0;
        $errors = [];
        
        foreach ($templates as $template_id => &$template_data) {
            if (isset($template_data['elements'])) {
                $migrated = self::migrate_template_data($template_data);
                $templates[$template_id] = $migrated;
                $count++;
            }
        }
        
        if ($count > 0) {
            update_option('pdf_builder_templates', $templates);
        }
        
        return [
            'migrated' => $count,
            'errors' => $errors
        ];
    }
}
?>
