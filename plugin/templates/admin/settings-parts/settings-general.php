<?php
/**
 * General tab content
 * Updated: 2025-11-18 20:15:00
 */
?>
            <h2>🏠 Paramètres Généraux</h2>

            <!-- Sub-tabs for General settings -->
            <nav class="nav-tab-wrapper wp-clearfix sub-tabs" id="general-sub-tabs">
                <a href="#general-company" class="nav-tab nav-tab-active" data-tab="general-company">🏢 Entreprise</a>
                <a href="#general-css" class="nav-tab" data-tab="general-css">🎨 CSS</a>
                <a href="#general-html" class="nav-tab" data-tab="general-html">📄 HTML</a>
            </nav>

            <section id="general-tab-content" class="tab-content-wrapper">
                <!-- Company Information Sub-tab -->
                <div id="general-company" class="tab-content active">
                    <div class="tab-content-inner">
                        <!-- Section Informations Entreprise -->
                        <section class="general-section">
                            <h3>🏢 Informations Entreprise</h3>

                            <form method="post" action="">
                                <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_company_nonce'); ?>
                                <input type="hidden" name="current_tab" value="general">
                                <!-- Le bouton submit est supprimé car on utilise le système AJAX global -->

                                <article style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                    <h4 style="margin-top: 0; color: #155724; font-size: 16px;">📋 Informations récupérées automatiquement de WooCommerce</h4>
                                    <div style="background: #f8f9fa; padding: 12px; border-radius: 6px; margin-bottom: 15px;">
                                        <p style="margin: 3px 0;"><strong>Nom de l'entreprise :</strong> <?php echo esc_html(get_option('woocommerce_store_name', get_bloginfo('name'))); ?></p>
                                        <p style="margin: 3px 0;"><strong>Adresse complète :</strong> <?php
                                        $address = get_option('woocommerce_store_address', '');
                                        $city = get_option('woocommerce_store_city', '');
                                        $postcode = get_option('woocommerce_store_postcode', '');
                                        $country = get_option('woocommerce_default_country', '');
                                        $full_address = array_filter([$address, $city, $postcode, $country]);
                                        echo esc_html(implode(', ', $full_address) ?: '<em>Non défini</em>');
                                        ?></p>
                                        <p style="margin: 3px 0;"><strong>Email :</strong> <?php echo esc_html(get_option('admin_email', '<em>Non défini</em>')); ?></p>
                                        <p style="color: #666; font-size: 12px; margin: 8px 0 0 0;">
                                        ℹ️ Ces informations sont automatiquement récupérées depuis les paramètres WooCommerce (WooCommerce > Réglages > Général).
                                        </p>
                                    </div>

                                    <h4 style="color: #dc3545;">📝 Informations à saisir manuellement</h4>
                                    <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
                                    Ces informations ne sont pas disponibles dans WooCommerce et doivent être saisies manuellement :
                                    </p>

                                    <table class="form-table">
                                        <tr>
                                            <th scope="row"><label for="company_phone_manual">Téléphone</label></th>
                                            <td>
                                                <input type="text" id="company_phone_manual" name="company_phone_manual"
                                                    value="<?php echo esc_attr($company_phone_manual); ?>"
                                                    placeholder="+33 1 23 45 67 89" />
                                                    <p class="description">Téléphone de l'entreprise</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label for="company_siret">Numéro SIRET</label></th>
                                            <td>
                                                <input type="text" id="company_siret" name="company_siret"
                                                    value="<?php echo esc_attr(get_option('pdf_builder_company_siret', '')); ?>"
                                                    placeholder="123 456 789 00012" />
                                                <p class="description">Numéro SIRET de l'entreprise</p>
                                                <div class="company-siret-preview" style="color: #666; font-size: 12px; margin-top: 4px;"></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label for="company_vat">Numéro TVA</label></th>
                                            <td>
                                                <input type="text" id="company_vat" name="company_vat"
                                                    value="<?php echo esc_attr(get_option('pdf_builder_company_vat', '')); ?>"
                                                    placeholder="FR12345678901, DE123456789, BE0123456789" />
                                                <p class="description">Numéro de TVA intracommunautaire (format européen : 2 lettres pays + 8-12 caractères)</p>
                                                <div class="company-vat-preview" style="color: #666; font-size: 12px; margin-top: 4px;"></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label for="company_rcs">RCS</label></th>
                                            <td>
                                                <input type="text" id="company_rcs" name="company_rcs"
                                                    value="<?php echo esc_attr(get_option('pdf_builder_company_rcs', '')); ?>"
                                                    placeholder="Lyon B 123 456 789" />
                                                <p class="description">Numéro RCS (Registre du Commerce et des Sociétés)</p>
                                                <div class="company-rcs-preview" style="color: #666; font-size: 12px; margin-top: 4px;"></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label for="company_capital">Capital social</label></th>
                                            <td>
                                                <input type="text" id="company_capital" name="company_capital"
                                                    value="<?php echo esc_attr(get_option('pdf_builder_company_capital', '')); ?>"
                                                    placeholder="10 000 €" />
                                                <p class="description">Montant du capital social de l'entreprise</p>
                                                <div class="company-capital-preview" style="color: #666; font-size: 12px; margin-top: 4px;"></div>
                                            </td>
                                        </tr>
                                    </table>
                                </article>
                            </form>
                        </section>
                    </div>
                </div>

                <!-- CSS Customization Sub-tab -->
                <div id="general-css" class="tab-content">
                    <div class="tab-content-inner">
                        <section class="general-section">
                            <h3>🎨 Personnalisation CSS</h3>

                            <form method="post" action="">
                                <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_css_nonce'); ?>
                                <input type="hidden" name="current_tab" value="general-css">

                                <article style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                    <h4 style="margin-top: 0; color: #007cba; font-size: 16px;">📝 Styles CSS personnalisés</h4>
                                    <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
                                    Ajoutez vos propres styles CSS pour personnaliser l'apparence de vos PDFs. Ces styles seront appliqués à tous les documents générés.
                                    </p>

                                    <table class="form-table">
                                        <tr>
                                            <th scope="row"><label for="pdf_builder_custom_css">CSS personnalisé</label></th>
                                            <td>
                                                <textarea id="pdf_builder_custom_css" name="pdf_builder_custom_css" rows="10" cols="50" style="width: 100%; font-family: monospace;"><?php echo esc_textarea(get_option('pdf_builder_custom_css', '')); ?></textarea>
                                                <p class="description">Entrez votre CSS personnalisé ici. Utilisez des sélecteurs spécifiques pour cibler les éléments PDF.</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label for="pdf_builder_css_enabled">Activer le CSS personnalisé</label></th>
                                            <td>
                                                <label class="toggle-switch">
                                                    <input type="checkbox" id="pdf_builder_css_enabled" name="pdf_builder_css_enabled" value="1" <?php checked(get_option('pdf_builder_css_enabled', false)); ?>>
                                                    <span class="toggle-slider"></span>
                                                </label>
                                                <p class="description">Cochez pour activer l'application du CSS personnalisé aux PDFs générés.</p>
                                            </td>
                                        </tr>
                                    </table>
                                </article>
                            </form>
                        </section>
                    </div>
                </div>

                <!-- HTML Customization Sub-tab -->
                <div id="general-html" class="tab-content">
                    <div class="tab-content-inner">
                        <section class="general-section">
                            <h3>📄 Personnalisation HTML</h3>

                            <form method="post" action="">
                                <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_html_nonce'); ?>
                                <input type="hidden" name="current_tab" value="general-html">

                                <article style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                    <h4 style="margin-top: 0; color: #28a745; font-size: 16px;">📝 Templates HTML personnalisés</h4>
                                    <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
                                    Définissez des templates HTML personnalisés pour différents types de documents PDF. Utilisez des variables comme {company_name}, {invoice_number}, etc.
                                    </p>

                                    <table class="form-table">
                                        <tr>
                                            <th scope="row"><label for="pdf_builder_invoice_template">Template Facture</label></th>
                                            <td>
                                                <textarea id="pdf_builder_invoice_template" name="pdf_builder_invoice_template" rows="8" cols="50" style="width: 100%; font-family: monospace;"><?php echo esc_textarea(get_option('pdf_builder_invoice_template', '')); ?></textarea>
                                                <p class="description">Template HTML pour les factures. Laissez vide pour utiliser le template par défaut.</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label for="pdf_builder_quote_template">Template Devis</label></th>
                                            <td>
                                                <textarea id="pdf_builder_quote_template" name="pdf_builder_quote_template" rows="8" cols="50" style="width: 100%; font-family: monospace;"><?php echo esc_textarea(get_option('pdf_builder_quote_template', '')); ?></textarea>
                                                <p class="description">Template HTML pour les devis. Laissez vide pour utiliser le template par défaut.</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label for="pdf_builder_html_enabled">Activer les templates HTML personnalisés</label></th>
                                            <td>
                                                <label class="toggle-switch">
                                                    <input type="checkbox" id="pdf_builder_html_enabled" name="pdf_builder_html_enabled" value="1" <?php checked(get_option('pdf_builder_html_enabled', false)); ?>>
                                                    <span class="toggle-slider"></span>
                                                </label>
                                                <p class="description">Cochez pour activer l'utilisation des templates HTML personnalisés.</p>
                                            </td>
                                        </tr>
                                    </table>
                                </article>
                            </form>
                        </section>
                    </div>
                </div>
            </section>

