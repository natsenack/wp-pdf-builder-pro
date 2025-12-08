<?php
/**
 * General tab content
 * Updated: 2025-12-02 21:20:00
 */

// require_once __DIR__ . '/../settings-helpers.php'; // REMOVED - settings-helpers.php deleted
?>
            <h2>🏠 Paramètres Généraux</h2>

            <!-- Pas de sous-onglets pour la page générale, juste le contenu direct -->
            <section id="general" class="general-section">
                <h3>🏢 Informations Entreprise</h3>

                <form method="post" action="" id="general-form">
                    <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_company_nonce'); ?>
                    <input type="hidden" name="current_tab" value="general">
                    <!-- Le bouton submit est supprimé car on utilise le système AJAX global -->

                    <article class="general-info-box">
                        <h4 class="general-info-title">📋 Informations récupérées automatiquement de WooCommerce</h4>
                        <div class="general-info-content">
                            <p><strong>Nom de l'entreprise :</strong> <?php echo esc_html(get_option('woocommerce_store_name', get_bloginfo('name'))); ?></p>
                            <p><strong>Adresse complète :</strong> <?php
                            $address = get_option('woocommerce_store_address', '');
                            $city = get_option('woocommerce_store_city', '');
                            $postcode = get_option('woocommerce_store_postcode', '');
                            $country = get_option('woocommerce_default_country', '');
                            $full_address = array_filter([$address, $city, $postcode, $country]);
                            echo esc_html(implode(', ', $full_address) ?: '<em>Non défini</em>');
                            ?></p>
                            <p><strong>Email :</strong> <?php echo esc_html(get_option('admin_email', '<em>Non défini</em>')); ?></p>
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
                                    <input type="text" id="company_phone_manual" name="pdf_builder_company_phone_manual"
                                        value="<?php 
                                        $settings = get_option('pdf_builder_settings', []);
                                        $phone_value = $settings['pdf_builder_company_phone_manual'] ?? '';
                                        // error_log("PDF Builder Template: company_phone_manual value = '{$phone_value}'");
                                        echo esc_attr($phone_value); 
                                        ?>"
                                        placeholder="+33 1 23 45 67 89" />
                                        <p class="description">Téléphone de l'entreprise</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="company_siret">Numéro SIRET</label></th>
                                <td>
                                    <input type="text" id="company_siret" name="pdf_builder_company_siret"
                                        value="<?php echo esc_attr($settings['pdf_builder_company_siret'] ?? ''); ?>"
                                        placeholder="123 456 789 00012" />
                                    <p class="description">Numéro SIRET de l'entreprise</p>
                                    <div class="company-preview"></div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="company_vat">Numéro TVA</label></th>
                                <td>
                                    <input type="text" id="company_vat" name="pdf_builder_company_vat"
                                        value="<?php echo esc_attr($settings['pdf_builder_company_vat'] ?? ''); ?>"
                                        placeholder="FR12345678901, DE123456789, BE0123456789" />
                                    <p class="description">Numéro TVA européen (2 lettres pays + 8-12 caractères)</p>
                                    <div class="company-preview"></div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="company_rcs">RCS</label></th>
                                <td>
                                    <input type="text" id="company_rcs" name="pdf_builder_company_rcs"
                                        value="<?php echo esc_attr($settings['pdf_builder_company_rcs'] ?? ''); ?>"
                                        placeholder="Lyon B 123 456 789" />
                                    <p class="description">Numéro RCS de l'entreprise</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="company_capital">Capital social</label></th>
                                <td>
                                    <input type="text" id="company_capital" name="pdf_builder_company_capital"
                                        value="<?php echo esc_attr($settings['pdf_builder_company_capital'] ?? ''); ?>"
                                        placeholder="10 000 €" />
                                    <p class="description">Capital social de l'entreprise</p>
                                </td>
                            </tr>
                        </table>
                    </article>

                    <article class="general-defaults-box">
                        <h4 class="general-defaults-title">⚙️ Paramètres par défaut</h4>
                        <p class="general-defaults-description">
                        Ces paramètres définissent les valeurs par défaut pour tous vos PDFs :
                        </p>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="default_format">Format de page par défaut</label></th>
                                <td>
                                    <select id="default_format" name="pdf_builder_default_format">
                                        <option value="A4" <?php selected($settings['pdf_builder_default_format'] ?? 'A4', 'A4'); ?>>A4</option>
                                        <option value="A3" <?php selected($settings['pdf_builder_default_format'] ?? 'A4', 'A3'); ?> disabled title="Bientôt disponible">A3 (soon)</option>
                                        <option value="Letter" <?php selected($settings['pdf_builder_default_format'] ?? 'A4', 'Letter'); ?> disabled title="Bientôt disponible">Letter (soon)</option>
                                    </select>
                                    <p class="description">Format de page par défaut pour les nouveaux PDFs</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="default_orientation">Orientation par défaut</label></th>
                                <td>
                                    <select id="default_orientation" name="pdf_builder_default_orientation">
                                        <option value="portrait" <?php selected($settings['pdf_builder_default_orientation'] ?? 'portrait', 'portrait'); ?>>Portrait</option>
                                        <option value="landscape" <?php selected($settings['pdf_builder_default_orientation'] ?? 'portrait', 'landscape'); ?>>Paysage</option>
                                    </select>
                                    <p class="description">Orientation par défaut pour les nouveaux PDFs</p>
                                </td>
                            </tr>
                        </table>
                    </article>
                </form>
            </section>

