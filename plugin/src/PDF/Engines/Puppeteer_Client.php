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

    const POLL_INTERVAL_S  = 3;   // secondes entre chaque polling
    const POLL_MAX_WAIT_S  = 120; // délai maximum total (secondes)

    /** @var bool */
    private $debug_enabled;
    
    /** @var bool – Mode simulation pour tester la queue sans vrai service */
    private $simulation_mode;
    
    /** @var int – Position initiale dans la queue simulée */
    private $simulation_initial_position = 5;
    
    /** @var int – Temps d'attente estimé en secondes */
    private $simulation_wait_time = 30;

    // ─── Constructeur ────────────────────────────────────────────────────────────

    public function __construct( bool $force_simulation_off = false ) {
        $this->debug_enabled = (bool) pdf_builder_get_option( 'pdf_builder_debug_enabled', false );
        if ( $force_simulation_off ) {
            $this->simulation_mode = false;
        } else {
            $this->simulation_mode = (bool) pdf_builder_get_option( 'pdf_builder_queue_simulation_enabled', false );
        }
        $this->log( "Puppeteer_Client initialized - simulation_mode=" . ( $this->simulation_mode ? 'ON' : 'OFF' ) );
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

        // 'format' dans le payload = type de sortie (pdf/png/jpg)
        // Le format papier (A4, A3…) va dans options.format (Puppeteer PDF options)
        $body_data = [
            'html'    => $html,
            'format'  => 'pdf',
            'options' => [
                'format'          => $format,  // ex: 'A4', 'A3'
                'printBackground' => true,
            ],
            'site_url' => $site_url ?: get_site_url(),
        ];

        // license_key est optionnel côté service : on l'omet si vide
        if ( $license_key !== '' ) {
            $body_data['license_key'] = $license_key;
        }

        $body = (string) wp_json_encode( $body_data );

        $this->log( "render() → POST {$path}  (license=" . ( $license_key ? 'yes' : 'no' ) . ", format={$format})" );

        [ $status, $response_body, $resp_headers ] = $this->http_post( $path, $body );

        // ─── Rendu synchrone (Premium) ───────────────────────────────────────────
        if ( $status === 200 ) {
            $tier   = $resp_headers['x-pup-tier'] ?? 'unknown'; // clés normalisées en minuscules
            $job_id = $resp_headers['x-pup-job-id'] ?? ''; // clés normalisées en minuscules
            $this->log( 'Rendu synchrone OK – tier=' . $tier . '  job_id=' . $job_id . '  ' . strlen( $response_body ) . ' octets' );
            error_log( '[Puppeteer_Client] TIER=' . $tier . '  job_id=' . $job_id . '  size=' . strlen( $response_body ) . ' bytes' );
            return $response_body;
        }

        // ─── Rendu asynchrone (Free) ─────────────────────────────────────────────
        if ( $status === 202 ) {
            $data = json_decode( $response_body, true );
            if ( empty( $data['job_id'] ) ) {
                throw new \RuntimeException( 'Service 202 sans job_id dans la réponse.' );
            }
            $tier = $data['tier'] ?? 'free';
            $this->log( "Rendu asynchrone → tier={$tier}  job_id={$data['job_id']}" );
            error_log( '[Puppeteer_Client] TIER=' . $tier . '  job_id=' . $data['job_id'] . '  (async/free)' );
            return $this->poll_job( (string) $data['job_id'] );
        }

        // ─── Erreur HTTP ─────────────────────────────────────────────────────────
        $err_msg = "Service PDF — HTTP {$status} : " . substr( $response_body, 0, 500 );
        error_log( '[Puppeteer_Client] HTTP ERROR: ' . $err_msg );
        throw new \RuntimeException( $err_msg );
    }

    /**
     * Génère une image PNG ou JPG directement via le service Puppeteer.
     *
     * @param string $html
     * @param string $format      'png' | 'jpg'
     * @param int    $width       Largeur en pixels
     * @param int    $height      Hauteur en pixels
     * @param int    $quality     Qualité JPEG (1-100)
     * @param string $license_key Clé de licence EDD
     * @param string $site_url    URL du site WordPress
     * @return string             Contenu binaire de l'image
     * @throws \RuntimeException  En cas d'erreur ou de timeout
     */
    public function render_image(
        string $html,
        string $format      = 'png',
        int    $width       = 794,
        int    $height      = 1123,
        int    $quality     = 90,
        string $license_key = '',
        string $site_url    = ''
    ): string {

        $path      = '/v2/render';
        $fmt_lower = strtolower( $format ) === 'jpg' ? 'jpg' : 'png';

        $img_options = [
            'width'           => $width,
            'height'          => $height,
            'printBackground' => true,
        ];
        // quality n'est supporté que pour JPEG (PNG l'ignore et plante Puppeteer)
        if ( $fmt_lower === 'jpg' ) {
            $img_options['quality'] = $quality;
        }

        $body_data = [
            'html'     => $html,
            'format'   => $fmt_lower,
            'options'  => $img_options,
            'site_url' => $site_url ?: get_site_url(),
        ];

        if ( $license_key !== '' ) {
            $body_data['license_key'] = $license_key;
        }

        $body = (string) wp_json_encode( $body_data );

        $this->log( "render_image() → POST {$path}  format={$fmt_lower}  {$width}x{$height}  quality={$quality}" );

        [ $status, $response_body, $resp_headers ] = $this->http_post( $path, $body );

        if ( $status === 200 ) {
            $this->log( 'Image synchrone OK – ' . strlen( $response_body ) . ' octets' );
            return $response_body;
        }

        if ( $status === 202 ) {
            $data = json_decode( $response_body, true );
            if ( empty( $data['job_id'] ) ) {
                throw new \RuntimeException( 'Service 202 sans job_id dans la réponse.' );
            }
            $this->log( "render_image async → job_id={$data['job_id']}" );
            return $this->poll_job( (string) $data['job_id'] );
        }

        $err_msg = "Service Image — HTTP {$status} : " . substr( $response_body, 0, 500 );
        error_log( '[Puppeteer_Client] IMAGE ERROR: ' . $err_msg );
        throw new \RuntimeException( $err_msg );
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

    /**
     * Interroge l'état d'un job en attente (pour affichage de la position dans la queue).
     * Retourne l'état sans bloquer.
     *
     * @param string $job_id
     * @return array  { 'status' => HTTP_CODE, 'position' => int|null, 'wait_time' => int|null, 'error' => string|null, 'body' => string }
     */
    public function get_job_status( string $job_id ): array {
        
        // ─── MODE SIMULATION : Simuler l'état du job ────────────────────────────
        if ( $this->simulation_mode && strpos( $job_id, 'sim-' ) === 0 ) {
            $sim_data = get_transient( 'pdf_queue_sim_' . $job_id );
            
            if ( ! $sim_data ) {
                // Job expiré ou inexistant
                return [
                    'status'    => 404,
                    'error'     => 'Simulation job expired',
                    'position'  => null,
                    'wait_time' => null,
                    'body'      => '',
                ];
            }
            
            $elapsed = time() - $sim_data['created_at'];
            $position = max( 1, $sim_data['position'] - intval( $elapsed / 3 ) ); // Position -1 toutes les 3 sec
            $wait_time = max( 0, $sim_data['wait_time'] - $elapsed ); // Temps qui décroît
            
            // Quand position <= 1, le job est prêt
            if ( $position <= 1 ) {
                $this->log( "Simulation: Job {$job_id} is ready (position reached 1)" );
                return [
                    'status'    => 200, // Prêt !
                    'position'  => 1,
                    'wait_time' => 0,
                    'error'     => null,
                    'body'      => 'Simulated PDF content',
                ];
            }
            
            $this->log( "Simulation: Job {$job_id} still pending (position={$position}, wait_time={$wait_time}s)" );
            return [
                'status'    => 202, // Toujours en attente
                'position'  => (int) $position,
                'wait_time' => (int) max( 0, $wait_time ),
                'error'     => null,
                'body'      => '',
            ];
        }
        
        // ─── MODE NORMAL : Interroger le service réel ──────────────────────────
        $path = "/v2/jobs/{$job_id}/result";
        $url  = self::SERVICE_BASE_URL . $path;

        $response = wp_remote_get( $url, [
            'timeout'    => 10,
            'user-agent' => 'PDF-Builder-Pro/' . PDF_BUILDER_PRO_VERSION,
            'headers'    => $this->build_get_headers( $path ),
            'sslverify'  => $this->should_verify_ssl(),
        ] );

        if ( is_wp_error( $response ) ) {
            return [
                'status'    => null,
                'error'     => $response->get_error_message(),
                'position'  => null,
                'wait_time' => null,
                'body'      => '',
            ];
        }

        $code = (int) wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $headers = wp_remote_retrieve_headers( $response );

        // Normaliser les headers
        if ( method_exists( $headers, 'getAll' ) ) {
            $headers_arr = array_change_key_case( $headers->getAll(), CASE_LOWER );
        } else {
            $headers_arr = array_change_key_case( (array) $headers, CASE_LOWER );
        }

        $result = [
            'status'    => $code,
            'body'      => $body,
            'position'  => null,
            'wait_time' => null,
            'error'     => null,
        ];

        // Parser les headers de réponse pour position et temps d'attente
        if ( ! empty( $headers_arr['x-pup-queue-position'] ) ) {
            $result['position'] = (int) $headers_arr['x-pup-queue-position'];
        }
        if ( ! empty( $headers_arr['x-pup-wait-time'] ) ) {
            $result['wait_time'] = (int) $headers_arr['x-pup-wait-time'];
        }

        if ( $code >= 500 ) {
            $result['error'] = "Service error (HTTP {$code})";
        }

        return $result;
    }

    /**
     * Initie une génération PDF et retourne immédiatement.
     * Si le service retient la requête (HTTP 202), retourne l'ID du job.
     * Si la génération est synchrone (HTTP 200), retourne le PDF binaire.
     *
     * @param string $html
     * @param string $format 
     * @param string $license_key
     * @param string $site_url
     * @return array  { 'is_ready' => bool, 'pdf' => string|null, 'job_id' => string|null, 'error' => string|null }
     */
    public function render_non_blocking(
        string $html,
        string $format      = 'A4',
        string $license_key = '',
        string $site_url    = ''
    ): array {
        
        // ─── MODE SIMULATION : Tester la queue sans appeler le vrai service ─────
        if ( $this->simulation_mode ) {
            $this->log( "SIMULATION MODE - Simulating queued response" );
            $job_id = 'sim-' . bin2hex( random_bytes( 12 ) );
            
            // Stocker l'info du job simulé en transient (30 minutes)
            set_transient( 'pdf_queue_sim_' . $job_id, [
                'created_at'  => time(),
                'position'    => $this->simulation_initial_position,
                'wait_time'   => $this->simulation_wait_time,
            ], 30 * MINUTE_IN_SECONDS );
            
            $this->log( "Simulation: Created job_id={$job_id} with initial_position={$this->simulation_initial_position}" );
            
            return [
                'is_ready' => false,
                'pdf'      => null,
                'job_id'   => $job_id,
                'error'    => null,
            ];
        }

        $path = '/v2/render';
        $body_data = [
            'html'    => $html,
            'format'  => 'pdf',
            'options' => [
                'format'          => $format,
                'printBackground' => true,
            ],
            'site_url' => $site_url ?: get_site_url(),
        ];

        if ( $license_key !== '' ) {
            $body_data['license_key'] = $license_key;
        }

        $body = (string) wp_json_encode( $body_data );
        $this->log( "render_non_blocking() → POST {$path}" );

        [ $status, $response_body, $resp_headers ] = $this->http_post( $path, $body );

        // ─── Rendu synchrone (Premium) ──────────────────────────────────────────
        if ( $status === 200 ) {
            $this->log( 'Rendu synchrone OK – ' . strlen( $response_body ) . ' octets' );
            return [
                'is_ready' => true,
                'pdf'      => $response_body,
                'job_id'   => null,
                'error'    => null,
            ];
        }

        // ─── Rendu asynchrone (Free – queue) ────────────────────────────────────
        if ( $status === 202 ) {
            $data = json_decode( $response_body, true );
            if ( empty( $data['job_id'] ) ) {
                return [
                    'is_ready' => false,
                    'pdf'      => null,
                    'job_id'   => null,
                    'error'    => 'Service 202 sans job_id',
                ];
            }
            $this->log( "Rendu asynchrone → job_id={$data['job_id']}" );
            return [
                'is_ready' => false,
                'pdf'      => null,
                'job_id'   => $data['job_id'],
                'error'    => null,
            ];
        }

        // ─── Erreur HTTP ───────────────────────────────────────────────────────
        $error_msg = "HTTP {$status}: " . substr( $response_body, 0, 200 );
        $this->log( "Erreur : {$error_msg}", 'ERROR' );
        return [
            'is_ready' => false,
            'pdf'      => null,
            'job_id'   => null,
            'error'    => $error_msg,
        ];
    }

    // ─── Polling ─────────────────────────────────────────────────────────────────

    /**
     * Interroge l'état d'un job en attente (pour affichage de la position dans la queue).
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

                // 202 ou 409 = job toujours en cours (409 = "job_not_ready")
                if ( $code !== 202 && $code !== 409 ) {
                    throw new \RuntimeException( "Polling job {$job_id} – HTTP {$code} : " . substr( $body, 0, 200 ) );
                }

                $this->log( "Polling #{$attempts} → HTTP {$code} (en cours, on attend…)" );
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
     * @return array{int, string, array}  [http_code, body, headers]
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

        $code          = (int) wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );
        $response_headers = wp_remote_retrieve_headers( $response );
        // Normaliser en tableau simple clé→valeur (WP retourne un objet CaseInsensitiveDictionary)
        // getAll() permet d'extraire proprement les clés sans artifacts d'objet
        if ( method_exists( $response_headers, 'getAll' ) ) {
            $headers_arr = array_change_key_case( $response_headers->getAll(), CASE_LOWER );
        } elseif ( is_array( $response_headers ) ) {
            $headers_arr = array_change_key_case( $response_headers, CASE_LOWER );
        } else {
            $headers_arr = array_change_key_case( (array) $response_headers, CASE_LOWER );
        }
        $this->log( 'Response headers: ' . json_encode( array_keys( $headers_arr ) ) );

        $this->log( "← HTTP {$code}  body_len=" . strlen( $response_body ) );

        return [ $code, $response_body, $headers_arr ];
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
