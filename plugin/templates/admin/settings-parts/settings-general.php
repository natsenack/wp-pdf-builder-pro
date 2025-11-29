<?php
/**
 * General tab content
 * Updated: 2025-11-18 20:15:00
 */
?>
            <h2>🏠 Paramètres Généraux</h2>

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
                                        value="<?php echo esc_attr($company_siret); ?>"
                                        placeholder="123 456 789 00012" />
                                    <p class="description">Numéro SIRET de l'entreprise</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="company_vat">Numéro TVA</label></th>
                                <td>
                                    <input type="text" id="company_vat" name="company_vat"
                                        value="<?php echo esc_attr($company_vat); ?>"
                                        placeholder="FR12345678901, DE123456789, BE0123456789" />
                                    <p class="description">Numéro de TVA intracommunautaire (format européen : 2 lettres pays + 8-12 caractères)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="company_rcs">RCS</label></th>
                                <td>
                                    <input type="text" id="company_rcs" name="company_rcs"
                                        value="<?php echo esc_attr($company_rcs); ?>"
                                        placeholder="Lyon B 123 456 789" />
                                    <p class="description">Numéro RCS (Registre du Commerce et des Sociétés)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="company_capital">Capital social</label></th>
                                <td>
                                    <input type="text" id="company_capital" name="company_capital"
                                        value="<?php echo esc_attr($company_capital); ?>"
                                        placeholder="10 000 €" />
                                    <p class="description">Montant du capital social de l'entreprise</p>
                                </td>
                            </tr>
                        </table>
                    </article>
                </form>

               <script>
                jQuery(document).ready(function($) {
                    // Fonction de validation des champs entreprise
                    function validateCompanyFields() {
                        var isValid = true;
                        var errors = [];

                        // Validation du téléphone (maximum 10 chiffres)
                        var phone = $('#company_phone_manual').val().trim();
                        if (phone !== '') {
                            // Supprimer tous les caractères non numériques
                            var phoneNumbers = phone.replace(/\D/g, '');
                            if (phoneNumbers.length > 10) {
                                isValid = false;
                                errors.push('Le numéro de téléphone ne peut pas dépasser 10 chiffres.');
                                $('#company_phone_manual').addClass('error').removeClass('valid');
                            } else {
                                $('#company_phone_manual').addClass('valid').removeClass('error');
                            }
                        } else {
                            $('#company_phone_manual').removeClass('error valid');
                        }

                        // Validation du SIRET (14 chiffres)
                        var siret = $('#company_siret').val().trim();
                        if (siret !== '') {
                            var siretNumbers = siret.replace(/\D/g, '');
                            if (siretNumbers.length !== 14) {
                                isValid = false;
                                errors.push('Le numéro SIRET doit contenir exactement 14 chiffres.');
                                $('#company_siret').addClass('error').removeClass('valid');
                            } else {
                                $('#company_siret').addClass('valid').removeClass('error');
                            }
                        } else {
                            $('#company_siret').removeClass('error valid');
                        }

                        // Validation du numéro TVA (format européen flexible)
                        var vat = $('#company_vat').val().trim();
                        if (vat !== '') {
                            // Regex pour les formats TVA européens courants
                            // Format général: 2 lettres pays + chiffres/lettres (8-12 caractères)
                            var vatPattern = /^[A-Z]{2}[A-Z0-9]{8,12}$/i;
                            if (!vatPattern.test(vat.replace(/\s/g, ''))) {
                                isValid = false;
                                errors.push('Le numéro TVA doit être au format européen valide (ex: FR12345678901, DE123456789, BE0123456789).');
                                $('#company_vat').addClass('error').removeClass('valid');
                            } else {
                                $('#company_vat').addClass('valid').removeClass('error');
                            }
                        } else {
                            $('#company_vat').removeClass('error valid');
                        }

                        // Afficher les erreurs si il y en a
                        if (!isValid) {
                            alert('Erreurs de validation :\n\n' + errors.join('\n'));
                        }

                        return isValid;
                    }

                    // Validation en temps réel pour le téléphone
                    $('#company_phone_manual').on('input', function() {
                        var phone = $(this).val().trim();
                        var phoneNumbers = phone.replace(/\D/g, '');
                        if (phoneNumbers.length > 10) {
                            $(this).addClass('error').removeClass('valid');
                        } else if (phoneNumbers.length > 0 && phoneNumbers.length <= 10) {
                            $(this).addClass('valid').removeClass('error');
                        } else {
                            $(this).removeClass('error valid');
                        }
                    });

                    // Validation en temps réel pour le SIRET
                    $('#company_siret').on('input', function() {
                        var siret = $(this).val().trim();
                        var siretNumbers = siret.replace(/\D/g, '');
                        if (siretNumbers.length === 14) {
                            $(this).addClass('valid').removeClass('error');
                        } else if (siretNumbers.length > 0) {
                            $(this).addClass('error').removeClass('valid');
                        } else {
                            $(this).removeClass('error valid');
                        }
                    });

                    // Validation en temps réel pour la TVA
                    $('#company_vat').on('input', function() {
                        var vat = $(this).val().trim();
                        // Regex pour les formats TVA européens courants
                        var vatPattern = /^[A-Z]{2}[A-Z0-9]{8,12}$/i;
                        if (vat !== '' && vatPattern.test(vat.replace(/\s/g, ''))) {
                            $(this).addClass('valid').removeClass('error');
                        } else if (vat !== '' && !vatPattern.test(vat.replace(/\s/g, ''))) {
                            $(this).addClass('error').removeClass('valid');
                        } else {
                            $(this).removeClass('error valid');
                        }
                    });

                    // Gestionnaire AJAX pour la soumission du formulaire
                    $('form[action*="admin.php?page=pdf-builder-settings"]').on('submit', function(e) {
                        e.preventDefault();

                        if (!validateCompanyFields()) {
                            return false;
                        }

                        // Trouver le bouton d'enregistrement dans ce formulaire
                        const form = $(this);
                        const submitBtn = form.find('input[type="submit"], button[type="submit"]');

                        // Désactiver le bouton et montrer l'état de chargement
                        if (submitBtn.length > 0) {
                            const originalText = submitBtn.val() || submitBtn.text();
                            submitBtn.prop('disabled', true);
                            submitBtn.val('Enregistrement...');
                            submitBtn.text('Enregistrement...');
                            submitBtn.css('opacity', '0.7');
                        }

                        // Collecter les données du formulaire
                        const formData = new FormData(this);
                        formData.append('action', 'pdf_builder_save_settings');
                        formData.append('nonce', window.pdfBuilderAjax?.nonce || '');
                        formData.append('tab', 'general');

                        // Faire la requête AJAX
                        if (window.PDF_Builder_Ajax_Handler) {
                            window.PDF_Builder_Ajax_Handler.makeRequest(formData, {
                                button: submitBtn[0],
                                context: 'General Settings',
                                successCallback: (result, originalData) => {
                                    // Log success
                                    if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                                        console.log('Paramètres sauvegardés avec succès !');
                                    }

                                    // Afficher une notification de succès
                                    if (window.showSuccessNotification) {
                                        window.showSuccessNotification('Paramètres sauvegardés avec succès !');
                                    }
                                },
                                errorCallback: (result, originalData) => {
                                    // Log error
                                    if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                                        console.error('Erreur lors de la sauvegarde: ' + (result.errorMessage || 'Erreur inconnue'));
                                    }
                                }
                            }).catch(error => {
                                if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                                    console.error('Erreur AJAX:', error);
                                    console.error('Erreur réseau lors de la sauvegarde');
                                }
                            });
                        } else {
                            // Fallback si le gestionnaire AJAX n'est pas disponible
                            if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                                console.warn('PDF_Builder_Ajax_Handler not available, using fallback');
                            }
                            this.submit();
                        }

                        return false;
                    });
                });
                </script>

                <style>
                    /* Classe commune pour les sections de l'onglet général */
                    .general-section {
                        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                        border: 2px solid #e9ecef;
                        border-radius: 12px;
                        padding: 20px;
                        margin-bottom: 20px;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                    }
                    .general-section h3 {
                        color: #495057;
                        margin-top: 0;
                        border-bottom: 2px solid #e9ecef;
                        padding-bottom: 8px;
                        font-size: 18px;
                    }
                    .form-table input.error {
                        border-color: #dc3545 !important;
                        box-shadow: 0 0 0 1px #dc3545 !important;
                        background-color: #fff5f5 !important;
                    }
                    .form-table input.error:focus {
                        border-color: #dc3545 !important;
                        box-shadow: 0 0 0 1px #dc3545, 0 0 0 3px rgba(220, 53, 69, 0.1) !important;
                    }
                    .form-table input.valid {
                        border-color: #28a745 !important;
                        box-shadow: 0 0 0 1px #28a745 !important;
                        background-color: #f8fff8 !important;
                    }
                    .form-table input.valid:focus {
                        border-color: #28a745 !important;
                        box-shadow: 0 0 0 1px #28a745, 0 0 0 3px rgba(40, 167, 69, 0.1) !important;
                    }
               </style>
           </section>
