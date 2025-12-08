<?php
    /**
     * Paramètres Généraux - PDF Builder Pro
     * Onglet principal des paramètres généraux avec informations entreprise
     *
     * @version 2.1.0
     * @since 2025-12-08
     */

    // Récupération sécurisée des paramètres
    $settings = get_option('pdf_builder_settings', array());

    // Fonction helper pour récupérer une valeur de setting avec fallback
    function get_pdf_setting($key, $default = '') {
        static $settings = null;
        if ($settings === null) {
            $settings = get_option('pdf_builder_settings', array());
        }
        return $settings[$key] ?? $default;
    }

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
?>

<section id="general" class="settings-section general-settings" role="tabpanel" aria-labelledby="tab-general">
    <header class="section-header">
        <h2 class="section-title">
            <span class="dashicons dashicons-admin-home"></span>
            Paramètres Généraux
        </h2>
        <p class="section-description">
            Configuration générale du générateur de PDF et informations de l'entreprise.
        </p>
    </header>

    <div class="settings-content">
        <!-- Informations WooCommerce -->
        <div class="settings-card woocommerce-info-card">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="dashicons dashicons-store"></span>
                    Informations récupérées automatiquement
                </h3>
                <p class="card-description">
                    Ces données sont synchronisées avec vos paramètres WooCommerce.
                </p>
            </div>

            <div class="card-content">
                <div class="info-grid">
                    <div class="info-item">
                        <label class="info-label">Nom de l'entreprise</label>
                        <div class="info-value">
                            <?php echo esc_html($store_name ?: '<em class="text-muted">Non défini</em>'); ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <label class="info-label">Adresse complète</label>
                        <div class="info-value">
                            <?php echo esc_html($full_address ?: '<em class="text-muted">Non définie</em>'); ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <label class="info-label">Email de contact</label>
                        <div class="info-value">
                            <?php echo esc_html($admin_email ?: '<em class="text-muted">Non défini</em>'); ?>
                        </div>
                    </div>
                </div>

                <div class="info-notice notice notice-info">
                    <p>
                        <span class="dashicons dashicons-info"></span>
                        <strong>Information :</strong> Ces informations sont automatiquement récupérées depuis
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wc-settings')); ?>" target="_blank">
                            WooCommerce → Réglages → Général
                        </a>.
                        Modifiez-les là-bas pour les mettre à jour ici.
                    </p>
                </div>
            </div>
        </div>

        <!-- Informations manuelles -->
        <div class="settings-card manual-info-card">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="dashicons dashicons-edit"></span>
                    Informations complémentaires
                </h3>
                <p class="card-description">
                    Informations légales et de contact non disponibles dans WooCommerce.
                </p>
            </div>

            <div class="card-content">
                <form method="post" action="" id="general-form" class="settings-form" novalidate>
                    <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_company_nonce'); ?>
                    <input type="hidden" name="current_tab" value="general">

                    <div class="form-grid">
                        <!-- Téléphone -->
                        <div class="form-field">
                            <label for="company_phone_manual" class="field-label required">
                                <span class="dashicons dashicons-phone"></span>
                                Téléphone
                            </label>
                            <div class="field-input">
                                <input
                                    type="tel"
                                    id="company_phone_manual"
                                    name="pdf_builder_company_phone_manual"
                                    value="<?php echo esc_attr(get_pdf_setting('pdf_builder_company_phone_manual')); ?>"
                                    placeholder="+33 1 23 45 67 89"
                                    class="regular-text"
                                    pattern="[\+]?[0-9\s\-\(\)]+"
                                    autocomplete="tel"
                                />
                                <p class="field-description">
                                    Numéro de téléphone de l'entreprise (format international recommandé).
                                </p>
                            </div>
                        </div>

                        <!-- SIRET -->
                        <div class="form-field">
                            <label for="company_siret" class="field-label">
                                <span class="dashicons dashicons-id"></span>
                                Numéro SIRET
                            </label>
                            <div class="field-input">
                                <input
                                    type="text"
                                    id="company_siret"
                                    name="pdf_builder_company_siret"
                                    value="<?php echo esc_attr(get_pdf_setting('pdf_builder_company_siret')); ?>"
                                    placeholder="123 456 789 00012"
                                    class="regular-text"
                                    pattern="[0-9\s]{14,17}"
                                    maxlength="17"
                                    autocomplete="off"
                                />
                                <p class="field-description">
                                    Numéro SIRET à 14 chiffres (espaces autorisés).
                                </p>
                            </div>
                        </div>

                        <!-- TVA -->
                        <div class="form-field">
                            <label for="company_vat" class="field-label">
                                <span class="dashicons dashicons-money"></span>
                                Numéro TVA
                            </label>
                            <div class="field-input">
                                <input
                                    type="text"
                                    id="company_vat"
                                    name="pdf_builder_company_vat"
                                    value="<?php echo esc_attr(get_pdf_setting('pdf_builder_company_vat')); ?>"
                                    placeholder="FR12345678901"
                                    class="regular-text"
                                    pattern="[A-Z]{2}[0-9A-Z]{8,12}"
                                    autocomplete="off"
                                />
                                <p class="field-description">
                                    Numéro TVA européen (2 lettres pays + 8-12 caractères alphanumériques).
                                </p>
                            </div>
                        </div>

                        <!-- RCS -->
                        <div class="form-field">
                            <label for="company_rcs" class="field-label">
                                <span class="dashicons dashicons-building"></span>
                                RCS
                            </label>
                            <div class="field-input">
                                <input
                                    type="text"
                                    id="company_rcs"
                                    name="pdf_builder_company_rcs"
                                    value="<?php echo esc_attr(get_pdf_setting('pdf_builder_company_rcs')); ?>"
                                    placeholder="Lyon B 123 456 789"
                                    class="regular-text"
                                    autocomplete="off"
                                />
                                <p class="field-description">
                                    Numéro RCS et ville d'immatriculation.
                                </p>
                            </div>
                        </div>

                        <!-- Capital social -->
                        <div class="form-field">
                            <label for="company_capital" class="field-label">
                                <span class="dashicons dashicons-chart-line"></span>
                                Capital social
                            </label>
                            <div class="field-input">
                                <input
                                    type="text"
                                    id="company_capital"
                                    name="pdf_builder_company_capital"
                                    value="<?php echo esc_attr(get_pdf_setting('pdf_builder_company_capital')); ?>"
                                    placeholder="10 000 €"
                                    class="regular-text"
                                    pattern="[0-9\s€,\.]+"
                                    autocomplete="off"
                                />
                                <p class="field-description">
                                    Montant du capital social (avec symbole €).
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Zone de prévisualisation -->
                    <div class="preview-section" id="company-preview" style="display: none;">
                        <h4>Aperçu des informations</h4>
                        <div class="preview-content">
                            <!-- Le contenu sera généré par JavaScript -->
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
    /* Styles pour les paramètres généraux */
    .general-settings {
        max-width: none;
    }

    .settings-content {
        display: grid;
        gap: 2rem;
    }

    .settings-card {
        background: #fff;
        border: 1px solid #dcdcde;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid #f0f0f1;
        background: #fafafa;
    }

    .card-title {
        margin: 0 0 0.5rem 0;
        font-size: 1.2em;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .card-title .dashicons {
        color: #2271b1;
    }

    .card-description {
        margin: 0;
        color: #646970;
        font-size: 0.9em;
    }

    .card-content {
        padding: 1.5rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .info-item {
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 4px;
        border-left: 3px solid #2271b1;
    }

    .info-label {
        display: block;
        font-weight: 600;
        color: #1d2327;
        margin-bottom: 0.25rem;
        font-size: 0.9em;
    }

    .info-value {
        color: #2c3338;
        word-break: break-word;
    }

    .text-muted {
        color: #646970;
        font-style: italic;
    }

    .info-notice {
        margin: 0;
        padding: 1rem;
        background: #f0f6fc;
        border-left: 4px solid #0a4b78;
    }

    .info-notice p {
        margin: 0;
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .form-field {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .field-label {
        font-weight: 600;
        color: #1d2327;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .field-label .dashicons {
        color: #2271b1;
        font-size: 1em;
    }

    .field-label.required::after {
        content: '*';
        color: #d63638;
        font-weight: bold;
    }

    .field-input input {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #8c8f94;
        border-radius: 4px;
        font-size: 0.95rem;
        transition: border-color 0.2s ease;
    }

    .field-input input:focus {
        outline: none;
        border-color: #2271b1;
        box-shadow: 0 0 0 2px rgba(34, 113, 177, 0.2);
    }

    .field-description {
        margin: 0.25rem 0 0 0;
        color: #646970;
        font-size: 0.85rem;
        line-height: 1.4;
    }

    .preview-section {
        margin-top: 2rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border: 1px solid #dcdcde;
        border-radius: 4px;
    }

    .preview-section h4 {
        margin: 0 0 1rem 0;
        color: #1d2327;
    }

    /* Responsive */
    @media (max-width: 782px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .card-header,
        .card-content {
            padding: 1rem;
        }
    }
</style>

<script>
    // Validation côté client pour les paramètres généraux
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('general-form');
        if (!form) return;

        // Validation du numéro de téléphone
        const phoneInput = document.getElementById('company_phone_manual');
        if (phoneInput) {
            phoneInput.addEventListener('input', function() {
                const isValid = /^[\+]?[0-9\s\-\(\)]+$/.test(this.value) || this.value === '';
                this.setCustomValidity(isValid ? '' : 'Format de numéro de téléphone invalide');
            });
        }

        // Validation du SIRET
        const siretInput = document.getElementById('company_siret');
        if (siretInput) {
            siretInput.addEventListener('input', function() {
                const cleanValue = this.value.replace(/\s/g, '');
                const isValid = /^[0-9]{14}$/.test(cleanValue) || cleanValue === '';
                this.setCustomValidity(isValid ? '' : 'Le SIRET doit contenir exactement 14 chiffres');
            });
        }

        // Validation du numéro TVA
        const vatInput = document.getElementById('company_vat');
        if (vatInput) {
            vatInput.addEventListener('input', function() {
                const isValid = /^[A-Z]{2}[0-9A-Z]{8,12}$/.test(this.value) || this.value === '';
                this.setCustomValidity(isValid ? '' : 'Format de numéro TVA invalide (2 lettres pays + 8-12 caractères)');
            });
        }

        // Prévisualisation des données (optionnel)
        function updatePreview() {
            const preview = document.getElementById('company-preview');
            if (!preview) return;

            const data = {
                phone: phoneInput?.value || '',
                siret: siretInput?.value || '',
                vat: vatInput?.value || '',
                rcs: document.getElementById('company_rcs')?.value || '',
                capital: document.getElementById('company_capital')?.value || ''
            };

            const hasData = Object.values(data).some(value => value.trim() !== '');
            preview.style.display = hasData ? 'block' : 'none';

            if (hasData) {
                const previewContent = preview.querySelector('.preview-content');
                if (previewContent) {
                    previewContent.innerHTML = `
                        <dl>
                            ${data.phone ? `<dt>Téléphone:</dt><dd>${data.phone}</dd>` : ''}
                            ${data.siret ? `<dt>SIRET:</dt><dd>${data.siret}</dd>` : ''}
                            ${data.vat ? `<dt>TVA:</dt><dd>${data.vat}</dd>` : ''}
                            ${data.rcs ? `<dt>RCS:</dt><dd>${data.rcs}</dd>` : ''}
                            ${data.capital ? `<dt>Capital:</dt><dd>${data.capital}</dd>` : ''}
                        </dl>
                    `;
                }
            }
        }

        // Écouter les changements pour mettre à jour la prévisualisation
        form.addEventListener('input', updatePreview);
        updatePreview(); // Initial update
    });
</script>

