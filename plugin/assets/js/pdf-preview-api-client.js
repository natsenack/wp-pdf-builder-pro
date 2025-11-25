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
        this.currentZoom = 100;
        this.currentRotation = 0;
        this.currentPanX = 0;
        this.currentPanY = 0;
        this.isDragging = false;
        this.lastMouseX = 0;
        this.lastMouseY = 0;
        this.dragListeners = null;
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

        // Réinitialiser le zoom, la rotation et le pan
        this.currentZoom = 100;
        this.currentRotation = 0;
        this.currentPanX = 0;
        this.currentPanY = 0;

        const img = previewModal.querySelector('#pdf-preview-image');
        const title = previewModal.querySelector('#pdf-preview-title');

        img.src = imageUrl;
        this.updateImageTransform(img);

        // Initialiser le drag/pan de l'image
        this.initImageDrag(img);

        if (context === 'editor') {
            title.textContent = '👁️ Aperçu du Template';
        } else {
            title.textContent = `📄 Aperçu Commande #${orderId}`;
        }

        // Ajouter les contrôles de zoom et rotation
        this.addZoomControls(previewModal, img);

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
                    overflow-x: hidden !important;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.3) !important;
                    flex-shrink: 0 !important;
                    min-width: 300px !important;
                    position: relative !important;
                    width: 500px !important;
                    align-self: center !important;
                }

                .pdf-preview-action-btn {
                    border: none !important;
                    padding: 8px 16px !important;
                    border-radius: 4px !important;
                    cursor: pointer !important;
                    font-size: 14px !important;
                    margin-right: 10px !important;
                    transition: background-color 0.2s ease !important;
                }

                .pdf-preview-action-btn.download-btn {
                    background: #007cba !important;
                    color: white !important;
                }

                .pdf-preview-action-btn.download-btn:hover {
                    background: #005a87 !important;
                }

                .pdf-preview-action-btn.print-btn {
                    background: #46b450 !important;
                    color: white !important;
                }

                .pdf-preview-action-btn.print-btn:hover {
                    background: #3d8b40 !important;
                }

                .pdf-preview-action-btn.regenerate-btn {
                    background: #f56e28 !important;
                    color: white !important;
                }

                .pdf-preview-action-btn.regenerate-btn:hover {
                    background: #e55a1f !important;
                }

                .pdf-preview-zoom-btn {
                    background: #f1f1f1 !important;
                    border: 1px solid #ddd !important;
                    padding: 5px 10px !important;
                    border-radius: 4px !important;
                    cursor: pointer !important;
                    font-size: 14px !important;
                    transition: background-color 0.2s ease !important;
                }

                .pdf-preview-zoom-btn:hover {
                    background: #e9e9e9 !important;
                }

                .pdf-preview-reset-btn {
                    background: #007cba !important;
                    color: white !important;
                    border: none !important;
                    padding: 5px 10px !important;
                    border-radius: 4px !important;
                    cursor: pointer !important;
                    font-size: 14px !important;
                    transition: background-color 0.2s ease !important;
                }

                .pdf-preview-reset-btn:hover {
                    background: #005a87 !important;
                }

                #pdf-preview-image {
                    max-width: 100% !important;
                    height: auto !important;
                    border: none !important;
                    border-radius: 0 !important;
                    transition: transform 0.3s ease !important;
                    object-fit: contain !important;
                    display: block !important;
                    transform-origin: center center !important;
                }

                #pdf-preview-image-container {
                    overflow: hidden !important;
                    border: 1px solid #ddd !important;
                    border-radius: 4px !important;
                    max-height: 60vh !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    background: #f9f9f9 !important;
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
            margin-bottom: 8px;
        `;

        const title = document.createElement('h4');
        title.id = 'pdf-preview-title';
        title.textContent = 'Aperçu PDF';
        title.style.cssText = 'margin: 0; color: #1d2327; font-size: 16px; font-weight: 600;';

        const closeBtn = document.createElement('button');
        closeBtn.id = 'pdf-preview-close';
        closeBtn.textContent = '×';
        closeBtn.style.cssText = `
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #666;
            padding: 0;
            line-height: 1;
        `;

        header.appendChild(title);
        header.appendChild(closeBtn);

        // Actions container
        const actions = document.createElement('div');
        actions.id = 'pdf-preview-actions';
        actions.style.cssText = 'margin-bottom: 10px;';

        // Image container avec overflow hidden
        const imageContainer = document.createElement('div');
        imageContainer.id = 'pdf-preview-image-container';
        imageContainer.style.cssText = 'overflow: hidden; border: 1px solid #ddd; border-radius: 4px; max-height: 60vh; display: flex; align-items: center; justify-content: center;';

        const img = document.createElement('img');
        img.id = 'pdf-preview-image';
        img.alt = 'Aperçu PDF';
        img.style.cssText = 'max-width: 100%; height: auto; object-fit: contain; display: block; transform-origin: center center; transition: transform 0.3s ease;';

        imageContainer.appendChild(img);

        wrapper.appendChild(header);
        wrapper.appendChild(actions);
        wrapper.appendChild(imageContainer);
        modal.appendChild(wrapper);

        // Gestionnaire de fermeture
        closeBtn.addEventListener('click', () => {
            modal.classList.remove('visible');
            this.cleanupDragListeners();
        });

        // Fermeture en cliquant en dehors
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('visible');
                this.cleanupDragListeners();
            }
        });

        return modal;
    }

    /**
     * Met à jour la transformation de l'image
     */
    updateImageTransform(img) {
        img.style.transform = 'translate(' + this.currentPanX + 'px, ' + this.currentPanY + 'px) scale(' + (this.currentZoom / 100) + ') rotate(' + this.currentRotation + 'deg)';
    }

    /**
     * Ajoute les contrôles de zoom et rotation
     */
    addZoomControls(modal, img) {
        // Créer le conteneur de contrôles s'il n'existe pas
        let controlsContainer = modal.querySelector('#pdf-preview-controls');
        if (!controlsContainer) {
            controlsContainer = document.createElement('div');
            controlsContainer.id = 'pdf-preview-controls';
            controlsContainer.style.cssText = 'display: flex; gap: 10px; align-items: center; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee; flex-wrap: wrap; justify-content: space-between;';

            // Insérer avant les actions
            const actionsContainer = modal.querySelector('#pdf-preview-actions');
            modal.querySelector('#pdf-preview-modal-wrapper').insertBefore(controlsContainer, actionsContainer);
        }
        controlsContainer.innerHTML = '';

        // Contrôles de zoom
        const zoomContainer = document.createElement('div');
        zoomContainer.id = 'pdf-preview-zoom';
        zoomContainer.style.cssText = 'display: flex; align-items: center; gap: 5px;';

        const zoomOutBtn = document.createElement('button');
        zoomOutBtn.textContent = '🔍-';
        zoomOutBtn.className = 'pdf-preview-zoom-btn';
        zoomOutBtn.addEventListener('click', () => this.zoomImage(img, -25));

        const zoomSlider = document.createElement('input');
        zoomSlider.type = 'range';
        zoomSlider.id = 'pdf-preview-zoom-slider';
        zoomSlider.min = '25';
        zoomSlider.max = '300';
        zoomSlider.value = this.currentZoom;
        zoomSlider.style.cssText = 'width: 120px;';
        zoomSlider.addEventListener('input', (e) => this.setZoom(img, parseInt(e.target.value)));

        const zoomValue = document.createElement('span');
        zoomValue.id = 'pdf-preview-zoom-value';
        zoomValue.textContent = this.currentZoom + '%';
        zoomValue.style.cssText = 'min-width: 45px; font-size: 12px; color: #666;';

        const zoomInBtn = document.createElement('button');
        zoomInBtn.textContent = '🔍+';
        zoomInBtn.className = 'pdf-preview-zoom-btn';
        zoomInBtn.addEventListener('click', () => this.zoomImage(img, 25));

        zoomContainer.appendChild(zoomOutBtn);
        zoomContainer.appendChild(zoomSlider);
        zoomContainer.appendChild(zoomValue);
        zoomContainer.appendChild(zoomInBtn);

        // Contrôles de rotation
        const rotateLeftBtn = document.createElement('button');
        rotateLeftBtn.textContent = '↺';
        rotateLeftBtn.title = 'Rotation gauche';
        rotateLeftBtn.className = 'pdf-preview-zoom-btn';
        rotateLeftBtn.addEventListener('click', () => this.rotateImage(img, -90));

        const rotateRightBtn = document.createElement('button');
        rotateRightBtn.textContent = '↻';
        rotateRightBtn.title = 'Rotation droite';
        rotateRightBtn.className = 'pdf-preview-zoom-btn';
        rotateRightBtn.addEventListener('click', () => this.rotateImage(img, 90));

        const resetBtn = document.createElement('button');
        resetBtn.textContent = '🔄 Reset';
        resetBtn.title = 'Réinitialiser zoom et rotation';
        resetBtn.className = 'pdf-preview-reset-btn';
        resetBtn.addEventListener('click', () => this.resetImage(img));

        controlsContainer.appendChild(zoomContainer);
        controlsContainer.appendChild(rotateLeftBtn);
        controlsContainer.appendChild(rotateRightBtn);
        controlsContainer.appendChild(resetBtn);
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
     * Tourne l'image
     */
    rotateImage(img, degrees) {
        this.currentRotation = (this.currentRotation + degrees) % 360;
        this.updateImageTransform(img);
    }

    /**
     * Réinitialise l'image
     */
    resetImage(img) {
        this.currentZoom = 100;
        this.currentRotation = 0;
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
     * Initialise le drag/pan de l'image
     */
    initImageDrag(img) {
        const container = img.parentElement;

        // Supprimer les anciens event listeners s'ils existent
        this.cleanupDragListeners();

        // Stocker les références pour pouvoir les supprimer plus tard
        this.dragListeners = {
            mousedown: (e) => this.handleMouseDown(e, img),
            mousemove: (e) => this.handleMouseMove(e, img, container),
            mouseup: () => this.handleMouseUp(img),
            mouseenter: () => this.handleMouseEnter(img),
            mouseleave: () => this.handleMouseLeave(img)
        };

        // Ajouter les nouveaux event listeners
        img.addEventListener('mousedown', this.dragListeners.mousedown);
        document.addEventListener('mousemove', this.dragListeners.mousemove);
        document.addEventListener('mouseup', this.dragListeners.mouseup);
        img.addEventListener('mouseenter', this.dragListeners.mouseenter);
        img.addEventListener('mouseleave', this.dragListeners.mouseleave);
    }

    /**
     * Nettoie les event listeners de drag
     */
    cleanupDragListeners() {
        if (!this.dragListeners) return;

        // Supprimer tous les listeners existants
        const img = document.querySelector('#pdf-preview-image');
        if (img) {
            img.removeEventListener('mousedown', this.dragListeners.mousedown);
            img.removeEventListener('mouseenter', this.dragListeners.mouseenter);
            img.removeEventListener('mouseleave', this.dragListeners.mouseleave);
        }

        document.removeEventListener('mousemove', this.dragListeners.mousemove);
        document.removeEventListener('mouseup', this.dragListeners.mouseup);

        this.dragListeners = null;
    }

    /**
     * Gestionnaire mousedown
     */
    handleMouseDown(e, img) {
        // Vérifier si le pan est possible (zoom > 100% OU image plus grande que le conteneur)
        const container = img.parentElement;
        const containerRect = container.getBoundingClientRect();
        const scale = this.currentZoom / 100;
        const canPan = this.currentZoom > 100 ||
                      (img.naturalWidth * scale > containerRect.width) ||
                      (img.naturalHeight * scale > containerRect.height);

        if (!canPan) {
            debugLog('🚫 Drag impossible:', {
                zoom: this.currentZoom,
                imgSize: { w: img.naturalWidth * scale, h: img.naturalHeight * scale },
                containerSize: { w: containerRect.width, h: containerRect.height }
            });
            return;
        }

        e.preventDefault();
        this.isDragging = true;
        this.lastMouseX = e.clientX;
        this.lastMouseY = e.clientY;
        img.style.cursor = 'grabbing';

        debugLog('🖱️ Drag commencé:', { x: e.clientX, y: e.clientY, zoom: this.currentZoom });
    }    /**
     * Gestionnaire mousemove
     */
    handleMouseMove(e, img, container) {
        if (!this.isDragging) return;

        // Vérifier si le pan est encore possible
        const containerRect = container.getBoundingClientRect();
        const scale = this.currentZoom / 100;
        const canPan = this.currentZoom > 100 ||
                      (img.naturalWidth * scale > containerRect.width) ||
                      (img.naturalHeight * scale > containerRect.height);

        if (!canPan) return;

        const deltaX = e.clientX - this.lastMouseX;
        const deltaY = e.clientY - this.lastMouseY;

        this.currentPanX += deltaX;
        this.currentPanY += deltaY;

        // Limiter le pan pour éviter que l'image sorte complètement
        this.constrainPan(img, container);

        this.updateImageTransform(img);

        this.lastMouseX = e.clientX;
        this.lastMouseY = e.clientY;

        debugLog('🖱️ Drag en cours:', { deltaX, deltaY, panX: this.currentPanX, panY: this.currentPanY });
    }

    /**
     * Gestionnaire mouseup
     */
    handleMouseUp(img) {
        if (this.isDragging) {
            this.isDragging = false;

            // Vérifier si le pan est encore possible pour le curseur
            const container = img.parentElement;
            const containerRect = container.getBoundingClientRect();
            const scale = this.currentZoom / 100;
            const canPan = this.currentZoom > 100 ||
                          (img.naturalWidth * scale > containerRect.width) ||
                          (img.naturalHeight * scale > containerRect.height);

            img.style.cursor = canPan ? 'grab' : 'default';
            debugLog('🖱️ Drag terminé');
        }
    }

    /**
     * Gestionnaire mouseenter
     */
    handleMouseEnter(img) {
        // Vérifier si le pan est possible
        const container = img.parentElement;
        const containerRect = container.getBoundingClientRect();
        const scale = this.currentZoom / 100;
        const canPan = this.currentZoom > 100 ||
                      (img.naturalWidth * scale > containerRect.width) ||
                      (img.naturalHeight * scale > containerRect.height);

        if (canPan) {
            img.style.cursor = 'grab';
        }
    }

    /**
     * Gestionnaire mouseleave
     */
    handleMouseLeave(img) {
        if (!this.isDragging) {
            img.style.cursor = 'default';
        }
    }

    /**
     * Contraint le pan pour éviter que l'image sorte complètement du conteneur
     */
    constrainPan(img, container) {
        const containerRect = container.getBoundingClientRect();

        // Utiliser les dimensions naturelles de l'image pour les calculs
        const naturalWidth = img.naturalWidth;
        const naturalHeight = img.naturalHeight;

        // Calculer les dimensions après zoom
        const scale = this.currentZoom / 100;
        const scaledWidth = naturalWidth * scale;
        const scaledHeight = naturalHeight * scale;

        // Calculer les limites de pan (l'image doit rester visible)
        const maxPanX = Math.max(0, (scaledWidth - containerRect.width) / 2);
        const maxPanY = Math.max(0, (scaledHeight - containerRect.height) / 2);

        // Appliquer les contraintes
        this.currentPanX = Math.max(-maxPanX, Math.min(maxPanX, this.currentPanX));
        this.currentPanY = Math.max(-maxPanY, Math.min(maxPanY, this.currentPanY));

        debugLog('🔒 Pan contraint:', {
            natural: { w: naturalWidth, h: naturalHeight },
            scaled: { w: scaledWidth, h: scaledHeight },
            container: { w: containerRect.width, h: containerRect.height },
            limits: { x: maxPanX, y: maxPanY },
            current: { x: this.currentPanX, y: this.currentPanY }
        });
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
        downloadBtn.className = 'pdf-preview-action-btn download-btn';
        downloadBtn.addEventListener('click', () => {
            this.downloadPreview(imageUrl);
        });

        // Bouton d'impression
        const printBtn = document.createElement('button');
        printBtn.textContent = '🖨️ Imprimer';
        printBtn.className = 'pdf-preview-action-btn print-btn';
        printBtn.addEventListener('click', () => {
            this.printPreview(imageUrl);
        });

        // Bouton de régénération (pour metabox seulement)
        if (context === 'metabox') {
            const regenerateBtn = document.createElement('button');
            regenerateBtn.textContent = '🔄 Régénérer';
            regenerateBtn.className = 'pdf-preview-action-btn regenerate-btn';
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
        link.download = 'pdf-preview-' + Date.now() + '.png';
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
        printWindow.document.write('\
            <html>\
                <head>\
                    <title>Aperçu PDF</title>\
                    <style>\
                        body { margin: 0; padding: 20px; text-align: center; }\
                        img { max-width: 100%; height: auto; }\
                        @media print {\
                            body { margin: 0; }\
                            img { max-width: 100%; height: auto; }\
                        }\
                    </style>\
                </head>\
                <body>\
                    <img src="' + imageUrl + '" alt="Aperçu PDF" onload="window.print(); window.close();" />\
                </body>\
            </html>\
        ');
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
            loader.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(255,255,255,0.9); border: 1px solid #ccc; border-radius: 8px; padding: 20px; z-index: 10000; display: none; text-align: center;';
            loader.innerHTML = '<div style="border: 4px solid #f3f3f3; border-top: 4px solid #007cba; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 10px;"></div><div>Génération de l\'aperçu...</div><style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>';
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





