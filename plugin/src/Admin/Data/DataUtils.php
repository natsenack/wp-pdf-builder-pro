<?php
namespace PDF_Builder\Admin\Data;

class DataUtils
{
    private $admin;

    public function __construct($admin)
    {
        $this->admin = $admin;
    }

    public function detectDocumentType($order_status)
    {
        // Log pour debug

        // Mapping des statuts WooCommerce vers les types de document
        $status_mapping = [
            'wc-devis' => 'devis',          // Devis (avec préfixe)
            'devis' => 'devis',             // Devis (sans préfixe)
            'wc-quote' => 'devis',           // Devis
            'wc-quotation' => 'devis',      // Devis (variante)
            'quote' => 'devis',             // Devis (sans préfixe)
            'quotation' => 'devis',         // Devis (sans préfixe)
            'wc-pending' => 'commande',     // En attente
            'wc-processing' => 'commande',  // En cours
            'wc-on-hold' => 'commande',     // En attente
            'wc-completed' => 'facture',    // Terminée -> Facture
            'wc-cancelled' => 'commande',   // Annulée
            'wc-refunded' => 'facture',     // Remboursée -> Facture
            'wc-failed' => 'commande',      // Échec
        ];
        // Retourner le type mappé ou 'commande' par défaut
        $document_type = isset($status_mapping[$order_status]) ? $status_mapping[$order_status] : 'commande';
        return $document_type;
    }

    public function getDocumentTypeLabel($document_type)
    {
        $labels = [
            'facture' => __('Facture', 'pdf-builder-pro'),
            'devis' => __('Devis', 'pdf-builder-pro'),
            'commande' => __('Commande', 'pdf-builder-pro'),
            'contrat' => __('Contrat', 'pdf-builder-pro'),
            'bon_livraison' => __('Bon de livraison', 'pdf-builder-pro'),
        ];
        return isset($labels[$document_type]) ? $labels[$document_type] : ucfirst($document_type);
    }

    public function sanitizeSettingValue($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'sanitizeSettingValue'], $value);
        } elseif (is_bool($value)) {
            return (bool) $value;
        } elseif (is_numeric($value)) {
            return is_float($value + 0) ? (float) $value : (int) $value;
        } else {
            return sanitize_text_field($value);
        }
    }

    public function cleanJsonData($json_string)
    {
        if (!is_string($json_string)) {
            return $json_string;
        }

        $original = $json_string;
        // Supprimer les caractères de contrôle invisibles (sauf tabulation, retour chariot, nouvelle ligne)
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $json_string);
        // Corriger les problèmes d'encodage UTF-8
        if (!mb_check_encoding($cleaned, 'UTF-8')) {
            $cleaned = mb_convert_encoding($cleaned, 'UTF-8', 'auto');
        }

        // Supprimer les BOM UTF-8 si présent
        $cleaned = preg_replace('/^\x{EF}\x{BB}\x{BF}/', '', $cleaned);
        // Nettoyer les espaces de noms problématiques
        $cleaned = str_replace('\\u0000', '', $cleaned);
        // Supprimer les caractères null
        $cleaned = str_replace("\0", '', $cleaned);
        // Corriger les virgules de fin dans les objets/tableaux
        $cleaned = preg_replace('/,(\s*[}\]])/m', '$1', $cleaned);
        // Supprimer les virgules multiples
        $cleaned = preg_replace('/,{2,}/', ',', $cleaned);
        // Corriger les clés non quotées (pattern simple)
        $cleaned = preg_replace('/([{,]\s*)([a-zA-Z_][a-zA-Z0-9_]*)\s*:/', '$1"$2":', $cleaned);
        // Supprimer les commentaires de style JavaScript (// et /* */)
        $cleaned = preg_replace('/\/\/.*$/m', '', $cleaned);
        $cleaned = preg_replace('/\/\*.*?\*\//s', '', $cleaned);
        // Corriger les valeurs undefined/null malformées
        $cleaned = preg_replace('/:\s*undefined\b/', ':null', $cleaned);
        // Supprimer les espaces blancs excessifs - ATTENTION: NE PAS utiliser car ça casse le JSON !
        // $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        return $cleaned;
    }

    public function aggressiveJsonClean($json_string)
    {
        if (!is_string($json_string)) {
            return $json_string;
        }

        // Appliquer d'abord le nettoyage normal
        $cleaned = $this->cleanJsonData($json_string);
        // Essayer de trouver et corriger les structures JSON de base
        // Chercher les patterns courants et essayer de les réparer

        // 1. Corriger les objets malformés avec des virgules finales
        $cleaned = preg_replace('/,(\s*})/', '$1', $cleaned);
        $cleaned = preg_replace('/,(\s*\])/m', '$1', $cleaned);

        // 2. Ajouter des guillemets manquants autour des clés
        $cleaned = preg_replace('/([{,]\s*)([a-zA-Z_][a-zA-Z0-9_]*)\s*:/', '$1"$2":', $cleaned);

        // 3. Corriger les valeurs de chaîne non quotées (simple)
        $cleaned = preg_replace('/:(\s*)([a-zA-Z_][a-zA-Z0-9_]*[a-zA-Z0-9])\s*([,}\]])/', ':"$2"$3', $cleaned);

        // 4. Supprimer les virgules finales avant les accolades fermantes
        $cleaned = preg_replace('/,(\s*})/', '$1', $cleaned);

        return $cleaned;
    }
}

