<?php // PDF tab content - Updated: 2025-11-19 01:40:00

// Inclure les fonctions helper nécessaires pour tous les onglets
require_once __DIR__ . '/settings-helpers.php';

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
                        <tr>
                            <th scope="row"><label for="default_format">Format de page</label></th>
                            <td>
                                <select id="default_format" name="pdf_builder_default_format">
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
                                <select id="default_orientation" name="pdf_builder_default_orientation">
                                    <option value="portrait" <?php selected($settings['pdf_builder_default_orientation'] ?? 'portrait', 'portrait'); ?>>Portrait</option>
                                    <option value="landscape" <?php selected($settings['pdf_builder_default_orientation'] ?? 'portrait', 'landscape'); ?>>Paysage</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_builder_cache_enabled">Cache activé</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="pdf_builder_cache_enabled" name="pdf_builder_cache_enabled" value="1" <?php checked($settings['pdf_builder_cache_enabled'] ?? '0', '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Améliorer les performances en mettant en cache les PDF</p>
                            </td>
                        </tr>
                        <script>
                            // Debug and fix toggle functionality for PDF cache - Updated: 2025-12-06
                            console.log('PDF Toggle script starting...');
                            
                            // Run immediately since the tab content is loaded dynamically
                            function setupPDFToggle() {
                                console.log('Setting up PDF toggle immediately');
                                
                                const pdfCacheInput = document.getElementById('pdf_builder_cache_enabled');
                                const pdfCacheLabel = pdfCacheInput ? pdfCacheInput.closest('label') : null;
                                const pdfCacheSlider = pdfCacheLabel ? pdfCacheLabel.querySelector('.toggle-slider') : null;
                                
                                console.log('PDF Input found:', pdfCacheInput);
                                console.log('PDF Label found:', pdfCacheLabel);
                                console.log('PDF Slider found:', pdfCacheSlider);
                                
                                if (pdfCacheInput && pdfCacheLabel && pdfCacheSlider) {
                                    console.log('Setting up PDF toggle...');
                                    
                                    // Ensure input is properly hidden
                                    pdfCacheInput.style.opacity = '0';
                                    // pdfCacheInput.style.pointerEvents = 'none'; // Removed to allow default label behavior
                                    
                                    // Make sure slider is clickable
                                    pdfCacheSlider.style.pointerEvents = 'auto';
                                    pdfCacheSlider.style.cursor = 'pointer';
                                    
                                    // Handle clicks on slider
                                    pdfCacheSlider.addEventListener('click', function(e) {
                                        e.stopPropagation();
                                        console.log('PDF Slider clicked, current state:', pdfCacheInput.checked);
                                        pdfCacheInput.checked = !pdfCacheInput.checked;
                                        pdfCacheInput.dispatchEvent(new Event('change', { bubbles: true }));
                                        console.log('PDF Cache enabled toggled to:', pdfCacheInput.checked);
                                    });
                                    
                                    // Handle clicks on label (but let default behavior work)
                                    pdfCacheLabel.addEventListener('click', function(e) {
                                        console.log('PDF Label clicked, current state:', pdfCacheInput.checked);
                                        // Don't prevent default, let the browser handle the checkbox toggle
                                        setTimeout(function() {
                                            console.log('PDF Cache enabled after label click:', pdfCacheInput.checked);
                                            pdfCacheInput.dispatchEvent(new Event('change', { bubbles: true }));
                                        }, 10);
                                    });
                                    
                                    // Debug change event
                                    pdfCacheInput.addEventListener('change', function() {
                                        console.log('PDF Cache enabled INPUT changed to:', this.checked);
                                    });
                                    
                                    console.log('PDF Toggle setup complete');
                                } else {
                                    console.error('PDF Toggle elements not found!');
                                }
                            }
                            
                            // Try to setup immediately
                            if (document.readyState === 'loading') {
                                document.addEventListener('DOMContentLoaded', setupPDFToggle);
                            } else {
                                setupPDFToggle();
                            }
                        </script>
                    </table>
                </section>

                <!-- Section Avancée (repliable) -->
                <section id="pdf" class="pdf-section">
                    <h3 style="color: #495057; margin-top: 30px; border-bottom: 2px solid #6c757d; padding-bottom: 10px; cursor: pointer;" onclick="PDFBuilderTabsAPI.toggleAdvancedSection()">
                        🔧 Options avancées <span id="advanced-toggle" style="float: right;">▼</span>
                    </h3>

                    <section id="advanced-section" style="display: none;">
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
