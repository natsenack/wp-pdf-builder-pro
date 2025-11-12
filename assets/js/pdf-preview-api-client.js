/* eslint-disable no-undef */
/**
 * PDF Builder Pro - Preview API Client
 * Intégration complète de l'API Preview 1.4
 */

// Fonctions de debug conditionnel
function isDebugEnabled() {
    // Debug activé seulement si explicitement forcé
    return window.location.search.includes('debug=force');
}

function debugLog(...args) {
    if (isDebugEnabled()) {

    }
}

function debugError(...args) {
    if (isDebugEnabled()) {

    }
}

function debugWarn(...args) {
    if (isDebugEnabled()) {

    }
}

class PDFPreviewAPI {
    constructor() {
        this.endpoint = pdfBuilderAjax?.ajaxurl || '/wp-admin/admin-ajax.php';
        this.nonce = pdfBuilderAjax?.nonce || '';
        this.isGenerating = false;
        this.cache = new Map();
    }

    /**
     * Génère un aperçu depuis l'éditeur (données fictives)
     */
    async generateEditorPreview(templateData, options = {}) {
        if (this.isGenerating) {
            debugWarn('⚠️ Génération déjà en cours...');
            return null;
        }

        this.isGenerating = true;
        this.showLoadingIndicator();

        try {
            const formData = new FormData();
            formData.append('action', 'wp_pdf_preview_image');
            formData.append('nonce', this.nonce);
            formData.append('context', 'editor');
            formData.append('template_data', JSON.stringify(templateData));
            formData.append('quality', options.quality || 150);
            formData.append('format', options.format || 'png');

            debugLog('📤 Envoi requête preview éditeur...');

            const response = await fetch(this.endpoint, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                debugLog('✅ Aperçu éditeur généré:', result.data);
                this.cachePreview(result.data);
                this.displayPreview(result.data.image_url, 'editor');
                return result.data;
            } else {
                debugError('❌ Erreur génération éditeur:', result.data);
                this.showError('Erreur lors de la génération de l\'aperçu');
                return null;
            }
        } catch (error) {
            debugError('❌ Erreur réseau:', error);
            this.showError('Erreur de connexion');
            return null;
        } finally {
            this.isGenerating = false;
            this.hideLoadingIndicator();
        }
    }

    /**
     * Génère un aperçu depuis la metabox WooCommerce (données réelles)
     */
    async generateOrderPreview(templateData, orderId, options = {}) {
        if (this.isGenerating) {
            debugWarn('⚠️ Génération déjà en cours...');
            return null;
        }

        this.isGenerating = true;
        this.showLoadingIndicator();

        try {
            const formData = new FormData();
            formData.append('action', 'wp_pdf_preview_image');
            formData.append('nonce', this.nonce);
            formData.append('context', 'metabox');
            formData.append('template_data', JSON.stringify(templateData));
            formData.append('order_id', orderId);
            formData.append('quality', options.quality || 150);
            formData.append('format', options.format || 'png');

            debugLog('📤 Envoi requête preview commande...', orderId);

            const response = await fetch(this.endpoint, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                debugLog('✅ Aperçu commande généré:', result.data);
                this.cachePreview(result.data);
                this.displayPreview(result.data.image_url, 'metabox', orderId);
                return result.data;
            } else {
                debugError('❌ Erreur génération commande:', result.data);
                this.showError('Erreur lors de la génération de l\'aperçu de commande');
                return null;
            }
        } catch (error) {
            debugError('❌ Erreur réseau:', error);
            this.showError('Erreur de connexion');
            return null;
        } finally {
            this.isGenerating = false;
            this.hideLoadingIndicator();
        }
    }

    /**
     * Met en cache les aperçus générés
     */
    cachePreview(data) {
        const key = data.cache_key || this.generateCacheKey(data);
        this.cache.set(key, {
            url: data.image_url,
            timestamp: Date.now(),
            context: data.context || 'unknown'
        });

        // Nettoyer le cache ancien (garder seulement 10 derniers)
        if (this.cache.size > 10) {
            const oldestKey = this.cache.keys().next().value;
            this.cache.delete(oldestKey);
        }
    }

    /**
     * Génère une clé de cache
     */
    generateCacheKey(data) {
        return btoa(JSON.stringify({
            context: data.context,
            order_id: data.order_id,
            template_hash: this.hashString(JSON.stringify(data.template_data))
        })).slice(0, 32);
    }

    /**
     * Hash simple pour les clés de cache
     */
    hashString(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convertir en 32 bits
        }
        return Math.abs(hash).toString(36);
    }

    /**
     * Affiche l'aperçu généré
     */
    displayPreview(imageUrl, context, orderId = null) {
        // Créer ou mettre à jour la modal d'aperçu
        let previewModal = document.getElementById('pdf-preview-modal');
        if (!previewModal) {
            previewModal = this.createPreviewModal();
            document.body.appendChild(previewModal);
        }

        const img = previewModal.querySelector('#pdf-preview-image');
        const title = previewModal.querySelector('#pdf-preview-title');

        img.src = imageUrl;
        img.style.maxWidth = '100%';
        img.style.height = 'auto';

        if (context === 'editor') {
            title.textContent = '👁️ Aperçu du Template';
        } else {
            title.textContent = `📄 Aperçu Commande #${orderId}`;
        }

        // Ajouter des boutons d'action
        this.addPreviewActions(previewModal, imageUrl, context);

        // Afficher la modal en togglant la classe
        previewModal.classList.add('visible');

        debugLog('🖼️ Aperçu affiché:', imageUrl);
    }

    /**
     * Crée la modal d'aperçu - FIXED CENTERING v3.3
     */
    createPreviewModal() {
        // Ajouter une vraie feuille CSS pour le modal si elle n'existe pas
        if (!document.getElementById('pdf-preview-modal-styles')) {
            const styleSheet = document.createElement('style');
            styleSheet.id = 'pdf-preview-modal-styles';
            styleSheet.textContent = `
                #pdf-preview-modal {
                    position: fixed !important;
                    top: 0 !important;
                    left: 0 !important;
                    width: 100% !important;
                    height: 100% !important;
                    background-color: rgba(0,0,0,0.8) !important;
                    display: none !important;
                    z-index: 99999 !important;
                    align-items: center !important;
                    justify-content: center !important;
                    flex-direction: column !important;
                    visibility: visible !important;
                    gap: 0 !important;
                    padding: 0 !important;
                    margin: 0 !important;
                }
                
                #pdf-preview-modal.visible {
                    display: flex !important;
                }
                
                #pdf-preview-modal-wrapper {
                    background: white !important;
                    border-radius: 8px !important;
                    padding: 20px !important;
                    max-width: 90vw !important;
                    max-height: 90vh !important;
                    overflow-y: auto !important;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.3) !important;
                    flex-shrink: 0 !important;
                    min-width: 300px !important;
                    position: relative !important;
                    width: 500px !important;
                    align-self: center !important;
                }
            `;
            document.head.appendChild(styleSheet);

        }
        
        const modal = document.createElement('div');
        modal.id = 'pdf-preview-modal';
        
        // Wrapper blanc centré
        const wrapper = document.createElement('div');
        wrapper.id = 'pdf-preview-modal-wrapper';

        // Header avec titre et bouton fermer
        const header = document.createElement('div');
        header.style.cssText = `
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        `;

        const title = document.createElement('h3');
        title.id = 'pdf-preview-title';
        title.textContent = 'Aperçu PDF';
        title.style.cssText = 'margin: 0; color: #1d2327;';

        const closeBtn = document.createElement('button');
        closeBtn.id = 'pdf-preview-close';
        closeBtn.textContent = '×';
        closeBtn.style.cssText = `
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        `;

        header.appendChild(title);
        header.appendChild(closeBtn);

        // Actions container
        const actions = document.createElement('div');
        actions.id = 'pdf-preview-actions';
        actions.style.cssText = 'margin-bottom: 15px;';

        // Image container
        const img = document.createElement('img');
        img.id = 'pdf-preview-image';
        img.alt = 'Aperçu PDF';
        img.style.cssText = 'max-width: 100%; height: auto; border: 1px solid #ddd;';

        wrapper.appendChild(header);
        wrapper.appendChild(actions);
        wrapper.appendChild(img);
        modal.appendChild(wrapper);

        // Gestionnaire de fermeture
        closeBtn.addEventListener('click', () => {
            modal.classList.remove('visible');
        });

        // Fermeture en cliquant en dehors
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('visible');
            }
        });

        return modal;
    }

    /**
     * Ajoute les boutons d'action à l'aperçu
     */
    addPreviewActions(modal, imageUrl, context) {
        const actionsContainer = modal.querySelector('#pdf-preview-actions');
        actionsContainer.innerHTML = '';

        // Bouton de téléchargement
        const downloadBtn = document.createElement('button');
        downloadBtn.textContent = '📥 Télécharger';
        downloadBtn.style.cssText = `
            background: #007cba;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        `;
        downloadBtn.addEventListener('click', () => {
            this.downloadPreview(imageUrl);
        });

        // Bouton d'impression
        const printBtn = document.createElement('button');
        printBtn.textContent = '🖨️ Imprimer';
        printBtn.style.cssText = `
            background: #46b450;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        `;
        printBtn.addEventListener('click', () => {
            this.printPreview(imageUrl);
        });

        // Bouton de régénération (pour metabox seulement)
        if (context === 'metabox') {
            const regenerateBtn = document.createElement('button');
            regenerateBtn.textContent = '🔄 Régénérer';
            regenerateBtn.style.cssText = `
                background: #f56e28;
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                cursor: pointer;
            `;
            regenerateBtn.addEventListener('click', () => {
                // Cette fonction devra être appelée depuis le contexte parent
                if (typeof window.regenerateOrderPreview === 'function') {
                    window.regenerateOrderPreview();
                }
            });
            actionsContainer.appendChild(regenerateBtn);
        }

        actionsContainer.appendChild(downloadBtn);
        actionsContainer.appendChild(printBtn);
    }

    /**
     * Télécharge l'aperçu
     */
    downloadPreview(imageUrl) {
        const link = document.createElement('a');
        link.href = imageUrl;
        link.download = `pdf-preview-${Date.now()}.png`;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        debugLog('📥 Téléchargement démarré:', imageUrl);
    }

    /**
     * Imprime l'aperçu
     */
    printPreview(imageUrl) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Aperçu PDF</title>
                    <style>
                        body { margin: 0; padding: 20px; text-align: center; }
                        img { max-width: 100%; height: auto; }
                        @media print {
                            body { margin: 0; }
                            img { max-width: 100%; height: auto; }
                        }
                    </style>
                </head>
                <body>
                    <img src="${imageUrl}" alt="Aperçu PDF" onload="window.print(); window.close();" />
                </body>
            </html>
        `);
        printWindow.document.close();

        debugLog('🖨️ Impression démarrée');
    }

    /**
     * Affiche l'indicateur de chargement
     */
    showLoadingIndicator() {
        let loader = document.getElementById('pdf-preview-loader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'pdf-preview-loader';
            loader.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: rgba(255,255,255,0.9);
                border: 1px solid #ccc;
                border-radius: 8px;
                padding: 20px;
                z-index: 10000;
                display: none;
                text-align: center;
            `;
            loader.innerHTML = `
                <div style="border: 4px solid #f3f3f3; border-top: 4px solid #007cba; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 10px;"></div>
                <div>Génération de l'aperçu...</div>
                <style>
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                </style>
            `;
            document.body.appendChild(loader);
        }
        loader.style.display = 'block';
    }

    /**
     * Cache l'indicateur de chargement
     */
    hideLoadingIndicator() {
        const loader = document.getElementById('pdf-preview-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    }

    /**
     * Affiche un message d'erreur
     */
    showError(message) {
        // Utiliser toastr si disponible, sinon alert
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            alert(message);
        }
    }
}

// Initialisation globale
window.pdfPreviewAPI = new PDFPreviewAPI();

// Fonctions d'aide pour une utilisation facile
window.generateEditorPreview = (templateData, options) => {
    return window.pdfPreviewAPI.generateEditorPreview(templateData, options);
};

window.generateOrderPreview = (templateData, orderId, options) => {
    return window.pdfPreviewAPI.generateOrderPreview(templateData, orderId, options);
};

debugLog('🎯 API Preview 1.4 initialisée et prête à l\'emploi !');
debugLog('📖 Utilisation:');
debugLog('   - generateEditorPreview(templateData)');
debugLog('   - generateOrderPreview(templateData, orderId)');
