/**
 * PDF Canvas Event Manager - Gestionnaire d'événements DOM natifs
 * Gestion centralisée des interactions utilisateur (souris, clavier, tactile)
 */

export class PDFCanvasEventManager {
    constructor(canvas, canvasInstance) {
        this.canvas = canvas;
        this.canvasInstance = canvasInstance;
        this.eventListeners = new Map();

        // État des événements
        this.mouseState = {
            isDown: false,
            button: null,
            position: { x: 0, y: 0 },
            lastPosition: { x: 0, y: 0 },
            dragStart: null
        };

        this.keyboardState = {
            keys: new Set(),
            modifiers: {
                ctrl: false,
                shift: false,
                alt: false,
                meta: false
            }
        };

        this.touchState = {
            touches: new Map(),
            gesture: null
        };

        // Configuration des événements
        this.eventConfig = {
            enableKeyboard: true,
            enableMouse: true,
            enableTouch: true,
            enableWheel: true,
            enableContextMenu: false,
            throttleMouseMove: true,
            mouseMoveThrottleMs: 16 // ~60fps
        };

        // Timers pour le throttling
        this.throttleTimers = new Map();

        this.init();
    }

    /**
     * Initialise les gestionnaires d'événements
     */
    init() {
        this.attachMouseEvents();
        this.attachKeyboardEvents();
        this.attachTouchEvents();
        this.attachWheelEvents();
        this.attachFocusEvents();
    }

    /**
     * Attache les événements souris
     */
    attachMouseEvents() {
        if (!this.eventConfig.enableMouse) return;

        const mouseEvents = [
            'mousedown', 'mouseup', 'mousemove',
            'mouseenter', 'mouseleave', 'mouseover', 'mouseout',
            'click', 'dblclick', 'contextmenu'
        ];

        mouseEvents.forEach(eventType => {
            const handler = this.createMouseHandler(eventType);
            this.canvas.addEventListener(eventType, handler, { passive: false });
            this.eventListeners.set(`mouse_${eventType}`, handler);
        });
    }

    /**
     * Attache les événements clavier
     */
    attachKeyboardEvents() {
        if (!this.eventConfig.enableKeyboard) return;

        const keyboardEvents = ['keydown', 'keyup', 'keypress'];

        keyboardEvents.forEach(eventType => {
            const handler = this.createKeyboardHandler(eventType);
            document.addEventListener(eventType, handler, { passive: false });
            this.eventListeners.set(`keyboard_${eventType}`, handler);
        });
    }

    /**
     * Attache les événements tactiles
     */
    attachTouchEvents() {
        if (!this.eventConfig.enableTouch) return;

        const touchEvents = [
            'touchstart', 'touchend', 'touchmove',
            'touchcancel', 'gesturestart', 'gesturechange', 'gestureend'
        ];

        touchEvents.forEach(eventType => {
            const handler = this.createTouchHandler(eventType);
            this.canvas.addEventListener(eventType, handler, {
                passive: false,
                capture: true
            });
            this.eventListeners.set(`touch_${eventType}`, handler);
        });
    }

    /**
     * Attache les événements de roulette
     */
    attachWheelEvents() {
        if (!this.eventConfig.enableWheel) return;

        const handler = this.createWheelHandler();
        this.canvas.addEventListener('wheel', handler, { passive: false });
        this.eventListeners.set('wheel', handler);
    }

    /**
     * Attache les événements de focus
     */
    attachFocusEvents() {
        const focusEvents = ['focus', 'blur', 'focusin', 'focusout'];

        focusEvents.forEach(eventType => {
            const handler = this.createFocusHandler(eventType);
            this.canvas.addEventListener(eventType, handler);
            this.eventListeners.set(`focus_${eventType}`, handler);
        });
    }

    /**
     * Crée un gestionnaire d'événement souris
     */
    createMouseHandler(eventType) {
        return (event) => {
            // Empêcher le menu contextuel si désactivé
            if (eventType === 'contextmenu' && !this.eventConfig.enableContextMenu) {
                event.preventDefault();
                return;
            }

            // Mettre à jour l'état de la souris
            this.updateMouseState(event, eventType);

            // Créer l'événement normalisé
            const normalizedEvent = this.normalizeMouseEvent(event, eventType);

            // Throttler les événements mousemove si configuré
            if (eventType === 'mousemove' && this.eventConfig.throttleMouseMove) {
                this.throttleEvent('mousemove', () => {
                    this.dispatchEvent(normalizedEvent);
                }, this.eventConfig.mouseMoveThrottleMs);
            } else {
                this.dispatchEvent(normalizedEvent);
            }

            // Empêcher la propagation si nécessaire
            if (this.shouldPreventDefault(event, eventType)) {
                event.preventDefault();
            }
        };
    }

    /**
     * Met à jour l'état de la souris
     */
    updateMouseState(event, eventType) {
        const rect = this.canvas.getBoundingClientRect();
        const scaleX = this.canvas.width / rect.width;
        const scaleY = this.canvas.height / rect.height;

        this.mouseState.lastPosition = { ...this.mouseState.position };
        this.mouseState.position = {
            x: (event.clientX - rect.left) * scaleX,
            y: (event.clientY - rect.top) * scaleY
        };

        switch (eventType) {
            case 'mousedown':
                this.mouseState.isDown = true;
                this.mouseState.button = event.button;
                this.mouseState.dragStart = { ...this.mouseState.position };
                break;
            case 'mouseup':
                this.mouseState.isDown = false;
                this.mouseState.button = null;
                this.mouseState.dragStart = null;
                break;
        }
    }

    /**
     * Normalise un événement souris
     */
    normalizeMouseEvent(event, eventType) {
        return {
            type: eventType,
            originalEvent: event,
            position: { ...this.mouseState.position },
            delta: {
                x: this.mouseState.position.x - this.mouseState.lastPosition.x,
                y: this.mouseState.position.y - this.mouseState.lastPosition.y
            },
            button: event.button,
            buttons: event.buttons,
            isDown: this.mouseState.isDown,
            modifiers: { ...this.keyboardState.modifiers },
            dragStart: this.mouseState.dragStart ? { ...this.mouseState.dragStart } : null,
            timestamp: Date.now()
        };
    }

    /**
     * Crée un gestionnaire d'événement clavier
     */
    createKeyboardHandler(eventType) {
        return (event) => {
            this.updateKeyboardState(event, eventType);
            const normalizedEvent = this.normalizeKeyboardEvent(event, eventType);
            this.dispatchEvent(normalizedEvent);

            if (this.shouldPreventDefault(event, eventType)) {
                event.preventDefault();
            }
        };
    }

    /**
     * Met à jour l'état du clavier
     */
    updateKeyboardState(event, eventType) {
        const key = event.key.toLowerCase();

        switch (eventType) {
            case 'keydown':
                this.keyboardState.keys.add(key);
                break;
            case 'keyup':
                this.keyboardState.keys.delete(key);
                break;
        }

        // Mettre à jour les modificateurs
        this.keyboardState.modifiers = {
            ctrl: event.ctrlKey,
            shift: event.shiftKey,
            alt: event.altKey,
            meta: event.metaKey
        };
    }

    /**
     * Normalise un événement clavier
     */
    normalizeKeyboardEvent(event, eventType) {
        return {
            type: eventType,
            originalEvent: event,
            key: event.key,
            code: event.code,
            modifiers: { ...this.keyboardState.modifiers },
            isRepeat: event.repeat,
            timestamp: Date.now()
        };
    }

    /**
     * Crée un gestionnaire d'événement tactile
     */
    createTouchHandler(eventType) {
        return (event) => {
            this.updateTouchState(event, eventType);
            const normalizedEvent = this.normalizeTouchEvent(event, eventType);
            this.dispatchEvent(normalizedEvent);

            if (this.shouldPreventDefault(event, eventType)) {
                event.preventDefault();
            }
        };
    }

    /**
     * Met à jour l'état tactile
     */
    updateTouchState(event, eventType) {
        const rect = this.canvas.getBoundingClientRect();
        const scaleX = this.canvas.width / rect.width;
        const scaleY = this.canvas.height / rect.height;

        switch (eventType) {
            case 'touchstart':
                Array.from(event.touches).forEach(touch => {
                    const touchPoint = {
                        id: touch.identifier,
                        x: (touch.clientX - rect.left) * scaleX,
                        y: (touch.clientY - rect.top) * scaleY,
                        force: touch.force || 1
                    };
                    this.touchState.touches.set(touch.identifier, touchPoint);
                });
                break;
            case 'touchend':
            case 'touchcancel':
                Array.from(event.changedTouches).forEach(touch => {
                    this.touchState.touches.delete(touch.identifier);
                });
                break;
            case 'touchmove':
                Array.from(event.touches).forEach(touch => {
                    if (this.touchState.touches.has(touch.identifier)) {
                        const touchPoint = this.touchState.touches.get(touch.identifier);
                        touchPoint.x = (touch.clientX - rect.left) * scaleX;
                        touchPoint.y = (touch.clientY - rect.top) * scaleY;
                        touchPoint.force = touch.force || 1;
                    }
                });
                break;
        }

        // Détecter les gestes
        this.detectGestures(event);
    }

    /**
     * Détecte les gestes tactiles
     */
    detectGestures(event) {
        const touches = Array.from(this.touchState.touches.values());

        if (touches.length === 2) {
            // Pinch to zoom
            const touch1 = touches[0];
            const touch2 = touches[1];
            const distance = Math.sqrt(
                Math.pow(touch2.x - touch1.x, 2) + Math.pow(touch2.y - touch1.y, 2)
            );

            if (!this.touchState.gesture) {
                this.touchState.gesture = {
                    type: 'pinch',
                    initialDistance: distance,
                    currentDistance: distance
                };
            } else {
                this.touchState.gesture.currentDistance = distance;
            }
        } else {
            this.touchState.gesture = null;
        }
    }

    /**
     * Normalise un événement tactile
     */
    normalizeTouchEvent(event, eventType) {
        return {
            type: eventType,
            originalEvent: event,
            touches: Array.from(this.touchState.touches.values()),
            changedTouches: Array.from(event.changedTouches || []).map(touch => ({
                id: touch.identifier,
                x: touch.clientX,
                y: touch.clientY,
                force: touch.force || 1
            })),
            gesture: this.touchState.gesture,
            timestamp: Date.now()
        };
    }

    /**
     * Crée un gestionnaire d'événement de roulette
     */
    createWheelHandler() {
        return (event) => {
            const normalizedEvent = this.normalizeWheelEvent(event);
            this.dispatchEvent(normalizedEvent);

            if (this.shouldPreventDefault(event, 'wheel')) {
                event.preventDefault();
            }
        };
    }

    /**
     * Normalise un événement de roulette
     */
    normalizeWheelEvent(event) {
        return {
            type: 'wheel',
            originalEvent: event,
            deltaX: event.deltaX,
            deltaY: event.deltaY,
            deltaZ: event.deltaZ,
            deltaMode: event.deltaMode,
            modifiers: { ...this.keyboardState.modifiers },
            timestamp: Date.now()
        };
    }

    /**
     * Crée un gestionnaire d'événement de focus
     */
    createFocusHandler(eventType) {
        return (event) => {
            const normalizedEvent = {
                type: eventType,
                originalEvent: event,
                timestamp: Date.now()
            };
            this.dispatchEvent(normalizedEvent);
        };
    }

    /**
     * Détermine si l'événement doit être empêché
     */
    shouldPreventDefault(event, eventType) {
        // Empêcher le scroll par défaut sur le canvas
        if (eventType === 'wheel') return true;

        // Empêcher le menu contextuel si désactivé
        if (eventType === 'contextmenu' && !this.eventConfig.enableContextMenu) return true;

        // Empêcher certains raccourcis clavier
        if (eventType === 'keydown') {
            const preventKeys = ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Space'];
            if (preventKeys.includes(event.code)) return true;
        }

        return false;
    }

    /**
     * Dispatche un événement vers l'instance canvas
     */
    dispatchEvent(normalizedEvent) {
        if (!this.canvasInstance) return;

        // Mapper les événements vers les méthodes de l'instance
        const eventMap = {
            mousedown: 'handleMouseDown',
            mouseup: 'handleMouseUp',
            mousemove: 'handleMouseMove',
            click: 'handleClick',
            dblclick: 'handleDoubleClick',
            keydown: 'handleKeyDown',
            keyup: 'handleKeyUp',
            wheel: 'handleWheel',
            touchstart: 'handleTouchStart',
            touchend: 'handleTouchEnd',
            touchmove: 'handleTouchMove'
        };

        const methodName = eventMap[normalizedEvent.type];
        if (methodName && typeof this.canvasInstance[methodName] === 'function') {
            this.canvasInstance[methodName](normalizedEvent);
        }

        // Émettre un événement personnalisé pour les listeners externes
        this.emitCustomEvent(normalizedEvent);
    }

    /**
     * Émet un événement personnalisé
     */
    emitCustomEvent(normalizedEvent) {
        const customEvent = new CustomEvent('pdf-canvas-event', {
            detail: normalizedEvent,
            bubbles: true,
            cancelable: true
        });

        this.canvas.dispatchEvent(customEvent);
    }

    /**
     * Throttle un événement
     */
    throttleEvent(key, callback, delay) {
        if (this.throttleTimers.has(key)) {
            clearTimeout(this.throttleTimers.get(key));
        }

        const timer = setTimeout(() => {
            callback();
            this.throttleTimers.delete(key);
        }, delay);

        this.throttleTimers.set(key, timer);
    }

    /**
     * Ajoute un listener d'événement personnalisé
     */
    addEventListener(eventType, callback) {
        this.canvas.addEventListener(`pdf-canvas-${eventType}`, callback);
    }

    /**
     * Supprime un listener d'événement personnalisé
     */
    removeEventListener(eventType, callback) {
        this.canvas.removeEventListener(`pdf-canvas-${eventType}`, callback);
    }

    /**
     * Obtient l'état actuel des événements
     */
    getEventState() {
        return {
            mouse: { ...this.mouseState },
            keyboard: {
                keys: Array.from(this.keyboardState.keys),
                modifiers: { ...this.keyboardState.modifiers }
            },
            touch: {
                touches: Array.from(this.touchState.touches.values()),
                gesture: this.touchState.gesture
            }
        };
    }

    /**
     * Configure les options d'événements
     */
    configure(options) {
        this.eventConfig = { ...this.eventConfig, ...options };
    }

    /**
     * Simule un événement
     */
    simulateEvent(eventType, eventData) {
        const simulatedEvent = {
            type: eventType,
            simulated: true,
            ...eventData,
            timestamp: Date.now()
        };

        this.dispatchEvent(simulatedEvent);
    }

    /**
     * Nettoie les ressources
     */
    dispose() {
        // Supprimer tous les listeners
        this.eventListeners.forEach((handler, key) => {
            const [type, eventName] = key.split('_');
            const target = type === 'keyboard' ? document : this.canvas;
            target.removeEventListener(eventName, handler);
        });

        this.eventListeners.clear();
        this.throttleTimers.clear();

        // Réinitialiser les états
        this.mouseState = {
            isDown: false,
            button: null,
            position: { x: 0, y: 0 },
            lastPosition: { x: 0, y: 0 },
            dragStart: null
        };

        this.keyboardState = {
            keys: new Set(),
            modifiers: {
                ctrl: false,
                shift: false,
                alt: false,
                meta: false
            }
        };

        this.touchState = {
            touches: new Map(),
            gesture: null
        };
    }
}

export default PDFCanvasEventManager;