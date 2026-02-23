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
     * Génère une image PNG/JPG à partir de HTML.
     *
     * Stratégie en deux temps :
     *  1. Screenshot natif via le service Puppeteer (plus rapide, requiert licence premium).
     *  2. Fallback : génération PDF (disponible free + premium) puis conversion Imagick.
     *
     * @param string $html
     * @param array  $options  [format => 'png'|'jpg', width, height, quality]
     * @return string          Contenu binaire de l'image
     * @throws \RuntimeException  Si les deux méthodes échouent
     */
    public function generate_image( $html, $options = [] ) {

        $this->log( "========== GÉNÉRATION IMAGE (PuppeteerEngine v2) ==========" );

        $format  = strtolower( $options['format'] ?? 'png' );
        $width   = (int) ( $options['width']   ?? 794 );
        $height  = (int) ( $options['height']  ?? 1123 );
        $quality = (int) ( $options['quality'] ?? 90 );

        $license_key = $this->get_license_key();
        $site_url    = get_site_url();

        $this->log( "format={$format}  {$width}x{$height}  quality={$quality}  license=" . ( $license_key ? 'yes' : 'no' ) );

        // ── Étape 1 : screenshot natif Puppeteer ─────────────────────────────────
        try {
            $image = $this->client->render_image( $html, $format, $width, $height, $quality, $license_key, $site_url );
            $this->log( "Image générée via service natif – " . strlen( $image ) . " octets (format={$format})" );
            return $image;
        } catch ( \Exception $original_e ) {
            $this->log(
                "render_image() échoué ({$original_e->getMessage()}) — tentative fallback Imagick",
                'WARNING'
            );
            error_log( '[PuppeteerEngine] render_image failed: ' . $original_e->getMessage() . ' — trying Imagick fallback' );
        }

        // ── Étape 2 : fallback PDF → Imagick ────────────────────────────────────
        if ( ! extension_loaded( 'imagick' ) || ! class_exists( '\Imagick' ) ) {
            throw new \RuntimeException(
                "Génération {$format} indisponible : le service a refusé la requête (tier_restriction ?) " .
                "et l'extension PHP Imagick n'est pas installée sur ce serveur. " .
                "Vérifiez votre licence ou demandez l'activation de l'extension Imagick."
            );
        }

        $this->log( "Fallback : génération PDF puis conversion Imagick (→{$format})" );

        $paper_format = ( $width >= 1100 && $height >= 1550 ) ? 'A3' : 'A4';

        try {
            $pdf_content = $this->client->render( $html, $paper_format, $license_key, $site_url );
        } catch ( \Exception $pdf_e ) {
            throw new \RuntimeException(
                "Fallback Imagick : impossible de générer le PDF intermédiaire — " . $pdf_e->getMessage()
            );
        }

        if ( empty( $pdf_content ) ) {
            throw new \RuntimeException( 'Fallback Imagick : le PDF généré est vide.' );
        }

        try {
            $imagick = new \Imagick();
            $imagick->setResolution( 150, 150 );
            $imagick->readImageBlob( $pdf_content );
            $imagick->setIteratorIndex( 0 );  // première page uniquement
            $imagick->setImageFormat( strtoupper( $format ) );
            if ( $format === 'jpg' ) {
                $imagick->setImageCompressionQuality( $quality );
            }
            // Redimensionner en préservant le ratio si les dimensions dépassent
            $imagick->resizeImage( $width, $height, \Imagick::FILTER_LANCZOS, 1, true );

            $image_data = $imagick->getImageBlob();
            $imagick->clear();
            $imagick->destroy();

            $this->log( "Fallback Imagick OK – " . strlen( $image_data ) . " octets" );
            return $image_data;

        } catch ( \Exception $imagick_e ) {
            throw new \RuntimeException( 'Fallback Imagick échoué : ' . $imagick_e->getMessage() );
        }
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
        if ( class_exists( '\PDF_Builder\Managers\PDF_Builder_License_Manager' ) ) {
            $lm = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance();
            if ( method_exists( $lm, 'get_license_key' ) ) {
                return (string) $lm->get_license_key();
            }
        }
        return (string) pdf_builder_get_option( 'pdf_builder_license_key', '' );
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