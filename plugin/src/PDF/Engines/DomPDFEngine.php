<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed
/**
 * DomPDFEngine — SUPPRIMÉ
 *
 * Ce fichier est conservé comme stub pour éviter les erreurs fatales
 * sur d'éventuelles références résiduelles.
 * Le moteur PDF est désormais assuré par PuppeteerEngine + Puppeteer_Client.
 *
 * @package PDF_Builder_Pro
 * @subpackage PDF\Engines
 * @version 2.0.0 (stub)
 * @deprecated Remplacé par PuppeteerEngine
 */

namespace PDF_Builder\PDF\Engines;

class DomPDFEngine implements PDFEngineInterface {

    public function __construct() {
        error_log( '[DomPDFEngine] Avertissement : DomPDFEngine est supprimé. Utilisez PuppeteerEngine.' );
    }

    public function generate( $html, $options = [] ) {
        error_log( '[DomPDFEngine] generate() appelé sur un moteur supprimé — retourne false.' );
        return false;
    }

    public function generate_image( $html, $options = [] ) {
        error_log( '[DomPDFEngine] generate_image() appelé sur un moteur supprimé — retourne false.' );
        return false;
    }

    public function get_name(): string {
        return 'DomPDF (supprimé)';
    }

    public function is_available(): bool {
        return false;
    }
}