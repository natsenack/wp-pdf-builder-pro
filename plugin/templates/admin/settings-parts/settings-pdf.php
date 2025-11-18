<?php // PDF tab content - Updated: 2025-11-18 20:20:00 ?>

            <h2>📄 Configuration PDF</h2>

            <!-- Section Configuration PDF -->
            <section class="general-section">
                <h3 style="color: #495057; margin-top: 0; border-bottom: 2px solid #e9ecef; padding-bottom: 10px;">
                    📄 Configuration PDF
                </h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_general_pdf_nonce'); ?>
                    <input type="hidden" name="current_tab" value="general">

                  <table class="form-table">
                    <tr>
                        <th scope="row"><label for="general_pdf_quality">Qualité PDF</label></th>
                        <td>
                            <select id="general_pdf_quality" name="pdf_quality">
                                <option value="low" <?php selected($pdf_quality, 'low'); ?>>Faible (fichiers plus petits)</option>
                                <option value="medium" <?php selected($pdf_quality, 'medium'); ?>>Moyen</option>
                                <option value="high" <?php selected($pdf_quality, 'high'); ?>>Élevée (meilleure qualité)</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="default_format">Format PDF par défaut</label></th>
                        <td>
                            <select id="default_format" name="default_format">
                                <option value="A4" <?php selected($default_format, 'A4'); ?>>A4</option>
                                <option value="A3" <?php selected($default_format, 'A3'); ?>>A3</option>
                                <option value="Letter" <?php selected($default_format, 'Letter'); ?>>Letter</option>
                                <option value="Legal" <?php selected($default_format, 'Legal'); ?>>Legal</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="default_orientation">Orientation par défaut</label></th>
                        <td>
                            <select id="default_orientation" name="default_orientation">
                                <option value="portrait" <?php selected($default_orientation, 'portrait'); ?>>Portrait</option>
                                <option value="landscape" <?php selected($default_orientation, 'landscape'); ?>>Paysage</option>
                            </select>
                        </td>
                    </tr>
                  </table>
                </form>
            </section>

            <form method="post" action="">
                <?php wp_nonce_field('pdf_builder_pdf', 'pdf_builder_pdf_nonce'); ?>
                <input type="hidden" name="current_tab" value="pdf">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="pdf_quality">Qualité PDF</label></th>
                        <td>
                            <select id="pdf_quality" name="pdf_quality">
                                <option value="low" <?php selected(get_option('pdf_builder_pdf_quality', 'high'), 'low'); ?>>Faible</option>
                                <option value="medium" <?php selected(get_option('pdf_builder_pdf_quality', 'high'), 'medium'); ?>>Moyen</option>
                                <option value="high" <?php selected(get_option('pdf_builder_pdf_quality', 'high'), 'high'); ?>>Élevé</option>
                            </select>
                            <p class="description">Qualité de génération des PDF</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="pdf_page_size">Taille de page</label></th>
                        <td>
                            <select id="pdf_page_size" name="pdf_page_size">
                                <option value="A4" <?php selected(get_option('pdf_builder_pdf_page_size', 'A4'), 'A4'); ?>>A4</option>
                                <option value="A3" <?php selected(get_option('pdf_builder_pdf_page_size', 'A4'), 'A3'); ?>>A3</option>
                                <option value="Letter" <?php selected(get_option('pdf_builder_pdf_page_size', 'A4'), 'Letter'); ?>>Letter</option>
                            </select>
                            <p class="description">Format de page pour les PDF</p>
                        </td>
                    </tr>
                </table>
            </form>