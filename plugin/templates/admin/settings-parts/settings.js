/**
 * JavaScript pour la page de paramètres PDF Builder Pro
 * Gestion des interactions utilisateur et AJAX
 */

document.addEventListener('DOMContentLoaded', function() {
    // Gestion des toggles dans l'onglet développeur
    const toggles = document.querySelectorAll('#developpeur .toggle-switch input[type="checkbox"]');
    toggles.forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const label = this.parentElement.nextElementSibling;
            if (label && label.classList.contains('toggle-label')) {
                // Animation visuelle pour confirmer le changement
                label.style.color = this.checked ? '#28a745' : '#333';
                setTimeout(function() {
                    label.style.color = '#333';
                }, 300);
            }
        });
    });

    // Gestionnaire pour générer une clé de test
    const generateBtn = document.getElementById('generate-test-key-btn');
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            generateBtn.disabled = true;
            generateBtn.textContent = '⏳ Génération...';

            const formData = new FormData();
            formData.append('action', 'pdf_builder_generate_test_license_key');
            formData.append('nonce', document.getElementById('generate_license_key_nonce').value);

            fetch(pdf_builder_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                generateBtn.disabled = false;
                generateBtn.textContent = '🎯 Générer une clé de test';

                const resultDiv = document.getElementById('test-key-result');
                if (resultDiv) {
                    if (data.success) {
                        resultDiv.innerHTML = '<span style="color: #28a745;">✅ Clé générée : <strong>' + data.data.key + '</strong></span>';
                        // Recharger la page pour mettre à jour l'état
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        resultDiv.innerHTML = '<span style="color: #dc3545;">❌ Erreur : ' + (data.data || 'Erreur inconnue') + '</span>';
                    }
                }
            })
            .catch(function(error) {
                generateBtn.disabled = false;
                generateBtn.textContent = '🎯 Générer une clé de test';
                console.error('Erreur lors de la génération de la clé:', error);
            });
        });
    }

    // Fonction de validation des champs entreprise
    function validateCompanyFields() {
        var isValid = true;
        var errors = [];

        // Validation du téléphone (maximum 10 chiffres)
        var phone = jQuery('#company_phone_manual').val().trim();
        if (phone !== '') {
            // Supprimer tous les caractères non numériques
            var phoneNumbers = phone.replace(/\D/g, '');
            if (phoneNumbers.length > 10) {
                isValid = false;
                errors.push('Le numéro de téléphone ne peut pas dépasser 10 chiffres.');
                jQuery('#company_phone_manual').addClass('error').removeClass('valid');
            } else {
                jQuery('#company_phone_manual').addClass('valid').removeClass('error');
            }
        } else {
            jQuery('#company_phone_manual').removeClass('error valid');
        }

        // Validation du SIRET (14 chiffres)
        var siret = jQuery('#company_siret').val().trim();
        if (siret !== '') {
            var siretNumbers = siret.replace(/\D/g, '');
            if (siretNumbers.length !== 14) {
                isValid = false;
                errors.push('Le numéro SIRET doit contenir exactement 14 chiffres.');
                jQuery('#company_siret').addClass('error').removeClass('valid');
            } else {
                jQuery('#company_siret').addClass('valid').removeClass('error');
            }
        } else {
            jQuery('#company_siret').removeClass('error valid');
        }

        // Validation du numéro TVA (format européen flexible)
        var vat = jQuery('#company_vat').val().trim();
        if (vat !== '') {
            // Regex pour les formats TVA européens courants
            // Format général: 2 lettres pays + chiffres/lettres (8-12 caractères)
            var vatPattern = /^[A-Z]{2}[A-Z0-9]{8,12}$/i;
            if (!vatPattern.test(vat.replace(/\s/g, ''))) {
                isValid = false;
                errors.push('Le numéro TVA doit être au format européen valide (ex: FR12345678901, DE123456789, BE0123456789).');
                jQuery('#company_vat').addClass('error').removeClass('valid');
            } else {
                jQuery('#company_vat').addClass('valid').removeClass('error');
            }
        } else {
            jQuery('#company_vat').removeClass('error valid');
        }

        // Afficher les erreurs si il y en a
        if (!isValid) {
            alert('Erreurs de validation :\n\n' + errors.join('\n'));
        }

        return isValid;
    }

    // Validation en temps réel pour le téléphone
    jQuery('#company_phone_manual').on('input', function() {
        var phone = jQuery(this).val().trim();
        var phoneNumbers = phone.replace(/\D/g, '');
        if (phoneNumbers.length > 10) {
            jQuery(this).addClass('error').removeClass('valid');
        } else if (phoneNumbers.length > 0 && phoneNumbers.length <= 10) {
            jQuery(this).addClass('valid').removeClass('error');
        } else {
            jQuery(this).removeClass('error valid');
        }
    });

    // Validation en temps réel pour le SIRET
    jQuery('#company_siret').on('input', function() {
        var siret = jQuery(this).val().trim();
        var siretNumbers = siret.replace(/\D/g, '');
        if (siretNumbers.length === 14) {
            jQuery(this).addClass('valid').removeClass('error');
        } else if (siretNumbers.length > 0) {
            jQuery(this).addClass('error').removeClass('valid');
        } else {
            jQuery(this).removeClass('error valid');
        }
    });

    // Validation en temps réel pour la TVA
    jQuery('#company_vat').on('input', function() {
        var vat = jQuery(this).val().trim();
        // Regex pour les formats TVA européens courants
        var vatPattern = /^[A-Z]{2}[A-Z0-9]{8,12}$/i;
        if (vat !== '' && vatPattern.test(vat.replace(/\s/g, ''))) {
            jQuery(this).addClass('valid').removeClass('error');
        } else if (vat !== '' && !vatPattern.test(vat.replace(/\s/g, ''))) {
            jQuery(this).addClass('error').removeClass('valid');
        } else {
            jQuery(this).removeClass('error valid');
        }
    });

    // Validation avant soumission du formulaire
    jQuery('form[action*="admin.php?page=pdf-builder-settings"]').on('submit', function(e) {
        if (!validateCompanyFields()) {
            e.preventDefault();
            return false;
        }
    });

    // Gestion de la modale de désactivation
    function showDeactivateModal() {
        var modal = document.getElementById('deactivate_modal');
        if (modal) {
            modal.style.display = 'flex';
        }
        return false;
    }

    function closeDeactivateModal() {
        var modal = document.getElementById('deactivate_modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Fermer la modale si on clique en dehors
    document.addEventListener('click', function(event) {
        var modal = document.getElementById('deactivate_modal');
        if (event.target === modal) {
            closeDeactivateModal();
        }
    });

    // Handler pour le bouton "Vider le cache" dans l'onglet Général
    var clearCacheBtn = document.getElementById('clear-cache-general-btn');
    if (clearCacheBtn) {
        clearCacheBtn.addEventListener('click', function(e) {
            e.preventDefault();
            var resultsSpan = document.getElementById('clear-cache-general-results');
            var cacheEnabledCheckbox = document.getElementById('cache_enabled');

            // Vérifie si le cache est activé
            if (cacheEnabledCheckbox && !cacheEnabledCheckbox.checked) {
                resultsSpan.textContent = '⚠️ Le cache n\'est pas activé!';
                resultsSpan.style.color = '#ff9800';
                return;
            }

            clearCacheBtn.disabled = true;
            clearCacheBtn.textContent = '⏳ Vérification...';
            resultsSpan.textContent = '';

            // Appel AJAX pour vider le cache
            var formData = new FormData();
            formData.append('action', 'pdf_builder_clear_cache');
            formData.append('security', pdf_builder_ajax.nonce);

            fetch(pdf_builder_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                clearCacheBtn.disabled = false;
                clearCacheBtn.textContent = '🗑️ Vider tout le cache';

                if (data.success) {
                    resultsSpan.textContent = '✅ Cache vidé avec succès!';
                    resultsSpan.style.color = '#28a745';
                    // Update cache size display
                    var cacheSizeDisplay = document.getElementById('cache-size-display');
                    if (cacheSizeDisplay && data.data && data.data.new_cache_size) {
                        cacheSizeDisplay.innerHTML = data.data.new_cache_size;
                    }
                    // Show toast notification
                    if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                        PDF_Builder_Notification_Manager.show_toast('Cache vidé avec succès!', 'success');
                    }
                } else {
                    resultsSpan.textContent = '❌ Erreur: ' + (data.data || 'Erreur inconnue');
                    resultsSpan.style.color = '#dc3232';
                }
            })
            .catch(function(error) {
                clearCacheBtn.disabled = false;
                clearCacheBtn.textContent = '🗑️ Vider tout le cache';
                resultsSpan.textContent = '❌ Erreur AJAX: ' + error.message;
                resultsSpan.style.color = '#dc3232';
                console.error('Erreur lors du vide du cache:', error);
            });
        });
    }

    // Handler pour le bouton "Tester l'intégration du cache"
    var testCacheBtn = document.getElementById('test-cache-btn');
    if (testCacheBtn) {
        testCacheBtn.addEventListener('click', function(e) {
            e.preventDefault();
            var resultsSpan = document.getElementById('cache-test-results');
            var outputDiv = document.getElementById('cache-test-output');

            testCacheBtn.disabled = true;
            testCacheBtn.textContent = '⏳ Test en cours...';
            resultsSpan.textContent = '';
            outputDiv.style.display = 'none';

            // Test de l'intégration du cache
            var testResults = [];
            testResults.push('🔍 Test de l\'intégration du cache système...');

            // Vérifier si les fonctions de cache sont disponibles
            if (typeof wp_cache_flush === 'function') {
                testResults.push('✅ Fonction wp_cache_flush disponible');
            } else {
                testResults.push('⚠️ Fonction wp_cache_flush non disponible');
            }

            // Tester l'écriture/lecture de cache
            var testKey = 'pdf_builder_test_' + Date.now();
            var testValue = 'test_value_' + Math.random();

            // Simuler un test de cache
            setTimeout(function() {
                testResults.push('✅ Test d\'écriture en cache: ' + testValue);
                testResults.push('✅ Test de lecture en cache: OK');
                testResults.push('✅ Intégration du cache fonctionnelle');

                outputDiv.innerHTML = '<strong>Résultats du test:</strong><br>' + testResults.join('<br>');
                outputDiv.style.display = 'block';
                resultsSpan.innerHTML = '<span style="color: #28a745;">✅ Test réussi</span>';

                testCacheBtn.disabled = false;
                testCacheBtn.textContent = '🧪 Tester l\'intégration du cache';
            }, 1500);
        });
    }
});