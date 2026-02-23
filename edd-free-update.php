<?php
/**
 * MU-Plugin: EDD Software Licensing - Require License for Updates
 *
 * Ajoute une checkbox "Require License for Downloading Updates" dans
 * l'onglet Versions de chaque produit EDD Software Licensing.
 * Si désactivée, les utilisateurs sans licence peuvent télécharger le ZIP.
 *
 * Location: /wp-content/mu-plugins/edd-free-update.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. AJOUTER LA CHECKBOX dans l'interface EDD SL (onglet Versions)
 *    Hook après le rendu du bloc "Staged Rollouts"
 */
add_action( 'edd_sl_after_version_fields', 'edd_free_render_require_license_field', 20, 1 );
add_action( 'edd_sl_metabox_fields_after', 'edd_free_render_require_license_field', 20, 1 );

// Fallback : injection via JS si les hooks ci-dessus n'existent pas
add_action( 'admin_footer', 'edd_free_inject_checkbox_via_js' );

function edd_free_render_require_license_field( $post_id ) {
    if ( empty( $post_id ) ) {
        $post_id = get_the_ID();
    }
    $value = get_post_meta( $post_id, '_edd_sl_require_license_updates', true );
    // Par défaut : licence requise (valeur '1')
    if ( $value === '' ) {
        $value = '1';
    }
    ?>
    <div class="edd-form-group edd-sl-require-license-updates" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #ddd;">
        <label class="edd-form-group__label" style="font-weight: 600;">
            <?php esc_html_e( 'Require License for Downloading Updates', 'easy-digital-downloads' ); ?>
        </label>
        <div class="edd-form-group__control">
            <input type="hidden" name="_edd_sl_require_license_updates" value="0" />
            <label>
                <input type="checkbox"
                       name="_edd_sl_require_license_updates"
                       value="1"
                    <?php checked( $value, '1' ); ?> />
                <?php esc_html_e( 'Require a valid license key to download updates. Uncheck to allow free/public download.', 'easy-digital-downloads' ); ?>
            </label>
        </div>
    </div>
    <?php
}

/**
 * Fallback JS : injecte la checkbox après le bloc "Staged Rollouts"
 * si les hooks PHP n'ont pas été déclenchés par EDD SL.
 */
function edd_free_inject_checkbox_via_js() {
    global $post;
    if ( empty( $post ) || $post->post_type !== 'download' ) {
        return;
    }
    $post_id = $post->ID;
    $value   = get_post_meta( $post_id, '_edd_sl_require_license_updates', true );
    if ( $value === '' ) {
        $value = '1';
    }
    $checked = $value === '1' ? 'checked' : '';
    $label   = esc_js( __( 'Require a valid license key to download updates. Uncheck to allow free/public download.', 'easy-digital-downloads' ) );
    $title   = esc_js( __( 'Require License for Downloading Updates', 'easy-digital-downloads' ) );
    ?>
    <script type="text/javascript">
    (function($) {
        // Cibler le bloc "Staged Rollouts" et insérer après
        var $target = $('label, .edd-form-group__label, p, strong, h4').filter(function() {
            return $(this).text().trim().indexOf('Staged Rollout') !== -1;
        }).closest('.edd-form-group, tr, div').last();

        if (!$target.length) {
            // Fallback : chercher par input[name*=staged]
            $target = $('input[name*="staged"], input[id*="staged"]').closest('.edd-form-group, tr, div').last();
        }

        var html = '<div class="edd-form-group edd-sl-require-license-updates" style="margin-top:16px;padding-top:16px;border-top:1px solid #ddd;">' +
            '<label class="edd-form-group__label" style="font-weight:600;"><?php echo esc_js( $title ); ?></label>' +
            '<div class="edd-form-group__control">' +
            '<input type="hidden" name="_edd_sl_require_license_updates" value="0" />' +
            '<label>' +
            '<input type="checkbox" name="_edd_sl_require_license_updates" value="1" <?php echo $checked; ?> /> ' +
            '<?php echo esc_js( $label ); ?>' +
            '</label>' +
            '</div>' +
            '</div>';

        if ($target.length) {
            $target.after(html);
        } else {
            // Dernier recours : ajouter dans le form EDD SL
            $('#edd_sl_version_info, #edd-sl-versions-metabox, .edd-sl-versions').append(html);
        }
    })(jQuery);
    </script>
    <?php
}

/**
 * 2. SAUVEGARDER la checkbox lors du save_post
 */
add_action( 'save_post', 'edd_free_save_require_license_field', 20, 2 );

function edd_free_save_require_license_field( $post_id, $post ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! isset( $post->post_type ) || $post->post_type !== 'download' ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( ! isset( $_POST['_edd_sl_require_license_updates'] ) ) {
        return;
    }

    $value = sanitize_text_field( $_POST['_edd_sl_require_license_updates'] );
    update_post_meta( $post_id, '_edd_sl_require_license_updates', $value === '1' ? '1' : '0' );

    error_log( '[EDD Free] Product #' . $post_id . ' require_license_updates = ' . $value );
}

/**
 * 3. INTERCEPTER get_version : si la checkbox est décochée,
 *    injecter le package directement depuis les données EDD du produit
 */
add_action( 'init', 'edd_free_intercept_get_version', 1 );

function edd_free_intercept_get_version() {
    if ( ! isset( $_GET['edd_action'] ) || $_GET['edd_action'] !== 'get_version' ) {
        return;
    }

    // Si une licence valide est fournie, EDD SL gère normalement
    if ( ! empty( $_GET['license'] ) ) {
        return;
    }

    // Identifier le produit
    $item_id   = isset( $_GET['item_id'] ) ? intval( $_GET['item_id'] ) : 0;
    $item_name = isset( $_GET['item_name'] ) ? sanitize_text_field( $_GET['item_name'] ) : '';

    // Trouver le post_id EDD correspondant
    $post_id = $item_id;
    if ( ! $post_id && $item_name ) {
        $post    = get_page_by_title( $item_name, OBJECT, 'download' );
        $post_id = $post ? $post->ID : 0;
    }

    if ( ! $post_id ) {
        return;
    }

    // Vérifier la checkbox
    $require_license = get_post_meta( $post_id, '_edd_sl_require_license_updates', true );

    // Par défaut : licence requise sauf si explicitement désactivée ('0')
    if ( $require_license !== '0' ) {
        return; // Laisser EDD SL gérer (retourne package vide sans licence)
    }

    // La licence n'est PAS requise → lire les infos directement depuis EDD
    $plugin_version = isset( $_GET['plugin_version'] ) ? sanitize_text_field( $_GET['plugin_version'] ) : '1.0.0.0';

    $info = edd_free_get_product_version_info( $post_id );

    if ( empty( $info['version'] ) || empty( $info['package'] ) ) {
        error_log( '[EDD Free] Impossible de lire la version/package EDD pour product #' . $post_id );
        return;
    }

    if ( version_compare( $info['version'], $plugin_version, '<=' ) ) {
        return; // Pas de mise à jour disponible
    }

    $response = array(
        'new_version'   => $info['version'],
        'version'       => $info['version'],
        'package'       => $info['package'],
        'download_link' => $info['package'],
        'url'           => get_permalink( $post_id ),
        'slug'          => get_post_field( 'post_name', $post_id ),
        'stable'        => 1,
        'tested'        => $info['tested_wp'],
    );

    error_log( '[EDD Free] Injection package libre v' . $info['version'] . ' pour product #' . $post_id );

    header( 'Content-Type: application/json' );
    echo wp_json_encode( $response );
    exit;
}

/**
 * Récupère la version et l'URL du package directement depuis EDD (post meta)
 * EDD Software Licensing stocke :
 *   - _edd_sl_version    : numéro de version (onglet Versions)
 *   - edd_download_files : tableau des fichiers uploadés dans EDD
 */
function edd_free_get_product_version_info( $post_id ) {
    // Version définie dans EDD SL (onglet Versions → Version Number)
    $version = get_post_meta( $post_id, '_edd_sl_version', true );

    // Fichiers du produit EDD (tableau [{name, file, attachment_id, ...}])
    $files = get_post_meta( $post_id, 'edd_download_files', true );

    $file_key    = null;
    $package_url = '';

    if ( ! empty( $files ) && is_array( $files ) ) {
        // Chercher le fichier correspondant à la version actuelle
        foreach ( $files as $key => $file ) {
            $file_url = isset( $file['file'] ) ? $file['file'] : '';
            if ( is_numeric( $file_url ) ) {
                $file_url = wp_get_attachment_url( intval( $file_url ) );
            }
            if ( $version && strpos( $file_url, $version ) !== false ) {
                $file_key = $key;
                break;
            }
        }
        // Fallback : dernier fichier
        if ( $file_key === null ) {
            end( $files );
            $file_key = key( $files );
        }
    }

    // Générer une URL de téléchargement EDD via notre endpoint custom
    if ( $file_key !== null ) {
        $token = wp_hash( 'edd_free_download_' . $post_id . '_' . $file_key . '_' . date( 'Y-m-d' ) );
        $package_url = add_query_arg( array(
            'edd_free_download' => 1,
            'download_id'       => $post_id,
            'file_key'          => $file_key,
            'token'             => $token,
        ), home_url( '/' ) );
    }

    // Récupérer "Tested up to WP"
    $tested_wp = get_post_meta( $post_id, '_edd_sl_tested_up_to', true ) ?: '6.6.0';

    return array(
        'version'   => $version,
        'package'   => $package_url,
        'tested_wp' => $tested_wp,
        'file_key'  => $file_key,
    );
}

/**
 * 4. ENDPOINT de téléchargement libre
 *    Sert le fichier EDD directement via le serveur (pas d'accès direct /uploads/edd/)
 */
add_action( 'init', 'edd_free_handle_download_request', 2 );

function edd_free_handle_download_request() {
    if ( ! isset( $_GET['edd_free_download'] ) || $_GET['edd_free_download'] !== '1' ) {
        return;
    }

    $download_id = isset( $_GET['download_id'] ) ? intval( $_GET['download_id'] ) : 0;
    $file_key    = isset( $_GET['file_key'] ) ? intval( $_GET['file_key'] ) : 0;
    $token       = isset( $_GET['token'] ) ? sanitize_text_field( $_GET['token'] ) : '';

    // Vérifier le token (valide aujourd'hui et hier pour les edge cases minuit)
    $valid_today     = wp_hash( 'edd_free_download_' . $download_id . '_' . $file_key . '_' . date( 'Y-m-d' ) );
    $valid_yesterday = wp_hash( 'edd_free_download_' . $download_id . '_' . $file_key . '_' . date( 'Y-m-d', strtotime( '-1 day' ) ) );

    if ( $token !== $valid_today && $token !== $valid_yesterday ) {
        wp_die( 'Token invalide ou expiré.', 403 );
    }

    // Vérifier que la checkbox est décochée pour ce produit
    $require_license = get_post_meta( $download_id, '_edd_sl_require_license_updates', true );
    if ( $require_license !== '0' ) {
        wp_die( 'Une licence est requise pour télécharger ce fichier.', 403 );
    }

    // Récupérer le fichier depuis EDD
    $files = get_post_meta( $download_id, 'edd_download_files', true );
    if ( empty( $files[ $file_key ] ) ) {
        wp_die( 'Fichier introuvable.', 404 );
    }

    $file_url = $files[ $file_key ]['file'];

    // Si c'est un attachment_id, récupérer le chemin réel
    if ( is_numeric( $file_url ) ) {
        $file_path = get_attached_file( intval( $file_url ) );
    } else {
        // Convertir l'URL en chemin serveur local
        $upload_dir = wp_upload_dir();
        $file_path  = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $file_url );
    }

    if ( ! $file_path || ! file_exists( $file_path ) ) {
        error_log( '[EDD Free] Fichier introuvable sur le serveur: ' . $file_path );
        wp_die( 'Fichier introuvable sur le serveur.', 404 );
    }

    $filename  = basename( $file_path );
    $file_size = filesize( $file_path );

    error_log( '[EDD Free] Téléchargement libre: ' . $filename . ' pour product #' . $download_id );

    // Servir le fichier
    nocache_headers();
    header( 'Content-Type: application/zip' );
    header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
    header( 'Content-Length: ' . $file_size );
    header( 'Content-Transfer-Encoding: binary' );

    @ob_end_clean();
    readfile( $file_path );
    exit;
}
