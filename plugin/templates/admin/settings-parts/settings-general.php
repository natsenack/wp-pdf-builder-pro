<?php
/**
 * General tab content
 * Updated: 2025-12-02 21:20:00
 */

/**
 * Safe wrapper for get_option that works even when WordPress is not fully loaded
 */
function pdf_builder_safe_get_option($option, $default = '') {
    if (function_exists('get_option')) {
        return get_option($option, $default);
    }
    return $default;
}

/**
 * Safe wrapper for checked function
 */
function pdf_builder_safe_checked($checked, $current = true, $echo = true) {
    if (function_exists('checked')) {
        return checked($checked, $current, $echo);
    }
    $result = checked($checked, $current, false);
    if ($echo) echo $result;
    return $result;
}

/**
 * Safe wrapper for selected function
 */
function pdf_builder_safe_selected($selected, $current = true, $echo = true) {
    if (function_exists('selected')) {
        return selected($selected, $current, $echo);
    }
    $result = selected($selected, $current, false);
    if ($echo) echo $result;
    return $result;
}

/**
 * Safe wrapper for get_bloginfo
 */
function pdf_builder_safe_get_bloginfo($show = '') {
    if (function_exists('get_bloginfo')) {
        return get_bloginfo($show);
    }
    return '';
}

/**
 * Safe wrapper for esc_html
 */
function pdf_builder_safe_esc_html($text) {
    if (function_exists('esc_html')) {
        return esc_html($text);
    }
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Safe wrapper for esc_attr
 */
function pdf_builder_safe_esc_attr($text) {
    if (function_exists('esc_attr')) {
        return esc_attr($text);
    }
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
?>
            <h2>🏠 Paramètres Généraux</h2>

            <!-- Pas de sous-onglets pour la page générale, juste le contenu direct -->
            <section class="general-section">
                <h3>🏢 Informations Entreprise</h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_company_nonce'); ?>
                    <input type="hidden" name="current_tab" value="general">
                    <!-- Le bouton submit est supprimé car on utilise le système AJAX global -->

                    <article class="general-info-box">
                        <h4 class="general-info-title">📋 Informations récupérées automatiquement de WooCommerce</h4>
                        <div class="general-info-content">
                            <p><strong>Nom de l'entreprise :</strong> <?php echo pdf_builder_safe_esc_html(pdf_builder_safe_get_option('woocommerce_store_name', pdf_builder_safe_get_bloginfo('name'))); ?></p>
                            <p><strong>Adresse complète :</strong> <?php
                            $address = pdf_builder_safe_get_option('woocommerce_store_address', '');
                            $city = pdf_builder_safe_get_option('woocommerce_store_city', '');
                            $postcode = pdf_builder_safe_get_option('woocommerce_store_postcode', '');
                            $country = pdf_builder_safe_get_option('woocommerce_default_country', '');
                            $full_address = array_filter([$address, $city, $postcode, $country]);
                            echo pdf_builder_safe_esc_html(implode(', ', $full_address) ?: '<em>Non défini</em>');
                            ?></p>
                            <p><strong>Email :</strong> <?php echo pdf_builder_safe_esc_html(pdf_builder_safe_get_option('admin_email', '<em>Non défini</em>')); ?></p>
                            <p class="general-info-hint">
                            ℹ️ Ces informations sont automatiquement récupérées depuis les paramètres WooCommerce (WooCommerce > Réglages > Général).
                            </p>
                        </div>

                        <h4 class="general-manual-title">📝 Informations à saisir manuellement</h4>
                        <p class="general-manual-description">
                        Ces informations ne sont pas disponibles dans WooCommerce et doivent être saisies manuellement :
                        </p>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="company_phone_manual">Téléphone</label></th>
                                <td>
                                    <input type="text" id="company_phone_manual" name="company_phone_manual"
                                        value="<?php echo pdf_builder_safe_esc_attr(pdf_builder_safe_get_option('pdf_builder_company_phone_manual', '')); ?>"
                                        placeholder="+33 1 23 45 67 89" />
                                        <p class="description">Téléphone de l'entreprise</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="company_siret">Numéro SIRET</label></th>
                                <td>
                                    <input type="text" id="company_siret" name="company_siret"
                                        value="<?php echo pdf_builder_safe_esc_attr(pdf_builder_safe_get_option('pdf_builder_company_siret', '')); ?>"
                                        placeholder="123 456 789 00012" />
                                    <p class="description">Numéro SIRET de l'entreprise</p>
                                    <div class="company-preview"></div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="company_vat">Numéro TVA</label></th>
                                <td>
                                    <input type="text" id="company_vat" name="company_vat"
                                        value="<?php echo pdf_builder_safe_esc_attr(pdf_builder_safe_get_option('pdf_builder_company_vat', '')); ?>"
                                        placeholder="FR12345678901, DE123456789, BE0123456789" />
                                    <p class="description">Numéro TVA européen (2 lettres pays + 8-12 caractères)</p>
                                    <div class="company-preview"></div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="company_rcs">RCS</label></th>
                                <td>
                                    <input type="text" id="company_rcs" name="company_rcs"
                                        value="<?php echo pdf_builder_safe_esc_attr(pdf_builder_safe_get_option('pdf_builder_company_rcs', '')); ?>"
                                        placeholder="Lyon B 123 456 789" />
                                    <p class="description">Numéro RCS de l'entreprise</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="company_capital">Capital social</label></th>
                                <td>
                                    <input type="text" id="company_capital" name="company_capital"
                                        value="<?php echo pdf_builder_safe_esc_attr(pdf_builder_safe_get_option('pdf_builder_company_capital', '')); ?>"
                                        placeholder="10 000 €" />
                                    <p class="description">Capital social de l'entreprise</p>
                                </td>
                            </tr>
                        </table>
                    </article>
                </form>
            </section>
