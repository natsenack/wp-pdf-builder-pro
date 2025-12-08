<?php // PDF tab content - Updated: 2025-11-19 01:40:00

// require_once __DIR__ . '/settings-helpers.php'; // REMOVED - settings-helpers.php deleted

$settings = get_option('pdf_builder_settings', array());
?>

            <h2>📄 Configuration PDF</h2>

            <!-- Formulaire unique pour tout l'onglet PDF -->
            <form id="pdf-settings-form" method="post" action="">
                <?php wp_nonce_field('pdf_builder_save_settings', 'pdf_builder_pdf_nonce'); ?>
                <input type="hidden" name="current_tab" value="pdf">

                <!-- Section Principale -->
                <section id="pdf" class="pdf-section">
                    <h3 style="color: #495057; margin-top: 0; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
                        ⚙️ Paramètres principaux
                    </h3>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="pdf_quality">Qualité</label></th>
                            <td>
                                <select id="pdf_quality" name="pdf_builder_pdf_quality">
                                    <option value="low" <?php selected($settings['pdf_builder_pdf_quality'] ?? 'high', 'low'); ?>>Rapide (fichiers légers)</option>
                                    <option value="medium" <?php selected($settings['pdf_builder_pdf_quality'] ?? 'high', 'medium'); ?>>Équilibré</option>
                                    <option value="high" <?php selected($settings['pdf_builder_pdf_quality'] ?? 'high', 'high'); ?>>Haute qualité</option>
                                </select>
                            </td>
                        </tr>
                        <script>
                    </table>
                </section>

                <!-- Section Avancée (repliable) -->
                <section id="pdf" class="pdf-section">
                    <h3 style="color: #495057; margin-top: 30px; border-bottom: 2px solid #6c757d; padding-bottom: 10px; cursor: pointer;" onclick="PDFBuilderTabsAPI.toggleAdvancedSection()">
                        🔧 Options avancées <span id="advanced-toggle" style="float: right;">▼</span>
                    </h3>

                    <section id="advanced-section" class="hidden-element">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="pdf_compression">Compression</label></th>
                                <td>
                                    <select id="pdf_compression" name="pdf_builder_pdf_compression">
                                        <option value="none" <?php selected($settings['pdf_builder_pdf_compression'] ?? 'medium', 'none'); ?>>Aucune</option>
                                        <option value="medium" <?php selected($settings['pdf_builder_pdf_compression'] ?? 'medium', 'medium'); ?>>Moyenne</option>
                                        <option value="high" <?php selected($settings['pdf_builder_pdf_compression'] ?? 'medium', 'high'); ?>>Élevée</option>
                                    </select>
                                    <p class="description">Réduit la taille des fichiers PDF</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="pdf_metadata_enabled">Métadonnées</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="pdf_metadata_enabled" name="pdf_builder_pdf_metadata_enabled" value="1" <?php checked($settings['pdf_builder_pdf_metadata_enabled'] ?? '1', '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Inclure titre, auteur et sujet dans les propriétés PDF</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="pdf_print_optimized">Optimisé impression</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="pdf_print_optimized" name="pdf_builder_pdf_print_optimized" value="1" <?php checked($settings['pdf_builder_pdf_print_optimized'] ?? '1', '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Ajuster les couleurs et la résolution pour l'impression</p>
                                </td>
                            </tr>
                        </table>
                    </section>
                </section>
            </form>

            <!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->

