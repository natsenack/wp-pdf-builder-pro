<?php // PDF tab content - Updated: 2025-11-19 01:40:00 ?>

            <h2>📄 Configuration PDF</h2>

            <!-- Section Qualité et Performance -->
            <section class="pdf-section">
                <h3 style="color: #495057; margin-top: 0; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
                    ⚡ Qualité & Performance
                </h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_pdf_quality', 'pdf_builder_pdf_quality_nonce'); ?>
                    <input type="hidden" name="current_tab" value="pdf">

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="pdf_quality">Qualité de génération</label></th>
                            <td>
                                <select id="pdf_quality" name="pdf_quality">
                                    <option value="low" <?php selected(get_option('pdf_builder_pdf_quality', 'high'), 'low'); ?>>Faible (rapide, fichiers légers)</option>
                                    <option value="medium" <?php selected(get_option('pdf_builder_pdf_quality', 'high'), 'medium'); ?>>Moyen (équilibre)</option>
                                    <option value="high" <?php selected(get_option('pdf_builder_pdf_quality', 'high'), 'high'); ?>>Élevé (qualité optimale)</option>
                                </select>
                                <p class="description">Impacte la vitesse de génération et la taille des fichiers</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_compression">Compression</label></th>
                            <td>
                                <select id="pdf_compression" name="pdf_compression">
                                    <option value="none" <?php selected(get_option('pdf_builder_pdf_compression', 'medium'), 'none'); ?>>Aucune</option>
                                    <option value="low" <?php selected(get_option('pdf_builder_pdf_compression', 'medium'), 'low'); ?>>Faible</option>
                                    <option value="medium" <?php selected(get_option('pdf_builder_pdf_compression', 'medium'), 'medium'); ?>>Moyenne</option>
                                    <option value="high" <?php selected(get_option('pdf_builder_pdf_compression', 'medium'), 'high'); ?>>Élevée</option>
                                </select>
                                <p class="description">Réduction de la taille des fichiers PDF</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_cache_enabled">Cache PDF activé</label></th>
                            <td>
                                <input type="checkbox" id="pdf_cache_enabled" name="pdf_cache_enabled" value="1" <?php checked(get_option('pdf_builder_pdf_cache_enabled', true)); ?>>
                                <label for="pdf_cache_enabled">Activer le cache pour améliorer les performances</label>
                                <p class="description">Met en cache les PDF générés pour accélérer les régénérations</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </section>

            <!-- Section Format et Mise en page -->
            <section class="pdf-section">
                <h3 style="color: #495057; margin-top: 30px; border-bottom: 2px solid #28a745; padding-bottom: 10px;">
                    📐 Format & Mise en page
                </h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_pdf_layout', 'pdf_builder_pdf_layout_nonce'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="pdf_page_size">Format de page</label></th>
                            <td>
                                <select id="pdf_page_size" name="pdf_page_size">
                                    <option value="A4" <?php selected(get_option('pdf_builder_pdf_page_size', 'A4'), 'A4'); ?>>A4 (210×297mm)</option>
                                    <option value="A3" <?php selected(get_option('pdf_builder_pdf_page_size', 'A4'), 'A3'); ?>>A3 (297×420mm)</option>
                                    <option value="Letter" <?php selected(get_option('pdf_builder_pdf_page_size', 'A4'), 'Letter'); ?>>Letter (8.5×11")</option>
                                    <option value="Legal" <?php selected(get_option('pdf_builder_pdf_page_size', 'A4'), 'Legal'); ?>>Legal (8.5×14")</option>
                                </select>
                                <p class="description">Dimensions standard des pages PDF</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_orientation">Orientation</label></th>
                            <td>
                                <select id="pdf_orientation" name="pdf_orientation">
                                    <option value="portrait" <?php selected(get_option('pdf_builder_pdf_orientation', 'portrait'), 'portrait'); ?>>Portrait</option>
                                    <option value="landscape" <?php selected(get_option('pdf_builder_pdf_orientation', 'portrait'), 'landscape'); ?>>Paysage</option>
                                </select>
                                <p class="description">Orientation des pages PDF</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_margins">Marges (mm)</label></th>
                            <td>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="number" id="pdf_margin_top" name="pdf_margin_top" value="<?php echo esc_attr(get_option('pdf_builder_pdf_margin_top', '15')); ?>" min="0" max="50" style="width: 60px;">
                                    <span>Haut</span>
                                    <input type="number" id="pdf_margin_right" name="pdf_margin_right" value="<?php echo esc_attr(get_option('pdf_builder_pdf_margin_right', '15')); ?>" min="0" max="50" style="width: 60px;">
                                    <span>Droite</span>
                                    <input type="number" id="pdf_margin_bottom" name="pdf_margin_bottom" value="<?php echo esc_attr(get_option('pdf_builder_pdf_margin_bottom', '15')); ?>" min="0" max="50" style="width: 60px;">
                                    <span>Bas</span>
                                    <input type="number" id="pdf_margin_left" name="pdf_margin_left" value="<?php echo esc_attr(get_option('pdf_builder_pdf_margin_left', '15')); ?>" min="0" max="50" style="width: 60px;">
                                    <span>Gauche</span>
                                </div>
                                <p class="description">Marges en millimètres (haut, droite, bas, gauche)</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </section>

            <!-- Section Métadonnées et Sécurité -->
            <section class="pdf-section">
                <h3 style="color: #495057; margin-top: 30px; border-bottom: 2px solid #ffc107; padding-bottom: 10px;">
                    🔒 Métadonnées & Sécurité
                </h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_pdf_metadata', 'pdf_builder_pdf_metadata_nonce'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="pdf_metadata_enabled">Métadonnées PDF</label></th>
                            <td>
                                <input type="checkbox" id="pdf_metadata_enabled" name="pdf_metadata_enabled" value="1" <?php checked(get_option('pdf_builder_pdf_metadata_enabled', true)); ?>>
                                <label for="pdf_metadata_enabled">Inclure les métadonnées (titre, auteur, sujet)</label>
                                <p class="description">Ajoute des informations sur le document dans les propriétés PDF</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_author">Auteur par défaut</label></th>
                            <td>
                                <input type="text" id="pdf_author" name="pdf_author" value="<?php echo esc_attr(get_option('pdf_builder_pdf_author', get_bloginfo('name'))); ?>" class="regular-text">
                                <p class="description">Nom de l'auteur affiché dans les propriétés du PDF</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_subject">Sujet par défaut</label></th>
                            <td>
                                <input type="text" id="pdf_subject" name="pdf_subject" value="<?php echo esc_attr(get_option('pdf_builder_pdf_subject', 'Document généré par PDF Builder Pro')); ?>" class="regular-text">
                                <p class="description">Description du document dans les propriétés PDF</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_protection_enabled">Protection PDF</label></th>
                            <td>
                                <input type="checkbox" id="pdf_protection_enabled" name="pdf_protection_enabled" value="1" <?php checked(get_option('pdf_builder_pdf_protection_enabled', false)); ?>>
                                <label for="pdf_protection_enabled">Activer la protection par mot de passe</label>
                                <p class="description">Protège les PDF avec un mot de passe (option avancée)</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </section>

            <!-- Section Templates et Personnalisation -->
            <section class="pdf-section">
                <h3 style="color: #495057; margin-top: 30px; border-bottom: 2px solid #6f42c1; padding-bottom: 10px;">
                    🎨 Templates & Personnalisation
                </h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_pdf_templates', 'pdf_builder_pdf_templates_nonce'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="pdf_default_template">Template par défaut</label></th>
                            <td>
                                <select id="pdf_default_template" name="pdf_default_template">
                                    <option value="default" <?php selected(get_option('pdf_builder_pdf_default_template', 'default'), 'default'); ?>>Template par défaut</option>
                                    <option value="minimal" <?php selected(get_option('pdf_builder_pdf_default_template', 'default'), 'minimal'); ?>>Template minimal</option>
                                    <option value="professional" <?php selected(get_option('pdf_builder_pdf_default_template', 'default'), 'professional'); ?>>Template professionnel</option>
                                    <option value="modern" <?php selected(get_option('pdf_builder_pdf_default_template', 'default'), 'modern'); ?>>Template moderne</option>
                                </select>
                                <p class="description">Style visuel appliqué par défaut aux nouveaux PDF</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_custom_css">CSS personnalisé</label></th>
                            <td>
                                <textarea id="pdf_custom_css" name="pdf_custom_css" rows="6" cols="50" placeholder="body { font-family: Arial, sans-serif; } .header { color: #007cba; }"><?php echo esc_textarea(get_option('pdf_builder_pdf_custom_css', '')); ?></textarea>
                                <p class="description">CSS personnalisé appliqué à tous les PDF (pour utilisateurs avancés)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_header_footer_enabled">En-tête/Pied de page</label></th>
                            <td>
                                <input type="checkbox" id="pdf_header_footer_enabled" name="pdf_header_footer_enabled" value="1" <?php checked(get_option('pdf_builder_pdf_header_footer_enabled', true)); ?>>
                                <label for="pdf_header_footer_enabled">Activer les en-têtes et pieds de page automatiques</label>
                                <p class="description">Ajoute automatiquement numéro de page, titre, et date</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </section>

            <!-- Section Impression et Accessibilité -->
            <section class="pdf-section">
                <h3 style="color: #495057; margin-top: 30px; border-bottom: 2px solid #dc3545; padding-bottom: 10px;">
                    🖨️ Impression & Accessibilité
                </h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_pdf_printing', 'pdf_builder_pdf_printing_nonce'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="pdf_print_optimized">Optimisé pour l'impression</label></th>
                            <td>
                                <input type="checkbox" id="pdf_print_optimized" name="pdf_print_optimized" value="1" <?php checked(get_option('pdf_builder_pdf_print_optimized', true)); ?>>
                                <label for="pdf_print_optimized">Générer des PDF optimisés pour l'impression</label>
                                <p class="description">Ajuste les couleurs et la résolution pour une meilleure qualité d'impression</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_accessibility_enabled">Accessibilité</label></th>
                            <td>
                                <input type="checkbox" id="pdf_accessibility_enabled" name="pdf_accessibility_enabled" value="1" <?php checked(get_option('pdf_builder_pdf_accessibility_enabled', false)); ?>>
                                <label for="pdf_accessibility_enabled">Générer des PDF accessibles (PDF/UA)</label>
                                <p class="description">Compatible avec les lecteurs d'écran et les standards d'accessibilité</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_bookmarks_enabled">Signets PDF</label></th>
                            <td>
                                <input type="checkbox" id="pdf_bookmarks_enabled" name="pdf_bookmarks_enabled" value="1" <?php checked(get_option('pdf_builder_pdf_bookmarks_enabled', true)); ?>>
                                <label for="pdf_bookmarks_enabled">Ajouter une table des matières avec signets</label>
                                <p class="description">Facilite la navigation dans les PDF longs</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </section>

            <!-- Boutons de sauvegarde -->
            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center;">
                <button type="button" name="submit_pdf_settings" class="button button-primary button-large floating-save-btn" style="margin-right: 10px;">
                    💾 Sauvegarder la configuration PDF
                </button>
                <button type="button" onclick="if(confirm('Réinitialiser tous les paramètres PDF ?')) { document.querySelectorAll('form').forEach(form => form.reset()); }" class="button button-secondary">
                    🔄 Réinitialiser
                </button>
            </div>