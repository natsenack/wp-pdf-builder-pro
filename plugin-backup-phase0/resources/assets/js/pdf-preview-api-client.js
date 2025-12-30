/* eslint-disable no-undef */
/**
 * PDF Builder Pro - Preview API Client
 * Intégration complète de l'API Preview 1.4
 */

// Fonctions de debug conditionnel - RÉACTIVÉES pour mesurer les FPS

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
        this.canDrag = false; // Flag pour savoir si le drag est autorisé
        this.containerRect = null; // Cache des dimensions du conteneur
        this.dragStartTime = 0; // Pour mesurer la performance des drags
        this.maxPanX = 0; // Limites pré-calculées
        this.maxPanY = 0; // Limites pré-calculées
        this.needsConstrain = false; // Flag pour les contraintes
        this.cachedScale = 1; // Cache du scale pour éviter division répétée
        this.cachedRotation = 0; // Cache de la rotation
        this.imageWidth = 0; // Dimensions de l'image chargée
        this.imageHeight = 0; // Dimensions de l'image chargée
        this.animationFrame = null; // Pour throttling des updates
        this.pendingUpdate = false; // Flag pour update pending
        this.image = null; // Référence à l'image chargée pour redessin
    }

    /**
     * Génère un aperçu depuis l'éditeur (données fictives)
     */
    async generateEditorPreview(templateData, options = {}) {
        
        :', isDebugEnabled());

        if (this.isGenerating) {
            
            return null;
        }

        this.isGenerating = true;
        this.showLoadingIndicator();

        try {
            
            const formData = new FormData();
            formData.append('action', 'wp_pdf_preview_image');
            formData.append('nonce', this.nonce);
            formData.append('context', options.context || 'editor');  // Utiliser le contexte passé ou 'editor' par défaut
            formData.append('template_data', JSON.stringify(templateData));
            formData.append('quality', options.quality || 150);
            formData.append('format', options.format || 'png');

            debugLog('🎨 [JS] FormData préparé:', {
                action: 'wp_pdf_preview_image',
                nonce: this.nonce ? 'présent' : 'manquant',
                context: 'editor',
                template_data: JSON.stringify(templateData).substring(0, 100) + '...',
                quality: options.quality || 150,
                format: options.format || 'png'
            });

            const response = await fetch(this.endpoint, {
                method: 'POST',
                body: formData
            });

            debugLog('🎨 [JS] Réponse reçue:', {
                ok: response.ok,
                status: response.status,
                statusText: response.statusText,
                headers: Object.fromEntries(response.headers.entries())
            });

            if (!response.ok) {
                const errorText = await response.text();
                
                throw new Error(`HTTP ${response.status}: ${response.statusText} - ${errorText}`);
            }

            const result = await response.json();

            if (result.success) {
                
                this.cachePreview(result.data);
                this.displayPreview(result.data.image_url, 'editor');
                return result.data;
            } else {
                this.showError('Erreur lors de la génération de l\'aperçu');
                return null;
            }
        } catch (error) {
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

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP ${response.status}: ${response.statusText} - ${errorText}`);
            }

            const result = await response.json();

            if (result.success) {
                
                this.cachePreview(result.data);
                this.displayPreview(result.data.image_url, 'metabox', orderId);
                return result.data;
            } else {
                
                this.showError('Erreur lors de la génération de l\'aperçu de commande');
                return null;
            }
        } catch (error) {
            
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

        const canvas = previewModal.querySelector('#pdf-preview-canvas');
        const title = previewModal.querySelector('#pdf-preview-title');

        // Load image and draw on canvas
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            this.imageWidth = img.naturalWidth;
            this.imageHeight = img.naturalHeight;
            
            // Limiter la taille du canvas pour performance (max 800px)
            const maxCanvasSize = 800;
            let canvasWidth = img.naturalWidth;
            let canvasHeight = img.naturalHeight;
            
            if (canvasWidth > maxCanvasSize || canvasHeight > maxCanvasSize) {
                const ratio = Math.min(maxCanvasSize / canvasWidth, maxCanvasSize / canvasHeight);
                canvasWidth *= ratio;
                canvasHeight *= ratio;
            }
            
            canvas.width = canvasWidth;
            canvas.height = canvasHeight;
            canvas.style.width = canvasWidth + 'px';
            canvas.style.height = canvasHeight + 'px';
            
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, canvasWidth, canvasHeight);
            this.image = img; // Stocker l'image pour redessin
            this.updateCanvasTransform(canvas);
        };
        img.src = imageUrl;

        // Initialiser le drag/pan du canvas
        this.initCanvasDrag(canvas);
        this.updateDragState(canvas);

        if (context === 'editor') {
            title.textContent = '👁️ Aperçu du Template';
        } else {
            title.textContent = `📄 Aperçu Commande #${orderId}`;
        }

        // Mettre à jour les boutons d'action avec l'URL de l'image
        this.updateActionButtons(imageUrl);

        // Ajouter les contrôles de zoom et rotation
        this.addZoomControls(previewModal, canvas);

        // Afficher la modal en togglant la classe
        previewModal.classList.add('visible');

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
                    max-width: 95vw !important;
                    max-height: 95vh !important;
                    overflow-y: auto !important;
                    overflow-x: hidden !important;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.3) !important;
                    flex-shrink: 0 !important;
                    min-width: 300px !important;
                    position: relative !important;
                    width: 90vw !important;
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

                #pdf-preview-canvas {
                    max-width: 100% !important;
                    height: auto !important;
                    border: none !important;
                    border-radius: 0 !important;
                    display: block !important;
                    will-change: transform !important;
                    backface-visibility: hidden !important;
                    -webkit-backface-visibility: hidden !important;
                    perspective: 1000px !important;
                    -webkit-perspective: 1000px !important;
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

        // Boutons d'action dans le header
        const actionButtons = document.createElement('div');
        actionButtons.style.cssText = 'display: flex; gap: 10px; align-items: center;';

        const downloadBtn = document.createElement('button');
        downloadBtn.textContent = '📥 Télécharger';
        downloadBtn.className = 'pdf-preview-action-btn download-btn';
        downloadBtn.addEventListener('click', () => {
            const imageUrl = document.querySelector('#pdf-preview-canvas') ? null : 'placeholder'; // Will be set later
            this.downloadPreview(imageUrl);
        });

        const printBtn = document.createElement('button');
        printBtn.textContent = '🖨️ Imprimer';
        printBtn.className = 'pdf-preview-action-btn print-btn';
        printBtn.addEventListener('click', () => {
            const imageUrl = document.querySelector('#pdf-preview-canvas') ? null : 'placeholder'; // Will be set later
            this.printPreview(imageUrl);
        });

        actionButtons.appendChild(downloadBtn);
        actionButtons.appendChild(printBtn);

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
        header.appendChild(actionButtons);
        header.appendChild(closeBtn);

        // Actions container
        const actions = document.createElement('div');
        actions.id = 'pdf-preview-actions';
        actions.style.cssText = 'margin-bottom: 10px;';

        // Image container avec overflow hidden
        const imageContainer = document.createElement('div');
        imageContainer.id = 'pdf-preview-image-container';
        imageContainer.style.cssText = 'overflow: hidden; border: 1px solid #ddd; border-radius: 4px; max-height: 60vh; display: flex; align-items: center; justify-content: center;';

        const canvas = document.createElement('canvas');
        canvas.id = 'pdf-preview-canvas';
        canvas.style.cssText = 'max-width: 100%; height: auto; display: block; transform-origin: center center; transition: transform 0.3s ease;';

        imageContainer.appendChild(canvas);

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
     * Met à jour la transformation du canvas - VERSION CONTEXT 2D ULTRA-OPTIMISEE
     */
    updateCanvasTransform(canvas) {
        if (!this.image) return;

        const ctx = canvas.getContext('2d');
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;

        // Clear et redessiner avec transformations
        ctx.save();
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Appliquer les transformations dans l'ordre: translate -> rotate -> scale
        ctx.translate(centerX + this.currentPanX, centerY + this.currentPanY);
        ctx.rotate(this.cachedRotation * Math.PI / 180);
        ctx.scale(this.cachedScale, this.cachedScale);
        ctx.translate(-centerX, -centerY);
        
        // Redessiner l'image
        ctx.drawImage(this.image, 0, 0, canvas.width, canvas.height);
        ctx.restore();
    }

    /**
     * Ajoute les contrôles de zoom et rotation
     */
    addZoomControls(modal, canvas) {
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
        zoomOutBtn.addEventListener('click', () => this.zoomCanvas(canvas, -25));

        const zoomSlider = document.createElement('input');
        zoomSlider.type = 'range';
        zoomSlider.id = 'pdf-preview-zoom-slider';
        zoomSlider.min = '25';
        zoomSlider.max = '300';
        zoomSlider.value = this.currentZoom;
        zoomSlider.style.cssText = 'width: 120px;';
        zoomSlider.addEventListener('input', (e) => this.setZoomCanvas(canvas, parseInt(e.target.value)));

        const zoomValue = document.createElement('span');
        zoomValue.id = 'pdf-preview-zoom-value';
        zoomValue.textContent = this.currentZoom + '%';
        zoomValue.style.cssText = 'min-width: 45px; font-size: 12px; color: #666;';

        const zoomInBtn = document.createElement('button');
        zoomInBtn.textContent = '🔍+';
        zoomInBtn.className = 'pdf-preview-zoom-btn';
        zoomInBtn.addEventListener('click', () => this.zoomCanvas(canvas, 25));

        zoomContainer.appendChild(zoomOutBtn);
        zoomContainer.appendChild(zoomSlider);
        zoomContainer.appendChild(zoomValue);
        zoomContainer.appendChild(zoomInBtn);

        // Contrôles de rotation
        const rotateLeftBtn = document.createElement('button');
        rotateLeftBtn.textContent = '↺';
        rotateLeftBtn.title = 'Rotation gauche';
        rotateLeftBtn.className = 'pdf-preview-zoom-btn';
        rotateLeftBtn.addEventListener('click', () => this.rotateCanvas(canvas, -90));

        const rotateRightBtn = document.createElement('button');
        rotateRightBtn.textContent = '↻';
        rotateRightBtn.title = 'Rotation droite';
        rotateRightBtn.className = 'pdf-preview-zoom-btn';
        rotateRightBtn.addEventListener('click', () => this.rotateCanvas(canvas, 90));

        const resetBtn = document.createElement('button');
        resetBtn.textContent = '🔄 Reset';
        resetBtn.title = 'Réinitialiser zoom et rotation';
        resetBtn.className = 'pdf-preview-reset-btn';
        resetBtn.addEventListener('click', () => this.resetCanvas(canvas));

        controlsContainer.appendChild(zoomContainer);
        controlsContainer.appendChild(rotateLeftBtn);
        controlsContainer.appendChild(rotateRightBtn);
        controlsContainer.appendChild(resetBtn);
    }

    /**
     * Zoom le canvas
     */
    zoomCanvas(canvas, delta) {
        this.currentZoom = Math.max(25, Math.min(300, this.currentZoom + delta));
        this.cachedScale = this.currentZoom / 100; // Mettre à jour le cache
        this.updateCanvasTransform(canvas);
        this.updateZoomUI();
        this.updateDragState(canvas);
    }

    /**
     * Définit le zoom directement
     */
    setZoomCanvas(canvas, zoom) {
        this.currentZoom = zoom;
        this.cachedScale = this.currentZoom / 100; // Mettre à jour le cache
        this.updateCanvasTransform(canvas);
        this.updateZoomUI();
        this.updateDragState(canvas);
    }

    /**
     * Tourne le canvas
     */
    rotateCanvas(canvas, degrees) {
        this.currentRotation = (this.currentRotation + degrees) % 360;
        this.cachedRotation = this.currentRotation; // Mettre à jour le cache
        this.updateCanvasTransform(canvas);
    }

    /**
     * Réinitialise le canvas
     */
    resetCanvas(canvas) {
        this.currentZoom = 100;
        this.currentRotation = 0;
        this.currentPanX = 0;
        this.currentPanY = 0;
        this.cachedScale = 1; // Reset cache
        this.cachedRotation = 0; // Reset cache
        this.updateCanvasTransform(canvas);
        this.updateZoomUI();
        this.updateDragState(canvas);
    }

    /**
     * Met à jour l'état du drag (dimensions du conteneur et flag canDrag)
     */
    updateDragState(img) {
        const container = img.parentElement;
        if (container) {
            this.containerRect = container.getBoundingClientRect();
            this.canDrag = this.currentZoom > 100; // STRICTEMENT > 100%

            // Mettre à jour le curseur immédiatement
            img.style.cursor = this.canDrag ? 'grab' : 'default';
        }
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
     * Initialise le drag/pan du canvas
     */
    initCanvasDrag(canvas) {
        // Supprimer les anciens event listeners s'ils existent
        this.cleanupDragListeners();

        // Stocker les références pour pouvoir les supprimer plus tard
        this.dragListeners = {
            mousedown: (e) => this.handleMouseDown(e, canvas),
            mousemove: (e) => this.handleMouseMove(e, canvas),
            mouseup: () => this.handleMouseUp(canvas),
            mouseenter: () => this.handleMouseEnter(canvas),
            mouseleave: () => this.handleMouseLeave(canvas)
        };        // Ajouter les nouveaux event listeners
        canvas.addEventListener('mousedown', this.dragListeners.mousedown);
        document.addEventListener('mousemove', this.dragListeners.mousemove);
        document.addEventListener('mouseup', this.dragListeners.mouseup);
        canvas.addEventListener('mouseenter', this.dragListeners.mouseenter);
        canvas.addEventListener('mouseleave', this.dragListeners.mouseleave);
    }

    /**
     * Nettoie les event listeners de drag
     */
    cleanupDragListeners() {
        if (!this.dragListeners) return;

        // Supprimer tous les listeners existants
        const canvas = document.querySelector('#pdf-preview-canvas');
        if (canvas) {
            canvas.removeEventListener('mousedown', this.dragListeners.mousedown);
            canvas.removeEventListener('mouseenter', this.dragListeners.mouseenter);
            canvas.removeEventListener('mouseleave', this.dragListeners.mouseleave);
        }

        document.removeEventListener('mousemove', this.dragListeners.mousemove);
        document.removeEventListener('mouseup', this.dragListeners.mouseup);

        this.dragListeners = null;
    }

    /**
     * Gestionnaire mousedown
     */
    handleMouseDown(e, canvas) {
        // Vérifier si le pan est possible (zoom > 100% UNIQUEMENT)
        const container = canvas.parentElement;
        this.containerRect = container.getBoundingClientRect();
        const scale = this.currentZoom / 100;
        this.canDrag = this.currentZoom > 100; // STRICTEMENT > 100%

        if (!this.canDrag) {
            return;
        }

        // Pré-calculer les limites de pan pour la performance maximale
        const naturalWidth = this.imageWidth;
        const naturalHeight = this.imageHeight;
        const scaledWidth = naturalWidth * scale;
        const scaledHeight = naturalHeight * scale;
        this.maxPanX = Math.max(0, (scaledWidth - this.containerRect.width) / 2);
        this.maxPanY = Math.max(0, (scaledHeight - this.containerRect.height) / 2);

        e.preventDefault();
        this.isDragging = true;
        this.lastMouseX = e.clientX;
        this.lastMouseY = e.clientY;
        this.dragStartTime = performance.now(); // Mesure performance des drags
        this.needsConstrain = false; // Reset le flag
        canvas.style.cursor = 'grabbing';
    }

    /**
     * Gestionnaire mousemove - VERSION ULTRA-OPTIMISEE
     */
    handleMouseMove(e, canvas) {
        if (!this.isDragging || !this.canDrag) return;

        // Variables locales ultra-optimisées pour éviter les accès répétés
        const clientX = e.clientX;
        const clientY = e.clientY;
        const lastX = this.lastMouseX;
        const lastY = this.lastMouseY;
        const maxPanX = this.maxPanX;
        const maxPanY = this.maxPanY;

        // Calcul des deltas
        const deltaX = clientX - lastX;
        const deltaY = clientY - lastY;

        // Accumuler le pan immédiatement
        let newPanX = this.currentPanX + deltaX;
        let newPanY = this.currentPanY + deltaY;

        // Appliquer les contraintes INSTANTANEMENT (pas de throttling)
        if (maxPanX > 0) {
            newPanX = newPanX < -maxPanX ? -maxPanX : (newPanX > maxPanX ? maxPanX : newPanX);
        }
        if (maxPanY > 0) {
            newPanY = newPanY < -maxPanY ? -maxPanY : (newPanY > maxPanY ? maxPanY : newPanY);
        }

        // Mettre à jour les valeurs
        this.currentPanX = newPanX;
        this.currentPanY = newPanY;

        // APPLICATION THROTTLEE avec RAF pour éviter surcharge
        this.pendingUpdate = true;
        if (!this.animationFrame) {
            this.animationFrame = requestAnimationFrame(() => {
                if (this.pendingUpdate) {
                    this.updateCanvasTransform(canvas);
                    this.pendingUpdate = false;
                }
                this.animationFrame = null;
            });
        }

        // Mettre à jour les positions souris
        this.lastMouseX = clientX;
        this.lastMouseY = clientY;
    }

    /**
     * Gestionnaire mouseup
     */
    handleMouseUp(canvas) {
        if (this.isDragging) {
            this.isDragging = false;
            canvas.style.cursor = this.currentZoom > 100 ? 'grab' : 'default';

            // Mesurer et logger la performance du drag
            const dragDuration = performance.now() - this.dragStartTime;
            if (dragDuration > 10) { // Seulement pour les drags significatifs (>10ms)
                const fps = 1000 / dragDuration;
                }ms (${fps.toFixed(1)}fps)`);
            }
        }
    }

    /**
     * Gestionnaire mouseenter
     */
    handleMouseEnter(canvas) {
        // À 100% zoom exactement, le drag est TOUJOURS désactivé
        if (this.currentZoom <= 100) {
            canvas.style.cursor = 'default';
            return;
        }

        canvas.style.cursor = 'grab';
    }

    /**
     * Gestionnaire mouseleave
     */
    handleMouseLeave(canvas) {
        if (!this.isDragging) {
            canvas.style.cursor = 'default';
        }
    }

    /**
     * Met à jour les boutons d'action avec l'URL de l'image
     */
    updateActionButtons(imageUrl) {
        const downloadBtn = document.querySelector('.pdf-preview-action-btn.download-btn');
        const printBtn = document.querySelector('.pdf-preview-action-btn.print-btn');
        
        if (downloadBtn) {
            downloadBtn.onclick = () => this.downloadPreview(imageUrl);
        }
        if (printBtn) {
            printBtn.onclick = () => this.printPreview(imageUrl);
        }
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
        // No UI notification: log error to console
        
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

');
');


