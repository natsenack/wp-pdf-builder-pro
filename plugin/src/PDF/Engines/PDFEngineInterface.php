<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
/**
 * Interface pour les moteurs de génération PDF
 * 
 * @package PDF_Builder_Pro
 * @subpackage PDF\Engines
 * @version 1.0.0
 */

namespace PDF_Builder\PDF\Engines;

interface PDFEngineInterface {
    
    /**
     * Génère un PDF à partir de HTML
     * 
     * @param string $html Contenu HTML
     * @param array $options Options de génération [width, height, orientation, etc.]
     * @return string|false Contenu PDF binaire ou false en cas d'erreur
     */
    public function generate($html, $options = []);
    
    /**
     * Génère une image (PNG/JPG) à partir de HTML
     * 
     * @param string $html Contenu HTML
     * @param array $options Options [format => 'png'|'jpg', width, height, quality]
     * @return string|false Contenu image binaire ou false
     */
    public function generate_image($html, $options = []);
    
    /**
     * Retourne le nom du moteur
     * 
     * @return string Nom du moteur
     */
    public function get_name();
    
    /**
     * Vérifie si le moteur est disponible
     * 
     * @return bool True si disponible
     */
    public function is_available();
}
