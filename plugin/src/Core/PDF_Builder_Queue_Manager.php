<?php
/**
 * PDF_Builder_Queue_Manager
 *
 * Gère la file d'attente côté WordPress pour la génération PDF des utilisateurs free.
 * Chaque job est enregistré dans une option WP avec horodatage, user_id et job_id.
 * La position dans la file est calculée dynamiquement à partir des jobs actifs.
 *
 * @package PDF_Builder_Pro
 * @subpackage Core
 * @version 1.0.0
 */

namespace PDF_Builder\Core;

class PDF_Builder_Queue_Manager {

    // ─── Constantes ─────────────────────────────────────────────────────────────

    /** Clé WP option qui stocke les jobs actifs (filequeue) */
    const OPTION_ACTIVE_JOBS = 'pdfb_queue_active_jobs';

    /** Durée de vie maximale d'un job en file (secondes) avant nettoyage auto */
    const JOB_TTL = 300; // 5 minutes

    /** Préfixe de transient pour stocker temporairement les PDF générés */
    const PDF_TRANSIENT_PREFIX = 'pdfb_pdf_result_';

    /** Durée pendant laquelle le PDF résultat est conservé pour téléchargement */
    const PDF_RESULT_TTL = 600; // 10 minutes

    // ─── API publique ────────────────────────────────────────────────────────────

    /**
     * Enregistre un nouveau job dans la file d'attente.
     * Retourne la position calculée (1-based).
     *
     * @param string $job_id   Identifiant retourné par le service Puppeteer (202)
     * @param int    $user_id  Identifiant WP de l'utilisateur
     * @return int   Position dans la file (1 = premier)
     */
    public static function register_job( string $job_id, int $user_id ): int {
        $jobs = self::get_active_jobs();
        $now  = time();

        // Nettoyage des jobs expirés avant insertion
        $jobs = array_filter( $jobs, fn( $j ) => ( $j['expires'] ?? 0 ) > $now );

        // Position = nombre de jobs actifs AVANT cet ajout + 1
        $position = count( $jobs ) + 1;

        $jobs[ $job_id ] = [
            'user_id' => $user_id,
            'created' => $now,
            'expires' => $now + self::JOB_TTL,
            'status'  => 'pending',
        ];

        update_option( self::OPTION_ACTIVE_JOBS, $jobs, false );

        error_log( "[QueueManager] Job enregistré : job_id={$job_id}  user_id={$user_id}  position={$position}" );

        return $position;
    }

    /**
     * Retourne la position actuelle d'un job dans la file (1-based).
     * Retourne 0 si le job n'est pas trouvé (potentiellement terminé).
     *
     * @param string $job_id
     * @return int
     */
    public static function get_position( string $job_id ): int {
        $jobs = self::get_active_jobs();
        $now  = time();

        // On ne compte que les jobs non expirés et avant le job courant
        $active_keys = array_keys(
            array_filter( $jobs, fn( $j ) => ( $j['expires'] ?? 0 ) > $now )
        );

        $idx = array_search( $job_id, $active_keys, true );

        if ( $idx === false ) {
            return 0; // Job introuvable ou expiré
        }

        return (int) $idx + 1;
    }

    /**
     * Marque un job comme terminé et le supprime de la file.
     *
     * @param string $job_id
     */
    public static function complete_job( string $job_id ): void {
        $jobs = self::get_active_jobs();
        unset( $jobs[ $job_id ] );
        update_option( self::OPTION_ACTIVE_JOBS, $jobs, false );
        error_log( "[QueueManager] Job terminé et retiré de la file : job_id={$job_id}" );
    }

    /**
     * Stocke temporairement le PDF binaire généré pour qu'il puisse être
     * téléchargé par le frontend via l'endpoint `pdf_builder_pdf_download`.
     *
     * @param string $job_id       Identifiant du job (clé du transient)
     * @param string $pdf_content  Contenu binaire du PDF
     */
    public static function store_pdf_result( string $job_id, string $pdf_content ): void {
        $transient_key = self::PDF_TRANSIENT_PREFIX . md5( $job_id );
        set_transient( $transient_key, base64_encode( $pdf_content ), self::PDF_RESULT_TTL );
        error_log( "[QueueManager] PDF stocké en transient pour job_id={$job_id}  taille=" . strlen( $pdf_content ) . " octets" );
    }

    /**
     * Récupère et supprime le PDF stocké (one-time download).
     *
     * @param string $job_id
     * @return string|false  Contenu binaire du PDF ou false si introuvable/expiré
     */
    public static function retrieve_pdf_result( string $job_id ): string|false {
        $transient_key = self::PDF_TRANSIENT_PREFIX . md5( $job_id );
        $encoded       = get_transient( $transient_key );

        if ( $encoded === false ) {
            return false;
        }

        // Suppression immédiate après lecture (one-time)
        delete_transient( $transient_key );

        return base64_decode( $encoded );
    }

    /**
     * Vérifie si un PDF résultat est disponible pour un job donné.
     *
     * @param string $job_id
     * @return bool
     */
    public static function has_pdf_result( string $job_id ): bool {
        $transient_key = self::PDF_TRANSIENT_PREFIX . md5( $job_id );
        return get_transient( $transient_key ) !== false;
    }

    /**
     * Nettoie tous les jobs expirés de la file.
     * Appelé automatiquement lors de chaque enregistrement.
     */
    public static function cleanup_expired_jobs(): void {
        $jobs = self::get_active_jobs();
        $now  = time();
        $cleaned = array_filter( $jobs, fn( $j ) => ( $j['expires'] ?? 0 ) > $now );
        if ( count( $cleaned ) !== count( $jobs ) ) {
            update_option( self::OPTION_ACTIVE_JOBS, $cleaned, false );
        }
    }

    /**
     * Retourne le nombre de jobs actifs dans la file.
     *
     * @return int
     */
    public static function get_queue_length(): int {
        $jobs = self::get_active_jobs();
        $now  = time();
        return count( array_filter( $jobs, fn( $j ) => ( $j['expires'] ?? 0 ) > $now ) );
    }

    // ─── Helpers privés ─────────────────────────────────────────────────────────

    /**
     * Récupère les jobs actifs depuis la DB.
     *
     * @return array
     */
    private static function get_active_jobs(): array {
        $jobs = get_option( self::OPTION_ACTIVE_JOBS, [] );
        return is_array( $jobs ) ? $jobs : [];
    }
}
