<?php // PDF tab content - Updated: 2025-11-19 01:40:00 ?>

            <h2>📄 Configuration PDF</h2>

            <!-- Section Principale -->
            <section class="pdf-section">
                <h3 style="color: #495057; margin-top: 0; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
                    ⚙️ Paramètres principaux
                </h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_pdf_settings', 'pdf_builder_pdf_settings_nonce'); ?>
                    <input type="hidden" name="current_tab" value="pdf">

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="pdf_quality">Qualité</label></th>
                            <td>
                                <select id="pdf_quality" name="pdf_quality">
                                    <option value="low" <?php selected(get_option('pdf_builder_pdf_quality', 'high'), 'low'); ?>>Rapide (fichiers légers)</option>
                                    <option value="medium" <?php selected(get_option('pdf_builder_pdf_quality', 'high'), 'medium'); ?>>Équilibré</option>
                                    <option value="high" <?php selected(get_option('pdf_builder_pdf_quality', 'high'), 'high'); ?>>Haute qualité</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_page_size">Format de page</label></th>
                            <td>
                                <select id="pdf_page_size" name="pdf_page_size">
                                    <option value="A4" <?php selected(get_option('pdf_builder_pdf_page_size', 'A4'), 'A4'); ?>>A4</option>
                                    <option value="A3" <?php selected(get_option('pdf_builder_pdf_page_size', 'A4'), 'A3'); ?>>A3</option>
                                    <option value="Letter" <?php selected(get_option('pdf_builder_pdf_page_size', 'A4'), 'Letter'); ?>>Letter</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_orientation">Orientation</label></th>
                            <td>
                                <select id="pdf_orientation" name="pdf_orientation">
                                    <option value="portrait" <?php selected(get_option('pdf_builder_pdf_orientation', 'portrait'), 'portrait'); ?>>Portrait</option>
                                    <option value="landscape" <?php selected(get_option('pdf_builder_pdf_orientation', 'portrait'), 'landscape'); ?>>Paysage</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_cache_enabled">Cache activé</label></th>
                            <td>
                                <input type="checkbox" id="pdf_cache_enabled" name="pdf_cache_enabled" value="1" <?php checked(get_option('pdf_builder_pdf_cache_enabled', true)); ?>>
                                <label for="pdf_cache_enabled">Améliorer les performances en mettant en cache les PDF</label>
                            </td>
                        </tr>
                    </table>
                </form>
            </section>

            <!-- Section Avancée (repliable) -->
            <section class="pdf-section">
                <h3 style="color: #495057; margin-top: 30px; border-bottom: 2px solid #6c757d; padding-bottom: 10px; cursor: pointer;" onclick="toggleAdvancedSection()">
                    🔧 Options avancées <span id="advanced-toggle" style="float: right;">▼</span>
                </h3>

                <div id="advanced-section" style="display: none;">
                    <form method="post" action="">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="pdf_compression">Compression</label></th>
                                <td>
                                    <select id="pdf_compression" name="pdf_compression">
                                        <option value="none" <?php selected(get_option('pdf_builder_pdf_compression', 'medium'), 'none'); ?>>Aucune</option>
                                        <option value="medium" <?php selected(get_option('pdf_builder_pdf_compression', 'medium'), 'medium'); ?>>Moyenne</option>
                                        <option value="high" <?php selected(get_option('pdf_builder_pdf_compression', 'medium'), 'high'); ?>>Élevée</option>
                                    </select>
                                    <p class="description">Réduit la taille des fichiers PDF</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="pdf_metadata_enabled">Métadonnées</label></th>
                                <td>
                                    <input type="checkbox" id="pdf_metadata_enabled" name="pdf_metadata_enabled" value="1" <?php checked(get_option('pdf_builder_pdf_metadata_enabled', true)); ?>>
                                    <label for="pdf_metadata_enabled">Inclure titre, auteur et sujet dans les propriétés PDF</label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="pdf_print_optimized">Optimisé impression</label></th>
                                <td>
                                    <input type="checkbox" id="pdf_print_optimized" name="pdf_print_optimized" value="1" <?php checked(get_option('pdf_builder_pdf_print_optimized', true)); ?>>
                                    <label for="pdf_print_optimized">Ajuster les couleurs et la résolution pour l'impression</label>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </section>

            <script>
            function toggleAdvancedSection() {
                const section = document.getElementById('advanced-section');
                const toggle = document.getElementById('advanced-toggle');

                if (section.style.display === 'none') {
                    section.style.display = 'block';
                    toggle.textContent = '▲';
                } else {
                    section.style.display = 'none';
                    toggle.textContent = '▼';
                }
            }
            </script>