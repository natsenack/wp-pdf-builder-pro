/**
 * Template Manager - Gestion des templates PDF
 * Sauvegarde, chargement et gestion des modèles
 */

export class TemplateManager {
    constructor(pdfBuilder) {
        this.pdfBuilder = pdfBuilder;
        this.templates = new Map();
        this.currentTemplate = null;
        this.storageKey = 'pdf-builder-templates';
        this.autoSaveEnabled = true;
        this.autoSaveInterval = 30000; // 30 secondes
        this.autoSaveTimer = null;
    }

    /**
     * Initialisation
     */
    init() {
        this._loadFromStorage();
        if (this.autoSaveEnabled) {
            this._startAutoSave();
        }
    }

    /**
     * Création d'un nouveau template
     */
    createTemplate(name, description = '') {
        const template = {
            id: `template_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
            name,
            description,
            elements: [],
            metadata: {
                createdAt: Date.now(),
                updatedAt: Date.now(),
                version: '1.0',
                author: 'PDF Builder',
                width: this.pdfBuilder.options.width,
                height: this.pdfBuilder.options.height
            }
        };

        this.templates.set(template.id, template);
        this.currentTemplate = template;
        this._saveToStorage();

        this.pdfBuilder.emit('templatecreated', { template });
        return template.id;
    }

    /**
     * Sauvegarde du template actuel
     */
    saveTemplate() {
        if (!this.currentTemplate) {
            throw new Error('Aucun template actif à sauvegarder');
        }

        // Mise à jour des éléments
        this.currentTemplate.elements = this.pdfBuilder.elementManager.getAllElements();
        this.currentTemplate.metadata.updatedAt = Date.now();

        this.templates.set(this.currentTemplate.id, this.currentTemplate);
        this._saveToStorage();

        this.pdfBuilder.emit('templatesaved', { template: this.currentTemplate });
        return this.currentTemplate.id;
    }

    /**
     * Chargement d'un template
     */
    loadTemplate(templateId) {
        const template = this.templates.get(templateId);
        if (!template) {
            throw new Error(`Template ${templateId} introuvable`);
        }

        // Nettoyage du canvas actuel
        this.pdfBuilder.elementManager.elements.clear();

        // Chargement des éléments
        template.elements.forEach(elementData => {
            try {
                this.pdfBuilder.elementManager.addElement(elementData.type, elementData);
            } catch (error) {
                console.warn('Erreur lors du chargement d\'un élément:', error);
            }
        });

        this.currentTemplate = template;
        this.pdfBuilder.render();

        this.pdfBuilder.emit('templateloaded', { template });
        return templateId;
    }

    /**
     * Suppression d'un template
     */
    deleteTemplate(templateId) {
        if (!this.templates.has(templateId)) {
            return false;
        }

        const template = this.templates.get(templateId);
        this.templates.delete(templateId);

        if (this.currentTemplate && this.currentTemplate.id === templateId) {
            this.currentTemplate = null;
        }

        this._saveToStorage();
        this.pdfBuilder.emit('templatedeleted', { template });

        return true;
    }

    /**
     * Duplication d'un template
     */
    duplicateTemplate(templateId, newName) {
        const original = this.templates.get(templateId);
        if (!original) {
            throw new Error(`Template ${templateId} introuvable`);
        }

        const duplicate = {
            ...original,
            id: `template_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
            name: newName || `${original.name} (copie)`,
            metadata: {
                ...original.metadata,
                createdAt: Date.now(),
                updatedAt: Date.now()
            }
        };

        this.templates.set(duplicate.id, duplicate);
        this._saveToStorage();

        this.pdfBuilder.emit('templateduplicated', { original, duplicate });
        return duplicate.id;
    }

    /**
     * Export d'un template
     */
    exportTemplate(templateId) {
        const template = this.templates.get(templateId);
        if (!template) {
            throw new Error(`Template ${templateId} introuvable`);
        }

        const exportData = {
            ...template,
            exportedAt: Date.now(),
            version: '1.0'
        };

        // Téléchargement du fichier JSON
        const blob = new Blob([JSON.stringify(exportData, null, 2)], {
            type: 'application/json'
        });

        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${template.name.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        this.pdfBuilder.emit('templateexported', { template });
    }

    /**
     * Import d'un template
     */
    async importTemplate(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();

            reader.onload = (event) => {
                try {
                    const data = JSON.parse(event.target.result);

                    // Validation basique
                    if (!data.id || !data.name || !data.elements) {
                        throw new Error('Format de fichier invalide');
                    }

                    // Générer un nouvel ID pour éviter les conflits
                    const template = {
                        ...data,
                        id: `template_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
                        metadata: {
                            ...data.metadata,
                            importedAt: Date.now()
                        }
                    };

                    this.templates.set(template.id, template);
                    this._saveToStorage();

                    this.pdfBuilder.emit('templateimported', { template });
                    resolve(template.id);

                } catch (error) {
                    reject(new Error(`Erreur lors de l'import: ${error.message}`));
                }
            };

            reader.onerror = () => reject(new Error('Erreur de lecture du fichier'));
            reader.readAsText(file);
        });
    }

    /**
     * Liste des templates
     */
    getTemplates() {
        return Array.from(this.templates.values()).map(template => ({
            id: template.id,
            name: template.name,
            description: template.description,
            elementCount: template.elements.length,
            createdAt: template.metadata.createdAt,
            updatedAt: template.metadata.updatedAt
        }));
    }

    /**
     * Recherche de templates
     */
    searchTemplates(query) {
        const lowercaseQuery = query.toLowerCase();
        return this.getTemplates().filter(template =>
            template.name.toLowerCase().includes(lowercaseQuery) ||
            template.description.toLowerCase().includes(lowercaseQuery)
        );
    }

    /**
     * Template actuel
     */
    getCurrentTemplate() {
        return this.currentTemplate;
    }

    /**
     * Statistiques des templates
     */
    getStats() {
        const templates = Array.from(this.templates.values());
        return {
            total: templates.length,
            totalElements: templates.reduce((sum, t) => sum + t.elements.length, 0),
            averageElements: templates.length > 0 ?
                Math.round(templates.reduce((sum, t) => sum + t.elements.length, 0) / templates.length) : 0,
            lastModified: templates.length > 0 ?
                Math.max(...templates.map(t => t.metadata.updatedAt)) : null
        };
    }

    /**
     * Sauvegarde automatique
     * @private
     */
    _startAutoSave() {
        this.autoSaveTimer = setInterval(() => {
            if (this.currentTemplate) {
                this.saveTemplate();
            }
        }, this.autoSaveInterval);
    }

    /**
     * Arrêt de la sauvegarde automatique
     */
    stopAutoSave() {
        if (this.autoSaveTimer) {
            clearInterval(this.autoSaveTimer);
            this.autoSaveTimer = null;
        }
    }

    /**
     * Sauvegarde dans le localStorage
     * @private
     */
    _saveToStorage() {
        try {
            const data = {
                templates: Array.from(this.templates.entries()),
                currentTemplateId: this.currentTemplate?.id || null,
                savedAt: Date.now()
            };
            localStorage.setItem(this.storageKey, JSON.stringify(data));
        } catch (error) {
            console.warn('Erreur lors de la sauvegarde:', error);
        }
    }

    /**
     * Chargement depuis le localStorage
     * @private
     */
    _loadFromStorage() {
        try {
            const data = JSON.parse(localStorage.getItem(this.storageKey));
            if (data && data.templates) {
                this.templates = new Map(data.templates);
                if (data.currentTemplateId) {
                    this.currentTemplate = this.templates.get(data.currentTemplateId) || null;
                }
            }
        } catch (error) {
            console.warn('Erreur lors du chargement:', error);
        }
    }

    /**
     * Nettoyage du stockage
     */
    clearStorage() {
        localStorage.removeItem(this.storageKey);
        this.templates.clear();
        this.currentTemplate = null;
        this.pdfBuilder.emit('storagecleared');
    }

    /**
     * Destruction
     */
    destroy() {
        this.stopAutoSave();
        this.templates.clear();
        this.currentTemplate = null;
    }
}