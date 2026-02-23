<?php
/**
 * PuppeteerEngine — Moteur PDF utilisant Puppeteer_Client (service distant threeaxe.fr)
 *
 * Remplace l'ancien moteur basé sur cURL + token simple.
 * Toute la logique HTTP / HMAC est déléguée à Puppeteer_Client.
 *
 * @package PDF_Builder_Pro
 * @subpackage PDF\Engines
 * @version 2.0.0
 */

namespace PDF_Builder\PDF\Engines;

class PuppeteerEngine implements PDFEngineInterface {

    /** @var Puppeteer_Client */
    private $client;

    /** @var bool */
    private $debug_enabled;

    // ─── Constructeur ────────────────────────────────────────────────────────────

    /**
     * @param array $config  Ignoré (conservé pour compatibilité avec les anciens appels).
     */
    public function __construct( $config = [] ) {
        $this->client        = new Puppeteer_Client();
        $this->debug_enabled = (bool) pdf_builder_get_option( 'pdf_builder_debug_enabled', false );
    }

    // ─── Interface PDFEngineInterface ────────────────────────────────────────────

    /**
     * Génère un PDF à partir de HTML.
     *
     * @param string $html
     * @param array  $options  [width, height, format, …]
     * @return string|false    Contenu binaire PDF ou false en cas d'erreur
     */
    public function generate( $html, $options = [] ) {

        $this->log( "========== GÉNÉRATION PDF (PuppeteerEngine v2) ==========" );
        $this->log( "HTML size : " . strlen( $html ) . " octets" );

        $format      = $this->resolve_format( $options );
        $license_key = $this->get_license_key();
        $site_url    = get_site_url();

        $this->log( "format={$format}  license=" . ( $license_key ? 'yes' : 'no' ) );

        try {
            $pdf = $this->client->render( $html, $format, $license_key, $site_url );
            $this->log( "PDF généré – " . strlen( $pdf ) . " octets" );
            return $pdf;
        } catch ( \Exception $e ) {
            error_log( '[PuppeteerEngine] EXCEPTION: ' . $e->getMessage() );
            $this->log( "Erreur : " . $e->getMessage(), 'ERROR' );
            return false;
        }
    }

    /**
     * Génère une image PNG/JPG via le service Puppeteer (screenshot natif).
     *
     * @param string $html
     * @param array  $options  [format => 'png'|'jpg', width, height, quality]
     * @return string          Contenu binaire de l'image
     * @throws \RuntimeException  En cas d'erreur du service
     */
    public function generate_image( $html, $options = [] ) {

        $this->log( "========== GÉNÉRATION IMAGE (PuppeteerEngine v2) ==========" );

        $format  = strtolower( $options['format'] ?? 'png' );
        $width   = (int) ( $options['width']   ?? 794 );
        $height  = (int) ( $options['height']  ?? 1123 );
        $quality = (int) ( $options['quality'] ?? 90 );

        $license_key = $this->get_license_key();
        $site_url    = get_site_url();

        $this->log( "format={$format}  {$width}x{$height}  quality={$quality}  license=" . ( $license_key ? 'yes (len=' . strlen( $license_key ) . ')' : 'NO KEY' ) );
        error_log( "[PuppeteerEngine] generate_image license_key=" . ( $license_key ? substr( $license_key, 0, 8 ) . '...' : 'EMPTY' ) );

        $image = $this->client->render_image( $html, $format, $width, $height, $quality, $license_key, $site_url );
        $this->log( "Image générée – " . strlen( $image ) . " octets (format={$format})" );
        return $image;
    }

    /**
     * Retourne le nom du moteur.
     *
     * @return string
     */
    public function get_name(): string {
        return 'Puppeteer';
    }

    /**
     * Vérifie si le service distant est joignable.
     *
     * @return bool
     */
    public function is_available(): bool {
        return $this->client->is_available();
    }

    /**
     * Teste la connexion au service.
     *
     * @return array{success: bool, message: string, response_time?: int}
     */
    public function test_connection(): array {
        $start = microtime( true );
        try {
            $available = $this->client->is_available();
            $ms        = (int) round( ( microtime( true ) - $start ) * 1000 );
            if ( $available ) {
                return [ 'success' => true, 'message' => 'Service Puppeteer joignable.', 'response_time' => $ms ];
            }
            return [ 'success' => false, 'message' => 'Service non disponible.' ];
        } catch ( \Exception $e ) {
            return [ 'success' => false, 'message' => $e->getMessage() ];
        }
    }

    // ─── Helpers privés ─────────────────────────────────────────────────────────

    /**
     * Déduit le format papier depuis les options.
     *
     * @param array $options
     * @return string
     */
    private function resolve_format( array $options ): string {
        if ( ! empty( $options['format'] ) && is_string( $options['format'] ) ) {
            return strtoupper( $options['format'] );
        }
        $w = (int) ( $options['width']  ?? 794 );
        $h = (int) ( $options['height'] ?? 1123 );
        if ( $w >= 1100 && $h >= 1550 ) {
            return 'A3';
        }
        return 'A4';
    }

    /**
     * Récupère la clé de licence EDD active.
     *
     * @return string
     */
    private function get_license_key(): string {
        // ── 1. Via LicenseManager (clé déchiffrée AES) ───────────────────────────
        if ( class_exists( '\PDF_Builder\Managers\PDF_Builder_License_Manager' ) ) {
            $lm  = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance();
            if ( method_exists( $lm, 'get_license_key' ) ) {
                $key = (string) $lm->get_license_key();
                error_log( '[PuppeteerEngine] get_license_key() LicenseManager => len=' . strlen( $key ) . ' status=' . ( method_exists( $lm, 'getLicenseStatus' ) ? $lm->getLicenseStatus() : 'N/A' ) );
                if ( $key !== '' ) {
                    return $key;
                }
            }
        }

        // ── 2. Ligne séparée pdf_builder_license_key (valeur brute / en clair) ───
        $raw = (string) pdf_builder_get_option( 'pdf_builder_license_key', '' );
        error_log( '[PuppeteerEngine] get_license_key() ligne séparée => len=' . strlen( $raw ) );
        if ( $raw !== '' ) {
            return $raw; // stockée en clair ou non déchiffrée — on tente
        }

        // ── 3. Fallback : clé dans le blob JSON pdf_builder_settings ─────────────
        $settings = pdf_builder_get_option( 'pdf_builder_settings', [] );
        if ( is_array( $settings ) && ! empty( $settings['pdf_builder_license_key'] ) ) {
            $key = (string) $settings['pdf_builder_license_key'];
            error_log( '[PuppeteerEngine] get_license_key() blob pdf_builder_settings => len=' . strlen( $key ) );
            return $key;
        }

        # Clé introuvable
        error_log( '[PuppeteerEngine] get_license_key() => EMPTY — impossible de trouver la clé de licence' );
        return '';
    }

    /**
     * Log interne.
     *
     * @param string $message
     * @param string $level  'INFO' | 'WARNING' | 'ERROR'
     */
    private function log( string $message, string $level = 'INFO' ): void {
        if ( $this->debug_enabled || $level === 'ERROR' ) {
            error_log( "[PuppeteerEngine][{$level}] {$message}" );
        }
    }
}