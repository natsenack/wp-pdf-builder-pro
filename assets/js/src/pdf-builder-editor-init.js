/**
 * PDF Builder Pro - INITIALISATION COHÉRENTE DE L'ÉDITEUR
 * ======================================================
 * 
 * Ce fichier initialise le canvas editor de manière cohérente.
 * Il s'assure que tous les modules sont en place et fonctionnent ensemble.
 */



// 1. VÉRIFIER LES DÉPENDANCES GLOBALES
function verifyGlobalDependencies() {
    
    
    const dependencies = {
        'jQuery': typeof jQuery !== 'undefined',
        'pdfBuilderAjax': typeof pdfBuilderAjax !== 'undefined',
        'PDFBuilderPro': typeof window.PDFBuilderPro !== 'undefined' || typeof PDFBuilderPro !== 'undefined'
    };
    
    
    
    return {
        jquery: jQuery,
        ajax: typeof pdfBuilderAjax !== 'undefined' ? pdfBuilderAjax : null,
        pdfBuilder: typeof window.PDFBuilderPro !== 'undefined' ? window.PDFBuilderPro : (typeof PDFBuilderPro !== 'undefined' ? PDFBuilderPro : null)
    };
}

// 2. INITIALISER LE CANVAS
function initializeCanvas(deps) {
    
    
    if (!deps.pdfBuilder) {
        
        return false;
    }
    
    const canvas = document.getElementById('pdf-canvas');
    if (!canvas) {
        
        return false;
    }
    
    
    
    // Initialiser avec les options du template
    const templateId = new URLSearchParams(window.location.search).get('template_id');
    const options = {
        containerId: 'pdf-canvas',
        templateId: templateId || null,
        width: 595,  // A4
        height: 842,  // A4
        zoom: 1,
        gridEnabled: true,
        snapToGrid: true,
        gridSize: 10
    };
    
    // Initialiser le PDFBuilderPro
    if (deps.pdfBuilder.init) {
        deps.pdfBuilder.init(options);
        
    } else if (deps.pdfBuilder.PDFCanvasVanilla) {
        const canvas = new deps.pdfBuilder.PDFCanvasVanilla(options);
        window.pdfCanvasInstance = canvas;
        canvas.init();
        
    }
    
    return true;
}

// 3. INITIALISER LA BARRE D'OUTILS
function initializeToolbar(deps) {
    
    
    const toolbar = document.querySelector('.pdf-builder-toolbar');
    if (!toolbar) {
        
        return false;
    }
    
    // Initialiser les événements des boutons
    toolbar.querySelectorAll('.toolbar-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.dataset.action;
            
            
            if (window.pdfCanvasInstance) {
                if (typeof window.pdfCanvasInstance[action] === 'function') {
                    window.pdfCanvasInstance[action]();
                }
            }
        });
    });
    
    
    return true;
}

// 4. INITIALISER LA BIBLIOTHÈQUE D'ÉLÉMENTS
function initializeElementLibrary(deps) {
    
    
    if (!deps.pdfBuilder || !deps.pdfBuilder.ELEMENT_LIBRARY) {
        
        return false;
    }
    
    const library = document.querySelector('.element-library');
    if (!library) {
        
        return false;
    }
    
    // Remplir la bibliothèque avec les éléments disponibles
    const elements = deps.pdfBuilder.getAllElementsFlat ? 
        deps.pdfBuilder.getAllElementsFlat() : 
        (deps.pdfBuilder.getAllElements ? deps.pdfBuilder.getAllElements() : []);
    
    
    
    // Créer les items de la bibliothèque
    elements.forEach(element => {
        const item = document.createElement('div');
        item.className = 'library-item';
        item.draggable = true;
        item.dataset.type = element.type;
        item.dataset.category = element.category || 'base';
        item.innerHTML = `
            <span class="library-icon">${element.icon || '📦'}</span>
            <span class="library-label">${element.label || element.type}</span>
        `;
        
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.effectAllowed = 'copy';
            e.dataTransfer.setData('application/json', JSON.stringify({
                type: 'new-element',
                elementType: element.type,
                elementData: element
            }));
        });
        
        library.appendChild(item);
    });
    
    
    return true;
}

// 5. INITIALISER LES ÉVÉNEMENTS DU CANVAS
function initializeCanvasEvents(deps) {
    
    
    const canvas = document.getElementById('pdf-canvas');
    if (!canvas) {
        
        return false;
    }
    
    // Drop event
    canvas.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
        canvas.classList.add('drag-over');
    });
    
    canvas.addEventListener('dragleave', () => {
        canvas.classList.remove('drag-over');
    });
    
    canvas.addEventListener('drop', (e) => {
        e.preventDefault();
        canvas.classList.remove('drag-over');
        
        try {
            const data = JSON.parse(e.dataTransfer.getData('application/json'));
            if (data.type === 'new-element') {
                const rect = canvas.getBoundingClientRect();
                const x = (e.clientX - rect.left) / (window.pdfCanvasInstance?.zoom || 1);
                const y = (e.clientY - rect.top) / (window.pdfCanvasInstance?.zoom || 1);
                
                
                
                if (window.pdfCanvasInstance && typeof window.pdfCanvasInstance.addElement === 'function') {
                    window.pdfCanvasInstance.addElement(data.elementType, { x, y, ...data.elementData });
                }
            }
        } catch (error) {
            
        }
    });
    
    
    return true;
}

// 6. INITIALISER LES PANNEAUX LATÉRAUX
function initializeSidePanels(deps) {
    
    
    const propertiesPanel = document.querySelector('.properties-panel');
    if (!propertiesPanel) {
        
        return false;
    }
    
    
    return true;
}

// 7. INITIALISER LE SYSTÈME D'AUTO-SAUVEGARDE
function initializeAutoSave(deps) {
    
    
    let autoSaveTimer;
    let hasChanges = false;
    
    // Marquer comme changé
    document.addEventListener('change', () => {
        hasChanges = true;
    });
    
    // Auto-save toutes les 30 secondes
    autoSaveTimer = setInterval(() => {
        if (hasChanges && window.pdfCanvasInstance) {
            
            if (typeof window.pdfCanvasInstance.save === 'function') {
                window.pdfCanvasInstance.save();
                hasChanges = false;
            }
        }
    }, 30000);
    
    
}

// FONCTION D'INITIALISATION PRINCIPALE
function initializeEditor() {
    
    
    
    
    try {
        // 1. Vérifier les dépendances
        const deps = verifyGlobalDependencies();
        if (!deps.pdfBuilder) {
            
            // Attendre le chargement
            setTimeout(initializeEditor, 500);
            return;
        }
        
        // 2. Initialiser tous les modules
        initializeCanvas(deps);
        initializeToolbar(deps);
        initializeElementLibrary(deps);
        initializeCanvasEvents(deps);
        initializeSidePanels(deps);
        initializeAutoSave(deps);
        
        // 3. Masquer le loading et afficher l'éditeur
        const loading = document.getElementById('pdf-builder-loading');
        const editor = document.getElementById('pdf-builder-editor');
        if (loading) loading.style.display = 'none';
        if (editor) editor.style.display = 'block';
        
        
        
        
        
    } catch (error) {
        
    }
}

// DÉCLENCHER L'INITIALISATION
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeEditor);
} else {
    // DOM est déjà chargé
    setTimeout(initializeEditor, 100);
}

// Exposition globale pour le débogage
window.PDFBuilderEditorInit = {
    initialize: initializeEditor,
    verify: verifyGlobalDependencies
};


