<?php
/**
 * Puppeteer_Client — Client HMAC pour le service PDF distant threeaxe.fr
 *
 * Protocole :
 *   POST /v2/render  { html, format, license_key, site_url }
 *   Headers : X-Pup-Timestamp, X-Pup-Nonce, X-Pup-Signature
 *
 *   HTTP 200 → PDF binaire direct
 *   HTTP 202 → { job_id }  → polling GET /v2/jobs/{job_id}/result (max 30 s)
 *
 * @package PDF_Builder_Pro
 * @subpackage PDF\Engines
 * @version 2.0.0
 */

namespace PDF_Builder\PDF\Engines;

class Puppeteer_Client {

    // ─── Configuration du service ───────────────────────────────────────────────

    const SERVICE_BASE_URL = 'http://pdf.threeaxe.fr';
    const HMAC_SECRET      = '04abca0b6fb5a01f8854daecd90fdfe709df2e6c446cf328986b5d952a0ac27e';

    const POLL_INTERVAL_S  = 2;   // secondes entre chaque polling
    const POLL_MAX_WAIT_S  = 30;  // délai maximum total (secondes)

    /** @var bool */
    private $debug_enabled;

    // ─── Constructeur ────────────────────────────────────────────────────────────

    public function __construct() {
        $this->debug_enabled = (bool) pdf_builder_get_option( 'pdf_builder_debug_enabled', false );
    }

    // ─── SSL helper ─────────────────────────────────────────────────────────────

    /**
     * Détermine si la vérification SSL doit être activée.
     * En local (localhost, *.local, *.test, 127.x) on la désactive pour éviter
     * cURL error 60 (self-signed certificate / CA bundle incomplet).
     * Peut être forcé via la constante PDF_BUILDER_SSL_VERIFY (bool).
     *
     * @return bool
     */
    private function should_verify_ssl(): bool {
        if ( defined( 'PDF_BUILDER_SSL_VERIFY' ) ) {
            return (bool) PDF_BUILDER_SSL_VERIFY;
        }
        $site = strtolower( get_site_url() );
        $is_local = (
            strpos( $site, 'localhost' ) !== false ||
            strpos( $site, '127.0.0.1' ) !== false ||
            strpos( $site, '.local' )    !== false ||
            strpos( $site, '.test' )     !== false ||
            strpos( $site, '.dev' )      !== false
        );
        return ! $is_local;
    }

    // ─── API publique ────────────────────────────────────────────────────────────

    /**
     * Génère un PDF via le service distant.
     *
     * @param string $html        Contenu HTML à convertir
     * @param string $format      Format papier : 'A4', 'A3', 'Letter', … (défaut 'A4')
     * @param string $license_key Clé de licence EDD (vide = mode free → 202)
     * @param string $site_url    URL du site WordPress
     * @return string             Contenu binaire du PDF
     * @throws \RuntimeException  En cas d'erreur ou de timeout
     */
    public function render(
        string $html,
        string $format      = 'A4',
        string $license_key = '',
        string $site_url    = ''
    ): string {

        $path = '/v2/render';
        $body = (string) wp_json_encode( [
            'html'        => $html,
            'format'      => $format,
            'license_key' => $license_key,
            'site_url'    => $site_url ?: get_site_url(),
        ] );

        $this->log( "render() → POST {$path}  (license=" . ( $license_key ? 'yes' : 'no' ) . ", format={$format})" );

        [ $status, $response_body ] = $this->http_post( $path, $body );

        // ─── Rendu synchrone (Premium) ───────────────────────────────────────────
        if ( $status === 200 ) {
            $this->log( 'Rendu synchrone OK – ' . strlen( $response_body ) . ' octets' );
            return $response_body;
        }

        // ─── Rendu asynchrone (Free) ─────────────────────────────────────────────
        if ( $status === 202 ) {
            $data = json_decode( $response_body, true );
            if ( empty( $data['job_id'] ) ) {
                throw new \RuntimeException( 'Service 202 sans job_id dans la réponse.' );
            }
            $this->log( "Rendu asynchrone → job_id={$data['job_id']}" );
            return $this->poll_job( (string) $data['job_id'] );
        }

        // ─── Erreur HTTP ─────────────────────────────────────────────────────────
        throw new \RuntimeException(
            "Service PDF — HTTP {$status} : " . substr( $response_body, 0, 300 )
        );
    }

    /**
     * Vérifie la disponibilité du service.
     *
     * Logique :
     *  - WP_Error (timeout, réseau) → indisponible
     *  - HTTP 5xx (ex: 503 WebGate = VPS éteint) → indisponible
     *  - Tout autre code (200, 404, 401…) → service joignable = disponible
     *    (un 404 signifie que le service répond mais n'a pas de route /health)
     *
     * @return bool
     */
    public function is_available(): bool {
        $url = self::SERVICE_BASE_URL . '/v2/health';

        $response = wp_remote_get( $url, [
            'timeout'    => 5,
            'user-agent' => 'PDF-Builder-Pro/' . PDF_BUILDER_PRO_VERSION,
            'sslverify'  => $this->should_verify_ssl(),
        ] );

        if ( is_wp_error( $response ) ) {
            $this->log( 'is_available() → WP_Error : ' . $response->get_error_message(), 'WARNING' );
            return false;
        }

        $code = (int) wp_remote_retrieve_response_code( $response );
        // 5xx = serveur ou gateway down ; tout le reste = service joignable
        $ok = $code < 500;
        $this->log( "is_available() → HTTP {$code} → " . ( $ok ? 'OK' : 'KO' ) );
        return $ok;
    }

    // ─── Polling ─────────────────────────────────────────────────────────────────

    /**
     * Interroge /v2/jobs/{job_id}/result jusqu'à obtenir le PDF (ou timeout).
     *
     * @param string $job_id
     * @return string Contenu binaire du PDF
     * @throws \RuntimeException
     */
    private function poll_job( string $job_id ): string {

        $path       = "/v2/jobs/{$job_id}/result";
        $url        = self::SERVICE_BASE_URL . $path;
        $deadline   = time() + self::POLL_MAX_WAIT_S;
        $attempts   = 0;

        while ( time() < $deadline ) {

            $attempts++;
            $this->log( "Polling #{$attempts} → GET {$path}" );

            $response = wp_remote_get( $url, [
                'timeout'    => 10,
                'user-agent' => 'PDF-Builder-Pro/' . PDF_BUILDER_PRO_VERSION,
                'headers'    => $this->build_get_headers( $path ),
                'sslverify'  => $this->should_verify_ssl(),
            ] );

            if ( is_wp_error( $response ) ) {
                $this->log( 'Polling WP_Error : ' . $response->get_error_message(), 'WARNING' );
            } else {
                $code = (int) wp_remote_retrieve_response_code( $response );
                $body = wp_remote_retrieve_body( $response );

                if ( $code === 200 ) {
                    $this->log( "Polling→ PDF prêt après {$attempts} tentative(s) – " . strlen( $body ) . ' octets' );
                    return $body;
                }

                // 202 = toujours en cours, 404 = job inconnu
                if ( $code !== 202 ) {
                    throw new \RuntimeException( "Polling job {$job_id} – HTTP {$code} : " . substr( $body, 0, 200 ) );
                }
            }

            sleep( self::POLL_INTERVAL_S );
        }

        throw new \RuntimeException(
            "Timeout après " . self::POLL_MAX_WAIT_S . " s (job_id={$job_id})"
        );
    }

    // ─── Envoi HTTP POST avec signature HMAC ─────────────────────────────────────

    /**
     * Effectue un POST authentifié et retourne [http_code, body].
     *
     * @param string $path Chemin de l'endpoint (ex. '/v2/render')
     * @param string $body JSON encodé
     * @return array{int, string}
     * @throws \RuntimeException
     */
    private function http_post( string $path, string $body ): array {

        $url       = self::SERVICE_BASE_URL . $path;
        $timestamp = (string) time();
        $nonce     = $this->generate_nonce();
        $signature = $this->compute_signature( 'POST', $path, $timestamp, $nonce, $body );

        $headers = [
            'Content-Type'    => 'application/json',
            'X-Pup-Timestamp' => $timestamp,
            'X-Pup-Nonce'     => $nonce,
            'X-Pup-Signature' => $signature,
            'User-Agent'      => 'PDF-Builder-Pro/' . PDF_BUILDER_PRO_VERSION,
        ];

        $this->log( "POST {$url}  ts={$timestamp}  nonce={$nonce}  sig={$signature}" );

        $response = wp_remote_post( $url, [
            'method'    => 'POST',
            'timeout'   => 35,
            'headers'   => $headers,
            'body'      => $body,
            'sslverify' => $this->should_verify_ssl(),
        ] );

        if ( is_wp_error( $response ) ) {
            throw new \RuntimeException(
                'wp_remote_post échoué : ' . $response->get_error_message()
            );
        }

        $code         = (int) wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        $this->log( "← HTTP {$code}  body_len=" . strlen( $response_body ) );

        return [ $code, $response_body ];
    }

    // ─── Signature HMAC ──────────────────────────────────────────────────────────

    /**
     * Calcule la signature HMAC-SHA256.
     *
     * Canonical :
     *   METHOD\nPATH\nTIMESTAMP\nNONCE\nSHA256(body)
     *
     * Signature :
     *   "v1=" + HMAC-SHA256(canonical, secret)
     *
     * @param string $method    Méthode HTTP en majuscules ('POST', 'GET')
     * @param string $path      Chemin de l'URL (ex. '/v2/render')
     * @param string $timestamp Timestamp Unix (chaîne)
     * @param string $nonce     Nonce UUID
     * @param string $body      Corps brut de la requête
     * @return string           Signature préfixée "v1=…"
     */
    private function compute_signature(
        string $method,
        string $path,
        string $timestamp,
        string $nonce,
        string $body
    ): string {

        $body_hash = hash( 'sha256', $body );
        $canonical = implode( "\n", [ $method, $path, $timestamp, $nonce, $body_hash ] );
        $hmac      = hash_hmac( 'sha256', $canonical, self::HMAC_SECRET );

        return 'v1=' . $hmac;
    }

    /**
     * Headers HMAC pour une requête GET (body vide).
     *
     * @param string $path
     * @return array
     */
    private function build_get_headers( string $path ): array {

        $timestamp = (string) time();
        $nonce     = $this->generate_nonce();
        $signature = $this->compute_signature( 'GET', $path, $timestamp, $nonce, '' );

        return [
            'X-Pup-Timestamp' => $timestamp,
            'X-Pup-Nonce'     => $nonce,
            'X-Pup-Signature' => $signature,
        ];
    }

    // ─── Divers ──────────────────────────────────────────────────────────────────

    /**
     * Génère un nonce UUID v4 via WordPress.
     *
     * @return string UUID
     */
    private function generate_nonce(): string {
        if ( function_exists( 'wp_generate_uuid4' ) ) {
            return wp_generate_uuid4();
        }
        // Fallback basique si wp_generate_uuid4 non disponible
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0fff ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    /**
     * Log interne (error_log uniquement si debug activé).
     *
     * @param string $message
     * @param string $level   'INFO' | 'WARNING' | 'ERROR'
     */
    private function log( string $message, string $level = 'INFO' ): void {
        if ( $this->debug_enabled || $level === 'ERROR' ) {
            error_log( "[Puppeteer_Client][{$level}] {$message}" );
        }
    }
}
