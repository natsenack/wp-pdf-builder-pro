/**
 * Event Emitter - Système d'événements personnalisé
 * Gestion flexible des événements avec pattern observer
 */

export class EventEmitter {
    constructor() {
        this.events = new Map();
        this.maxListeners = 10; // Limite par défaut
        this.wildcardEvents = new Set();
    }

    /**
     * Ajout d'un écouteur d'événement
     */
    on(event, listener, context = null) {
        if (typeof listener !== 'function') {
            throw new TypeError('Listener must be a function');
        }

        if (!this.events.has(event)) {
            this.events.set(event, []);
        }

        const listeners = this.events.get(event);

        // Vérification de la limite
        if (listeners.length >= this.maxListeners) {
            console.warn(`Max listeners (${this.maxListeners}) reached for event: ${event}`);
        }

        // Évite les doublons
        const existingIndex = listeners.findIndex(l => l.listener === listener);
        if (existingIndex === -1) {
            listeners.push({
                listener: listener,
                context: context,
                once: false
            });
        }

        return this;
    }

    /**
     * Ajout d'un écouteur unique (se déclenche une fois)
     */
    once(event, listener, context = null) {
        if (typeof listener !== 'function') {
            throw new TypeError('Listener must be a function');
        }

        if (!this.events.has(event)) {
            this.events.set(event, []);
        }

        const listeners = this.events.get(event);
        listeners.push({
            listener: listener,
            context: context,
            once: true
        });

        return this;
    }

    /**
     * Suppression d'un écouteur
     */
    off(event, listener) {
        if (!this.events.has(event)) return this;

        if (!listener) {
            // Supprimer tous les écouteurs de l'événement
            this.events.delete(event);
            return this;
        }

        const listeners = this.events.get(event);
        const filtered = listeners.filter(l => l.listener !== listener);

        if (filtered.length === 0) {
            this.events.delete(event);
        } else {
            this.events.set(event, filtered);
        }

        return this;
    }

    /**
     * Émission d'un événement
     */
    emit(event, ...args) {
        const listeners = this.events.get(event);
        if (!listeners || listeners.length === 0) {
            // Vérifier les wildcards
            this._emitWildcard(event, args);
            return false;
        }

        // Copie pour éviter les modifications pendant l'émission
        const listenersCopy = [...listeners];

        listenersCopy.forEach((listenerObj, index) => {
            try {
                const { listener, context, once } = listenerObj;

                if (context) {
                    listener.apply(context, args);
                } else {
                    listener(...args);
                }

                // Supprimer les écouteurs "once"
                if (once) {
                    listeners.splice(listeners.indexOf(listenerObj), 1);
                }

            } catch (error) {
                console.error(`Error in event listener for '${event}':`, error);
            }
        });

        // Nettoyer si plus d'écouteurs
        if (listeners.length === 0) {
            this.events.delete(event);
        }

        // Émettre les wildcards aussi
        this._emitWildcard(event, args);

        return true;
    }

    /**
     * Émission d'événements wildcard (*)
     * @private
     */
    _emitWildcard(event, args) {
        if (!this.wildcardEvents.has('*')) return;

        const wildcardListeners = this.events.get('*') || [];
        wildcardListeners.forEach(listenerObj => {
            try {
                const { listener, context } = listenerObj;
                if (context) {
                    listener.apply(context, [event, ...args]);
                } else {
                    listener(event, ...args);
                }
            } catch (error) {
                console.error(`Error in wildcard listener for '${event}':`, error);
            }
        });
    }

    /**
     * Écouteur pour tous les événements
     */
    onAny(listener, context = null) {
        this.wildcardEvents.add('*');
        return this.on('*', listener, context);
    }

    /**
     * Suppression de l'écouteur universel
     */
    offAny(listener) {
        return this.off('*', listener);
    }

    /**
     * Nombre d'écouteurs pour un événement
     */
    listenerCount(event) {
        return this.events.has(event) ? this.events.get(event).length : 0;
    }

    /**
     * Liste des événements actifs
     */
    eventNames() {
        return Array.from(this.events.keys());
    }

    /**
     * Liste des écouteurs d'un événement
     */
    listeners(event) {
        if (!this.events.has(event)) return [];
        return this.events.get(event).map(l => l.listener);
    }

    /**
     * Suppression de tous les écouteurs
     */
    removeAllListeners(event) {
        if (event) {
            this.events.delete(event);
        } else {
            this.events.clear();
            this.wildcardEvents.clear();
        }
        return this;
    }

    /**
     * Configuration du nombre maximum d'écouteurs
     */
    setMaxListeners(n) {
        if (typeof n !== 'number' || n < 0) {
            throw new TypeError('Max listeners must be a positive number');
        }
        this.maxListeners = n;
        return this;
    }

    /**
     * Récupération du nombre maximum d'écouteurs
     */
    getMaxListeners() {
        return this.maxListeners;
    }

    /**
     * Vérification d'événements actifs
     */
    hasListeners(event) {
        return this.events.has(event) && this.events.get(event).length > 0;
    }

    /**
     * Statistiques des événements
     */
    getStats() {
        const stats = {
            totalEvents: this.events.size,
            totalListeners: 0,
            events: {}
        };

        this.events.forEach((listeners, event) => {
            stats.events[event] = listeners.length;
            stats.totalListeners += listeners.length;
        });

        return stats;
    }

    /**
     * Debug des événements
     */
    debug() {
        console.group('EventEmitter Debug');
        console.log('Events:', this.eventNames());
        console.log('Stats:', this.getStats());

        this.events.forEach((listeners, event) => {
            console.group(`Event: ${event} (${listeners.length} listeners)`);
            listeners.forEach((l, i) => {
                console.log(`${i + 1}. ${l.listener.name || 'anonymous'}${l.once ? ' (once)' : ''}`);
            });
            console.groupEnd();
        });
        console.groupEnd();
    }

    /**
     * Nettoyage périodique (optionnel)
     */
    cleanup() {
        // Supprimer les événements sans écouteurs
        for (const [event, listeners] of this.events) {
            if (listeners.length === 0) {
                this.events.delete(event);
            }
        }
    }

    /**
     * Destruction complète
     */
    destroy() {
        this.events.clear();
        this.wildcardEvents.clear();
    }
}

// Instance globale pour utilisation facile
export const eventEmitter = new EventEmitter();