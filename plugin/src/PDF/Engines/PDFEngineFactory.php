<?php
/**
 * Factory pour créer des moteurs de génération PDF
 *
 * Depuis v2.0.0 : seul PuppeteerEngine (service threeaxe.fr) est utilisé.
 * DomPDF est définitivement supprimé.
 *
 * @package PDF_Builder_Pro
 * @subpackage PDF\Engines
 * @version 2.0.0
 */

namespace PDF_Builder\PDF\Engines;

class PDFEngineFactory {

    /** @var PDFEngineInterface|null */
    private static $engine_instance = null;

    /**
     * Crée ou retourne une instance de moteur PDF.
     *
     * @param string|null $engine_name  Ignoré (conservé pour compatibilité). Toujours Puppeteer.
     * @param array       $config       Ignoré (conservé pour compatibilité).
     * @return PDFEngineInterface
     */
    public static function create( $engine_name = null, $config = [] ): PDFEngineInterface {
        self::log_engine_selection( 'Puppeteer', $engine_name ?? 'default' );
        return new PuppeteerEngine();
    }

    /**
     * Retourne le moteur singleton.
     *
     * @param string|null $engine_name  Ignoré.
     * @param array       $config       Ignoré.
     * @return PDFEngineInterface
     */
    public static function get_instance( $engine_name = null, $config = [] ): PDFEngineInterface {
        if ( self::$engine_instance === null ) {
            self::$engine_instance = self::create();
        }
        return self::$engine_instance;
    }

    /**
     * Réinitialise l'instance singleton.
     */
    public static function reset_instance(): void {
        self::$engine_instance = null;
    }

    /**
     * Liste les moteurs disponibles.
     *
     * @return array
     */
    public static function list_available_engines(): array {
        $puppeteer = new PuppeteerEngine();
        return [
            'puppeteer' => [
                'name'        => 'Puppeteer',
                'available'   => $puppeteer->is_available(),
                'description' => 'Service Puppeteer distant (threeaxe.fr) — PDF haute fidélité',
            ],
        ];
    }

    /**
     * Teste le moteur Puppeteer et retourne le résultat.
     *
     * @return array
     */
    public static function test_engines(): array {
        $puppeteer = new PuppeteerEngine();
        $result    = $puppeteer->test_connection();

        return [
            'puppeteer' => [
                'name'          => 'Puppeteer',
                'success'       => $result['success'],
                'message'       => $result['message'],
                'response_time' => $result['response_time'] ?? null,
            ],
        ];
    }

    /**
     * Retourne des informations sur les moteurs (pour les pages de diagnostic).
     *
     * @return array
     */
    public static function get_engines_status(): array {
        return self::list_available_engines();
    }

    /**
     * Log de sélection du moteur.
     *
     * @param string $engine
     * @param string $reason
     */
    private static function log_engine_selection( string $engine, string $reason ): void {
        error_log( "[PDFEngineFactory] Moteur sélectionné : {$engine} (raison: {$reason})" );
    }
}