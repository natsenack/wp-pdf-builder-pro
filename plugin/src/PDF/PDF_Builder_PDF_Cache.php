<?php
/**
 * PDF_Builder_PDF_Cache — Cache pour les PDFs générés par Puppeteer.
 *
 * Stratégie :
 *  1. Si l'extension PHP Redis est disponible et que le serveur répond → Redis natif.
 *  2. Sinon → WP transients (MySQL).
 *
 * La clé de cache est basée sur :
 *  - order_id
 *  - template_id
 *  - hash du contenu HTML final (capte tout changement de données commande)
 *
 * TTL : 24 h par défaut (configurable via option pdf_builder_pdf_cache_ttl).
 *
 * @package PDF_Builder_Pro
 * @subpackage PDF
 */

namespace PDF_Builder\PDF;

class PDF_Builder_PDF_Cache {

    // Préfixe des clés Redis/transient
    const KEY_PREFIX = 'pdfb_pdf_';

    // TTL par défaut (secondes)
    const DEFAULT_TTL = 86400; // 24 h

    /** @var \Redis|null */
    private static $redis = null;

    /** @var bool|null  null = non encore testé */
    private static $redis_ok = null;

    // ─── Connexion Redis ──────────────────────────────────────────────────────────

    /**
     * Tente d'obtenir une connexion Redis.
     * Le résultat est mémorisé pour éviter de re-tenter à chaque appel.
     *
     * @return \Redis|null
     */
    private static function redis(): ?\Redis {

        if ( self::$redis_ok === false ) {
            return null;
        }

        if ( self::$redis_ok === true ) {
            return self::$redis;
        }

        // Premier appel : tenter la connexion
        if ( ! class_exists( 'Redis' ) ) {
            self::$redis_ok = false;
            return null;
        }

        try {
            $host    = defined( 'PDF_BUILDER_REDIS_HOST' ) ? PDF_BUILDER_REDIS_HOST : '127.0.0.1';
            $port    = defined( 'PDF_BUILDER_REDIS_PORT' ) ? (int) PDF_BUILDER_REDIS_PORT : 6379;
            $timeout = 0.5; // 500 ms max pour ne pas bloquer la génération

            $r = new \Redis();
            if ( ! $r->connect( $host, $port, $timeout ) ) {
                self::$redis_ok = false;
                error_log( '[PDF Cache] Redis::connect() returned false' );
                return null;
            }

            // Auth optionnel
            if ( defined( 'PDF_BUILDER_REDIS_PASSWORD' ) && PDF_BUILDER_REDIS_PASSWORD !== '' ) {
                $r->auth( PDF_BUILDER_REDIS_PASSWORD );
            }

            // DB sélectionnée (défaut 0)
            $db = defined( 'PDF_BUILDER_REDIS_DB' ) ? (int) PDF_BUILDER_REDIS_DB : 0;
            if ( $db !== 0 ) {
                $r->select( $db );
            }

            $r->ping();

            self::$redis    = $r;
            self::$redis_ok = true;
            error_log( '[PDF Cache] Connexion Redis OK (' . $host . ':' . $port . ' db=' . $db . ')' );
            return $r;

        } catch ( \Exception $e ) {
            self::$redis_ok = false;
            error_log( '[PDF Cache] Redis non disponible : ' . $e->getMessage() );
            return null;
        }
    }

    // ─── Clé de cache ─────────────────────────────────────────────────────────────

    /**
     * Construit la clé de cache à partir de l'ID commande, template et hash HTML.
     *
     * @param int    $order_id
     * @param string $template_id
     * @param string $html         Contenu HTML final (avant envoi à Puppeteer)
     * @return string
     */
    public static function make_key( int $order_id, string $template_id, string $html ): string {
        $hash = substr( md5( $html ), 0, 16 );
        return self::KEY_PREFIX . $order_id . '_' . $template_id . '_' . $hash;
    }

    // ─── Lecture ─────────────────────────────────────────────────────────────────

    /**
     * Récupère un PDF depuis le cache.
     *
     * @param string $key  Clé retournée par make_key()
     * @return string|false  Contenu binaire PDF ou false si absent/expiré
     */
    public static function get( string $key ) {

        $redis = self::redis();

        if ( $redis !== null ) {
            $data = $redis->get( $key );
            if ( $data !== false && $data !== null ) {
                error_log( '[PDF Cache] HIT Redis – clé=' . $key . ' (' . strlen( $data ) . ' bytes)' );
                return $data;
            }
            error_log( '[PDF Cache] MISS Redis – clé=' . $key );
            return false;
        }

        // Fallback WP transients
        $data = get_transient( $key );
        if ( $data !== false ) {
            error_log( '[PDF Cache] HIT transient – clé=' . $key );
            return $data;
        }
        error_log( '[PDF Cache] MISS transient – clé=' . $key );
        return false;
    }

    // ─── Écriture ─────────────────────────────────────────────────────────────────

    /**
     * Stocke un PDF dans le cache.
     *
     * @param string $key     Clé retournée par make_key()
     * @param string $pdf     Contenu binaire PDF
     * @param int    $ttl     TTL en secondes (0 = valeur par défaut)
     */
    public static function set( string $key, string $pdf, int $ttl = 0 ): void {

        if ( $ttl <= 0 ) {
            $ttl = (int) pdf_builder_get_option( 'pdf_builder_pdf_cache_ttl', self::DEFAULT_TTL );
        }

        $redis = self::redis();

        if ( $redis !== null ) {
            $redis->setex( $key, $ttl, $pdf );
            error_log( '[PDF Cache] SET Redis – clé=' . $key . ' ttl=' . $ttl . 's (' . strlen( $pdf ) . ' bytes)' );
            return;
        }

        // Fallback WP transients
        set_transient( $key, $pdf, $ttl );
        error_log( '[PDF Cache] SET transient – clé=' . $key . ' ttl=' . $ttl . 's' );
    }

    // ─── Invalidation ─────────────────────────────────────────────────────────────

    /**
     * Supprime TOUS les PDFs en cache pour une commande donnée.
     * À appeler quand la commande est mise à jour.
     *
     * @param int    $order_id
     * @param string $template_id  Vide = invalider tous les templates de cette commande
     */
    public static function invalidate( int $order_id, string $template_id = '' ): void {

        $pattern = self::KEY_PREFIX . $order_id . '_' . ( $template_id !== '' ? $template_id . '_' : '' ) . '*';

        $redis = self::redis();

        if ( $redis !== null ) {
            // SCAN pour éviter KEYS qui bloque Redis sur grosse base
            $cursor = null;
            $count  = 0;
            do {
                $keys = $redis->scan( $cursor, [ 'match' => $pattern, 'count' => 100 ] );
                if ( is_array( $keys ) ) {
                    foreach ( $keys as $k ) {
                        $redis->del( $k );
                        $count++;
                    }
                }
            } while ( $cursor !== 0 && $cursor !== false );

            error_log( '[PDF Cache] Invalidation Redis : ' . $count . ' clé(s) supprimée(s) (pattern=' . $pattern . ')' );
            return;
        }

        // Fallback WP : suppression directe des clés devinables
        // (on ne peut pas faire de wildcard sur les transients)
        global $wpdb;
        $like = $wpdb->esc_like( '_transient_' . self::KEY_PREFIX . $order_id . '_' . $template_id ) . '%';
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $like
            )
        );
        error_log( '[PDF Cache] Invalidation transient pour order_id=' . $order_id );
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────────

    /**
     * Vrai si Redis est actif et joignable.
     *
     * @return bool
     */
    public static function is_redis_active(): bool {
        return self::redis() !== null;
    }
}
