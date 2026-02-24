<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed
if ( ! defined( 'ABSPATH' ) ) exit;
    /**
     * Paramètres Généraux - PDF Builder Pro (Version compressée)
     * Onglet principal des paramètres généraux avec informations entreprise
     *
     * @version 2.2.0 - Compressé
     * @since 2025-12-09
     */

    // Récupération sécurisée des paramètres
    $settings = pdf_builder_get_option('pdf_builder_settings', array());
    
    // Récupération des informations légales individuellement
    $company_phone_manual = pdf_builder_get_option('pdf_builder_company_phone_manual', '');
    $company_siret = pdf_builder_get_option('pdf_builder_company_siret', '');
    $company_vat = pdf_builder_get_option('pdf_builder_company_vat', '');
    $company_rcs = pdf_builder_get_option('pdf_builder_company_rcs', '');
    $company_capital = pdf_builder_get_option('pdf_builder_company_capital', '');
    
    error_log('[PDF Builder] settings-general.php - Individual fields loaded: phone=' . $company_phone_manual . ', siret=' . $company_siret);

    // Récupération des informations WooCommerce
    $store_name = get_option('woocommerce_store_name', get_bloginfo('name'));
    $store_address = get_option('woocommerce_store_address', '');
    $store_city = get_option('woocommerce_store_city', '');
    $store_postcode = get_option('woocommerce_store_postcode', '');
    $store_country = get_option('woocommerce_default_country', '');
    $admin_email = get_option('admin_email', '');

    // Construction de l'adresse complète
    $address_parts = array_filter([$store_address, $store_city, $store_postcode, $store_country]);
    $full_address = implode(', ', $address_parts);

    error_log('[PDF Builder] settings-general.php loaded - settings count: ' . count($settings) . ', store_name: ' . $store_name);
?>

<section id="general" class="settings-section pdfb-general-settings" role="tabpanel" aria-labelledby="tab-general">
    <header class="pdfb-section-header">
        <h2 style="display: flex; justify-content: flex-start; align-items: center;" class="pdfb-section-title">
            <span class="dashicons dashicons-admin-home"></span>
            <span>Paramètres Généraux</span>
        </h2>
        <p class="pdfb-section-description">
            Configuration générale et informations entreprise.
        </p>
    </header>

    <div class="pdfb-settings-content">
        <!-- Formulaire supprimé - les champs sont maintenant dans le formulaire principal -->
        <input type="hidden" name="current_tab" value="general">

            <!-- Informations WooCommerce (compact) -->
            <div class="pdfb-settings-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="dashicons dashicons-store"></span>
                        Données WooCommerce
                    </h3>
                </div>
                <div class="card-content">
                    <div class="pdfb-woo-info-compact">
                        <div><strong>Entreprise:</strong> <?php echo esc_html($store_name ?: '<em>Non défini</em>'); ?></div>
                        <div><strong>Adresse:</strong> <?php echo esc_html($full_address ?: '<em>Non définie</em>'); ?></div>
                        <div><strong>Email:</strong> <?php echo esc_html($admin_email ?: '<em>Non défini</em>'); ?></div>
                    </div>
                    <p class="pdfb-woo-notice">
                        <small>⚙️ Modifiez dans <a href="<?php echo esc_url(admin_url('admin.php?page=wc-settings')); ?>" target="_blank">WooCommerce → Réglages</a></small>
                    </p>
                </div>
            </div>

            <!-- Informations complémentaires (compact) -->
            <div class="pdfb-settings-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="dashicons dashicons-edit"></span>
                        Informations légales
                    </h3>
                </div>
                <div class="card-content">
                    <div class="form-grid-compact">
                        <div class="pdfb-pdf-form-field">
                            <label for="company_phone_manual">📞 Téléphone *</label>
                            <input type="tel" id="company_phone_manual" name="company_phone_manual"
                                   value="<?php echo esc_attr($company_phone_manual); ?>"
                                   placeholder="+33 1 23 45 67 89" pattern="[\+]?[0-9\s\-\(\)]+"/>
                        </div>

                        <div class="pdfb-pdf-form-field">
                            <label for="company_siret">🆔 SIRET</label>
                            <input type="text" id="company_siret" name="company_siret"
                                   value="<?php echo esc_attr($company_siret); ?>"
                                   placeholder="12345678900012" pattern="[0-9\s]{14,17}" maxlength="17"/>
                        </div>

                        <div class="pdfb-pdf-form-field">
                            <label for="company_vat">💰 TVA</label>
                            <input type="text" id="company_vat" name="company_vat"
                                   value="<?php echo esc_attr($company_vat); ?>"
                                   placeholder="FR12345678901" pattern="[A-Z]{2}[0-9A-Z]{8,12}"/>
                        </div>

                        <div class="pdfb-pdf-form-field">
                            <label for="company_rcs">🏢 RCS</label>
                            <input type="text" id="company_rcs" name="company_rcs"
                                   value="<?php echo esc_attr($company_rcs); ?>"
                                   placeholder="Lyon B 123456789"/>
                        </div>

                        <div class="pdfb-pdf-form-field">
                            <label for="company_capital">📈 Capital</label>
                            <input type="text" id="company_capital" name="company_capital"
                                   value="<?php echo esc_attr($company_capital); ?>"
                                   placeholder="10000 €" pattern="[0-9\s€,\.]+"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>

