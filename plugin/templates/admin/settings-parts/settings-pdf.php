<?php // PDF tab content - Updated: 2025-11-19 01:40:00

// require_once __DIR__ . '/settings-helpers.php'; // REMOVED - settings-helpers.php deleted

$settings = get_option('pdf_builder_settings', array());
error_log('[PDF Builder] settings-pdf.php loaded - settings count: ' . count($settings));
?>



            <!-- Section Principale -->
            <section id="pdf" class="pdf-section contenu-canvas-section">
            <h3 class="" style="display: flex; justify-content: flex-start; align-items: center;">
                <span>📄 Configuration PDF</span>
            </h3>
                <h3 style="color: #495057; margin-top: 0; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
                    ⚙️ Paramètres principaux
                </h3>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="pdf_quality">Qualité</label></th>
                        <td>
                            <select id="pdf_quality" name="pdf_builder_settings[pdf_builder_pdf_quality]">
                                    <option value="low" <?php selected($settings['pdf_builder_pdf_quality'] ?? 'high', 'low'); ?>>Rapide (fichiers légers)</option>
                                    <option value="medium" <?php selected($settings['pdf_builder_pdf_quality'] ?? 'high', 'medium'); ?>>Équilibré</option>
                                    <option value="high" <?php selected($settings['pdf_builder_pdf_quality'] ?? 'high', 'high'); ?>>Haute qualité</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="default_format">Format de page</label></th>
                            <td>
                                <select id="default_format" name="pdf_builder_settings[pdf_builder_default_format]">
                                    <option value="A4" <?php selected($settings['pdf_builder_default_format'] ?? 'A4', 'A4'); ?>>A4</option>
                                    <option value="A3" <?php selected($settings['pdf_builder_default_format'] ?? 'A4', 'A3'); ?> disabled title="Bientôt disponible">A3 (soon)</option>
                                    <option value="Letter" <?php selected($settings['pdf_builder_default_format'] ?? 'A4', 'Letter'); ?> disabled title="Bientôt disponible">Letter (soon)</option>
                                </select>
                                <p class="description" style="margin-top:6px; color:#6c757d; font-size:12px;">Les formats A3 et Letter sont prévus; sélection désactivée pour l'instant.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="default_orientation">Orientation</label></th>
                            <td>
                                <select id="default_orientation" name="pdf_builder_settings[pdf_builder_default_orientation]">
                                    <option value="portrait" <?php selected($settings['pdf_builder_default_orientation'] ?? 'portrait', 'portrait'); ?>>Portrait</option>
                                    <option value="landscape" <?php selected($settings['pdf_builder_default_orientation'] ?? 'portrait', 'landscape'); ?>>Paysage</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_builder_pdf_cache_enabled">Cache activé</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_settings[pdf_builder_pdf_cache_enabled]" value="0">
                                    <input type="checkbox" id="pdf_builder_pdf_cache_enabled" name="pdf_builder_settings[pdf_builder_pdf_cache_enabled]" value="1" <?php checked($settings['pdf_builder_pdf_cache_enabled'] ?? '0', '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Améliorer les performances en mettant en cache les PDF</p>
                            </td>
                        </tr>
                        <script>
                            // PDF Cache toggle functionality
                            (function() {
                                const pdfCacheInput = document.getElementById('pdf_builder_pdf_cache_enabled');
                                const pdfCacheLabel = pdfCacheInput ? pdfCacheInput.closest('label') : null;
                                const pdfCacheSlider = pdfCacheLabel ? pdfCacheLabel.querySelector('.toggle-slider') : null;
                                
                                if (pdfCacheInput && pdfCacheLabel && pdfCacheSlider) {
                                    // Make slider clickable
                                    pdfCacheSlider.style.pointerEvents = 'auto';
                                    pdfCacheSlider.style.cursor = 'pointer';
                                    
                                    // Handle slider clicks
                                    pdfCacheSlider.addEventListener('click', function(e) {
                                        e.stopPropagation();
                                        pdfCacheInput.dataset.sliderClicked = 'true';
                                        pdfCacheInput.checked = !pdfCacheInput.checked;
                                        pdfCacheInput.dispatchEvent(new Event('change', { bubbles: true }));
                                    });
                                    
                                    // Handle label clicks (prevent double toggle)
                                    pdfCacheLabel.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        if (pdfCacheInput.dataset.sliderClicked) {
                                            delete pdfCacheInput.dataset.sliderClicked;
                                            return;
                                        }
                                        pdfCacheInput.checked = !pdfCacheInput.checked;
                                        pdfCacheInput.dispatchEvent(new Event('change', { bubbles: true }));
                                    });
                                }
                            })();
                        </script>
                    </table>
                </section>

                <!-- Section Avancée (repliable) -->
                <section id="pdf" class="pdf-section">
                    <h3 id="advanced-section-toggle" style="color: #495057; margin-top: 30px; border-bottom: 2px solid #6c757d; padding-bottom: 10px; cursor: pointer;">
                        🔧 Options avancées <span id="advanced-toggle" style="float: right;">▼</span>
                    </h3>

                    <script>
                    console.log('Setting up advanced section toggle handler...');

                    var retryCount = 0;
                    var maxRetries = 30; // Increased to 30 attempts (6 seconds)

                    function attachToggleHandler() {
                        retryCount++;
                        console.log('Attempt', retryCount, 'of', maxRetries);

                        var toggleElement = document.getElementById('advanced-section-toggle');
                        console.log('Toggle element:', toggleElement ? 'found' : 'NOT FOUND');

                        if (window.PDFBuilderTabsAPI) {
                            console.log('PDFBuilderTabsAPI found:', typeof window.PDFBuilderTabsAPI);
                            console.log('toggleAdvancedSection function:', typeof window.PDFBuilderTabsAPI.toggleAdvancedSection);
                        } else {
                            console.log('PDFBuilderTabsAPI: NOT FOUND');
                        }

                        if (toggleElement && window.PDFBuilderTabsAPI && typeof window.PDFBuilderTabsAPI.toggleAdvancedSection === 'function') {
                            toggleElement.addEventListener('click', function() {
                                console.log('Toggle clicked - calling API');
                                window.PDFBuilderTabsAPI.toggleAdvancedSection();
                            });
                            console.log('✅ Handler attached successfully on attempt', retryCount);
                        } else if (retryCount >= maxRetries) {
                            console.error('❌ Failed to attach handler after', maxRetries, 'attempts');
                            console.error('Final state:', {
                                toggleElement: !!toggleElement,
                                PDFBuilderTabsAPI: !!window.PDFBuilderTabsAPI,
                                toggleFunction: !!(window.PDFBuilderTabsAPI && window.PDFBuilderTabsAPI.toggleAdvancedSection)
                            });
                        } else {
                            console.log('⏳ Retrying in 200ms... (attempt', retryCount + 1, 'of', maxRetries, ')');
                            setTimeout(attachToggleHandler, 200);
                        }
                    }

                    // Start immediately
                    attachToggleHandler();
                    </script>

                    <section id="advanced-section" class="hidden-element">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="pdf_compression">Compression</label></th>
                                <td>
                                    <select id="pdf_compression" name="pdf_builder_settings[pdf_builder_pdf_compression]">
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
                                        <input type="hidden" name="pdf_builder_settings[pdf_builder_pdf_metadata_enabled]" value="0">
                                        <input type="checkbox" id="pdf_metadata_enabled" name="pdf_builder_settings[pdf_builder_pdf_metadata_enabled]" value="1" <?php checked($settings['pdf_builder_pdf_metadata_enabled'] ?? '1', '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Inclure titre, auteur et sujet dans les propriétés PDF</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="pdf_print_optimized">Optimisé impression</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="hidden" name="pdf_builder_settings[pdf_builder_pdf_print_optimized]" value="0">
                                        <input type="checkbox" id="pdf_print_optimized" name="pdf_builder_settings[pdf_builder_pdf_print_optimized]" value="1" <?php checked($settings['pdf_builder_pdf_print_optimized'] ?? '1', '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Ajuster les couleurs et la résolution pour l'impression</p>
                                </td>
                            </tr>
                        </table>
                    </section>
                </section>

            <!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->

