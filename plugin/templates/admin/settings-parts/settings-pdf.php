<?php // PDF tab content - Updated: 2025-11-19 01:40:00

/**
 * Safe wrapper for get_option that works even when WordPress is not fully loaded
 */
function pdf_builder_safe_get_option($option, $default = '') {
    if (function_exists('get_option')) {
        return pdf_builder_safe_get_option($option, $default);
    }
    return $default;
}

/**
 * Safe wrapper for checked function
 */
function pdf_builder_safe_checked($checked, $current = true, $echo = true) {
    if (function_exists('checked')) {
        return pdf_builder_safe_checked($checked, $current, $echo);
    }
    $result = pdf_builder_safe_checked($checked, $current, false);
    if ($echo) echo $result;
    return $result;
}

/**
 * Safe wrapper for selected function
 */
function pdf_builder_safe_selected($selected, $current = true, $echo = true) {
    if (function_exists('selected')) {
        return pdf_builder_safe_selected($selected, $current, $echo);
    }
    $result = pdf_builder_safe_selected($selected, $current, false);
    if ($echo) echo $result;
    return $result;
}
?>

            <h2>📄 Configuration PDF</h2>

            <!-- Formulaire unique pour tout l'onglet PDF -->
            <form id="pdf-settings-form" method="post" action="">
                <?php wp_nonce_field('pdf_builder_save_settings', 'pdf_builder_pdf_nonce'); ?>
                <input type="hidden" name="current_tab" value="pdf">

                <!-- Section Principale -->
                <section class="pdf-section">
                    <h3 style="color: #495057; margin-top: 0; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
                        ⚙️ Paramètres principaux
                    </h3>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="pdf_quality">Qualité</label></th>
                            <td>
                                <select id="pdf_quality" name="pdf_quality">
                                    <option value="low" <?php pdf_builder_safe_selected(pdf_builder_safe_get_option('pdf_builder_pdf_quality', 'high'), 'low'); ?>>Rapide (fichiers légers)</option>
                                    <option value="medium" <?php pdf_builder_safe_selected(pdf_builder_safe_get_option('pdf_builder_pdf_quality', 'high'), 'medium'); ?>>Équilibré</option>
                                    <option value="high" <?php pdf_builder_safe_selected(pdf_builder_safe_get_option('pdf_builder_pdf_quality', 'high'), 'high'); ?>>Haute qualité</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="default_format">Format de page</label></th>
                            <td>
                                <select id="default_format" name="default_format">
                                    <option value="A4" <?php pdf_builder_safe_selected(pdf_builder_safe_get_option('pdf_builder_default_format', 'A4'), 'A4'); ?>>A4</option>
                                    <option value="A3" <?php pdf_builder_safe_selected(pdf_builder_safe_get_option('pdf_builder_default_format', 'A4'), 'A3'); ?> disabled title="Bientôt disponible">A3 (soon)</option>
                                    <option value="Letter" <?php pdf_builder_safe_selected(pdf_builder_safe_get_option('pdf_builder_default_format', 'A4'), 'Letter'); ?> disabled title="Bientôt disponible">Letter (soon)</option>
                                </select>
                                <p class="description" style="margin-top:6px; color:#6c757d; font-size:12px;">Les formats A3 et Letter sont prévus; sélection désactivée pour l'instant.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="default_orientation">Orientation</label></th>
                            <td>
                                <select id="default_orientation" name="default_orientation">
                                    <option value="portrait" <?php pdf_builder_safe_selected(pdf_builder_safe_get_option('pdf_builder_default_orientation', 'portrait'), 'portrait'); ?>>Portrait</option>
                                    <option value="landscape" <?php pdf_builder_safe_selected(pdf_builder_safe_get_option('pdf_builder_default_orientation', 'portrait'), 'landscape'); ?>>Paysage</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_builder_cache_enabled">Cache activé</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="pdf_builder_cache_enabled" name="pdf_builder_cache_enabled" value="1" <?php pdf_builder_safe_checked(pdf_builder_safe_get_option('pdf_builder_cache_enabled', false)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Améliorer les performances en mettant en cache les PDF</p>
                            </td>
                        </tr>
                    </table>
                </section>

                <!-- Section Avancée (repliable) -->
                <section class="pdf-section">
                    <h3 style="color: #495057; margin-top: 30px; border-bottom: 2px solid #6c757d; padding-bottom: 10px; cursor: pointer;" onclick="toggleAdvancedSection()">
                        🔧 Options avancées <span id="advanced-toggle" style="float: right;">▼</span>
                    </h3>

                    <section id="advanced-section" style="display: none;">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="pdf_compression">Compression</label></th>
                                <td>
                                    <select id="pdf_compression" name="pdf_compression">
                                        <option value="none" <?php pdf_builder_safe_selected(pdf_builder_safe_get_option('pdf_builder_pdf_compression', 'medium'), 'none'); ?>>Aucune</option>
                                        <option value="medium" <?php pdf_builder_safe_selected(pdf_builder_safe_get_option('pdf_builder_pdf_compression', 'medium'), 'medium'); ?>>Moyenne</option>
                                        <option value="high" <?php pdf_builder_safe_selected(pdf_builder_safe_get_option('pdf_builder_pdf_compression', 'medium'), 'high'); ?>>Élevée</option>
                                    </select>
                                    <p class="description">Réduit la taille des fichiers PDF</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="pdf_metadata_enabled">Métadonnées</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="pdf_metadata_enabled" name="pdf_metadata_enabled" value="1" <?php pdf_builder_safe_checked(pdf_builder_safe_get_option('pdf_builder_pdf_metadata_enabled', true)); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Inclure titre, auteur et sujet dans les propriétés PDF</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="pdf_print_optimized">Optimisé impression</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="pdf_print_optimized" name="pdf_print_optimized" value="1" <?php pdf_builder_safe_checked(pdf_builder_safe_get_option('pdf_builder_pdf_print_optimized', true)); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Ajuster les couleurs et la résolution pour l'impression</p>
                                </td>
                            </tr>
                        </table>
                    </section>
                </section>
            </form>

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
