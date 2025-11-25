/**
 * PDF Builder Pro - Preview API Client
 * Intégration complète de l'API Preview 1.4
 * SOLUTION: Centrage modal avec injecteur CSS agressif
 */

// ⚡ FORCE INJECTER LE CSS IMMÉDIATEMENT AU CHARGEMENT
(function() {
    if (!document.getElementById('pdf-preview-modal-styles-v2')) {
        const style = document.createElement('style');
        style.id = 'pdf-preview-modal-styles-v2';
        style.textContent = `
            #pdf-preview-modal {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: 100% !important;
                background: rgba(0,0,0,0.8) !important;
                display: none !important;
                z-index: 99999 !important;
            }
            #pdf-preview-modal.visible {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            #pdf-preview-modal-wrapper {
                background: white !important;
                border-radius: 8px !important;
                padding: 20px !important;
                max-width: 90vw !important;
                max-height: 90vh !important;
                overflow-y: auto !important;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3) !important;
                width: 800px !important;
                position: relative !important;
            }
            #pdf-preview-image {
                max-width: 100% !important;
                height: auto !important;
                border: 1px solid #ddd !important;
                border-radius: 4px !important;
                transition: transform 0.3s ease !important;
            }
            #pdf-preview-controls {
                display: flex !important;
                gap: 10px !important;
                align-items: center !important;
                margin-bottom: 15px !important;
                flex-wrap: wrap !important;
            }
            #pdf-preview-zoom {
                display: flex !important;
                align-items: center !important;
                gap: 5px !important;
            }
            #pdf-preview-zoom-slider {
                width: 120px !important;
            }
            #pdf-preview-zoom-value {
                min-width: 45px !important;
                font-size: 12px !important;
                color: #666 !important;
            }
            #pdf-preview-loading {
                display: none !important;
                position: absolute !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                text-align: center !important;
                z-index: 10 !important;
            }
            #pdf-preview-spinner {
                border: 4px solid #f3f3f3 !important;
                border-top: 4px solid #007cba !important;
                border-radius: 50% !important;
                width: 40px !important;
                height: 40px !important;
                animation: spin 1s linear infinite !important;
                margin: 0 auto 10px !important;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            #pdf-preview-error {
                display: none !important;
                background: #ffebee !important;
                color: #c62828 !important;
                padding: 15px !important;
                border-radius: 4px !important;
                border: 1px solid #ffcdd2 !important;
                text-align: center !important;
            }
            @media (max-width: 768px) {
                #pdf-preview-modal-wrapper {
                    width: 95vw !important;
                    padding: 15px !important;
                }
                #pdf-preview-controls {
                    flex-direction: column !important;
                    align-items: stretch !important;
                }
                #pdf-preview-zoom {
                    justify-content: center !important;
                }
            }
        `;
        document.head.appendChild(style);
    }
})();

// Fonctions de debug conditionnel
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



            const response = await fetch(this.endpoint, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {

                this.cachePreview(result.data);
                this.displayPreview(result.data.image_url, 'editor');
                return result.data;
            } else {

                this.showError('Erreur lors de la génération de l\'aperçu');
                return null;
            }
        } catch {

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



            const response = await fetch(this.endpoint, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {

                this.cachePreview(result.data);
                this.displayPreview(result.data.image_url, 'metabox', orderId);
                return result.data;
            } else {

                this.showError('Erreur lors de la génération de l\'aperçu de commande');
                return null;
            }
        } catch {

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
     * FIX CENTRAGE MODAL - Version 3.2.1
     */
    displayPreview(imageUrl, context, orderId = null) {

        
        // Créer ou mettre à jour la modal d'aperçu
        let previewModal = document.getElementById('pdf-preview-modal');
        if (!previewModal) {
            previewModal = this.createPreviewModal();
            document.body.appendChild(previewModal);
        }

        // Reset zoom and rotation
        this.currentZoom = 100;
        this.currentRotation = 0;

        const img = previewModal.querySelector('#pdf-preview-image');
        const title = previewModal.querySelector('#pdf-preview-title');

        // Hide image initially, show loading
        img.style.display = 'none';
        this.showLoading('Chargement de l\'aperçu...');

        img.onload = () => {
            this.hideLoading();
            img.style.display = 'block';
            this.updateImageTransform(img);
        };

        img.onerror = () => {
            this.hideLoading();
            this.showError('Erreur lors du chargement de l\'image');
        };

        img.src = imageUrl;
        img.style.maxWidth = '100%';
        img.style.height = 'auto';

        if (context === 'editor') {
            title.textContent = '👁️ Aperçu du Template';
        } else {
            title.textContent = `📄 Aperçu Commande #${orderId}`;
        }

        // Ajouter des boutons d'action et contrôles
        this.addPreviewActions(previewModal, imageUrl, context);
        this.addZoomControls(previewModal, img);

        // Afficher la modal en togglant la classe FLEX
        previewModal.classList.add('visible');
        




    }

    /**
     * Crée la modal d'aperçu (CSS déjà injecté en haut du fichier)
     */
    createPreviewModal() {
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

        // Contrôles de zoom et rotation
        const controls = document.createElement('div');
        controls.id = 'pdf-preview-controls';
        controls.style.cssText = 'margin-bottom: 15px;';

        // Actions container
        const actions = document.createElement('div');
        actions.id = 'pdf-preview-actions';
        actions.style.cssText = 'margin-bottom: 15px;';

        // Indicateur de chargement
        const loading = document.createElement('div');
        loading.id = 'pdf-preview-loading';
        loading.innerHTML = `
            <div id="pdf-preview-spinner"></div>
            <div id="pdf-preview-loading-text">Chargement...</div>
        `;

        // Message d'erreur
        const error = document.createElement('div');
        error.id = 'pdf-preview-error';
        error.innerHTML = `
            <div id="pdf-preview-error-text">Erreur de chargement</div>
        `;

        // Image container
        const img = document.createElement('img');
        img.id = 'pdf-preview-image';
        img.alt = 'Aperçu PDF';
        img.style.cssText = 'max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 4px;';

        wrapper.appendChild(header);
        wrapper.appendChild(controls);
        wrapper.appendChild(actions);
        wrapper.appendChild(loading);
        wrapper.appendChild(error);
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
     * Ajoute les contrôles de zoom et rotation
     */
    addZoomControls(modal, img) {
        const controlsContainer = modal.querySelector('#pdf-preview-controls');
        controlsContainer.innerHTML = '';

        // Contrôles de zoom
        const zoomContainer = document.createElement('div');
        zoomContainer.id = 'pdf-preview-zoom';

        const zoomOutBtn = document.createElement('button');
        zoomOutBtn.textContent = '🔍-';
        zoomOutBtn.style.cssText = `
            background: #f1f1f1;
            border: 1px solid #ddd;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        `;
        zoomOutBtn.addEventListener('click', () => this.zoomImage(img, -25));

        const zoomSlider = document.createElement('input');
        zoomSlider.type = 'range';
        zoomSlider.id = 'pdf-preview-zoom-slider';
        zoomSlider.min = '25';
        zoomSlider.max = '300';
        zoomSlider.value = '100';
        zoomSlider.addEventListener('input', (e) => this.setZoom(img, parseInt(e.target.value)));

        const zoomValue = document.createElement('span');
        zoomValue.id = 'pdf-preview-zoom-value';
        zoomValue.textContent = '100%';

        const zoomInBtn = document.createElement('button');
        zoomInBtn.textContent = '🔍+';
        zoomInBtn.style.cssText = `
            background: #f1f1f1;
            border: 1px solid #ddd;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        `;
        zoomInBtn.addEventListener('click', () => this.zoomImage(img, 25));

        zoomContainer.appendChild(zoomOutBtn);
        zoomContainer.appendChild(zoomSlider);
        zoomContainer.appendChild(zoomValue);
        zoomContainer.appendChild(zoomInBtn);

        // Contrôles de rotation
        const rotateLeftBtn = document.createElement('button');
        rotateLeftBtn.textContent = '↺';
        rotateLeftBtn.title = 'Rotation gauche';
        rotateLeftBtn.style.cssText = `
            background: #f1f1f1;
            border: 1px solid #ddd;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 15px;
        `;
        rotateLeftBtn.addEventListener('click', () => this.rotateImage(img, -90));

        const rotateRightBtn = document.createElement('button');
        rotateRightBtn.textContent = '↻';
        rotateRightBtn.title = 'Rotation droite';
        rotateRightBtn.style.cssText = `
            background: #f1f1f1;
            border: 1px solid #ddd;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        `;
        rotateRightBtn.addEventListener('click', () => this.rotateImage(img, 90));

        const resetBtn = document.createElement('button');
        resetBtn.textContent = '🔄 Reset';
        resetBtn.title = 'Réinitialiser zoom et rotation';
        resetBtn.style.cssText = `
            background: #007cba;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 15px;
        `;
        resetBtn.addEventListener('click', () => this.resetImage(img, zoomSlider, zoomValue));

        controlsContainer.appendChild(zoomContainer);
        controlsContainer.appendChild(rotateLeftBtn);
        controlsContainer.appendChild(rotateRightBtn);
        controlsContainer.appendChild(resetBtn);
    }

    /**
     * Met à jour la transformation de l'image
     */
    updateImageTransform(img) {
        img.style.transform = `scale(${this.currentZoom / 100}) rotate(${this.currentRotation}deg)`;
    }

    /**
     * Zoom l'image
     */
    zoomImage(img, delta) {
        this.currentZoom = Math.max(25, Math.min(300, this.currentZoom + delta));
        this.updateImageTransform(img);
        this.updateZoomUI();
    }

    /**
     * Définit le zoom directement
     */
    setZoom(img, zoom) {
        this.currentZoom = zoom;
        this.updateImageTransform(img);
        this.updateZoomUI();
    }

    /**
     * Met à jour l'interface utilisateur du zoom
     */
    updateZoomUI() {
        const slider = document.getElementById('pdf-preview-zoom-slider');
        const value = document.getElementById('pdf-preview-zoom-value');
        if (slider) slider.value = this.currentZoom;
        if (value) value.textContent = this.currentZoom + '%';
    }

    /**
     * Tourne l'image
     */
    rotateImage(img, degrees) {
        this.currentRotation = (this.currentRotation + degrees) % 360;
        this.updateImageTransform(img);
    }

    /**
     * Réinitialise l'image
     */
    resetImage(img, slider, value) {
        this.currentZoom = 100;
        this.currentRotation = 0;
        this.updateImageTransform(img);
        if (slider) slider.value = 100;
        if (value) value.textContent = '100%';
    }

    /**
     * Affiche l'indicateur de chargement
     */
    showLoading(message = 'Chargement...') {
        const loading = document.getElementById('pdf-preview-loading');
        const spinner = document.getElementById('pdf-preview-spinner');
        const text = document.getElementById('pdf-preview-loading-text');

        if (loading) {
            loading.style.display = 'block';
            if (text) text.textContent = message;
        }
    }

    /**
     * Cache l'indicateur de chargement
     */
    hideLoading() {
        const loading = document.getElementById('pdf-preview-loading');
        if (loading) {
            loading.style.display = 'none';
        }
    }

    /**
     * Affiche un message d'erreur
     */
    showError(message) {
        const error = document.getElementById('pdf-preview-error');
        const text = document.getElementById('pdf-preview-error-text');

        if (error) {
            error.style.display = 'block';
            if (text) text.textContent = message;
        }
    }

    /**
     * Cache le message d'erreur
     */
    hideError() {
        const error = document.getElementById('pdf-preview-error');
        if (error) {
            error.style.display = 'none';
        }
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





