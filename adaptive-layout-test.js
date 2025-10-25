// Test temporaire du système adaptatif - À supprimer après test
// Version: 1.1 - Correction du chemin
(function() {
    'use strict';

    // Attendre que le DOM soit chargé
    document.addEventListener('DOMContentLoaded', function() {
        // Créer un panneau de contrôle pour tester le redimensionnement
        const testPanel = document.createElement('div');
        testPanel.id = 'adaptive-test-panel';
        testPanel.innerHTML = `
            <div style="
                position: fixed;
                top: 20px;
                right: 20px;
                background: #1f2937;
                color: white;
                padding: 15px;
                border-radius: 8px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.3);
                z-index: 10000;
                font-family: monospace;
                font-size: 12px;
                min-width: 200px;
            ">
                <h4 style="margin: 0 0 10px 0; color: #60a5fa;">🧪 Test Layout Adaptatif</h4>
                <div style="margin-bottom: 10px;">
                    <label>Largeur sidebar: <span id="sidebar-width">auto</span>px</label>
                </div>
                <div style="margin-bottom: 10px;">
                    <input type="range" id="width-slider" min="200" max="600" value="350" step="10"
                           style="width: 100%; margin: 5px 0;">
                </div>
                <div style="display: flex; gap: 5px;">
                    <button id="btn-narrow" style="flex: 1; padding: 5px; background: #dc2626; border: none; border-radius: 4px; color: white; cursor: pointer;">Étroit</button>
                    <button id="btn-normal" style="flex: 1; padding: 5px; background: #059669; border: none; border-radius: 4px; color: white; cursor: pointer;">Normal</button>
                    <button id="btn-wide" style="flex: 1; padding: 5px; background: #7c3aed; border: none; border-radius: 4px; color: white; cursor: pointer;">Large</button>
                </div>
                <div style="margin-top: 10px;">
                    <button id="btn-close" style="width: 100%; padding: 5px; background: #6b7280; border: none; border-radius: 4px; color: white; cursor: pointer;">Fermer test</button>
                </div>
            </div>
        `;

        document.body.appendChild(testPanel);

        // Trouver le sidebar
        const sidebar = document.querySelector('.properties-panel') ||
                       document.querySelector('[class*="sidebar"]') ||
                       document.querySelector('#properties-panel');

        if (!sidebar) {
            console.warn('Sidebar non trouvé pour le test adaptatif');
            return;
        }

        const widthDisplay = document.getElementById('sidebar-width');
        const widthSlider = document.getElementById('width-slider');

        // Fonction pour mettre à jour la largeur
        function updateSidebarWidth(width) {
            if (width === 'auto') {
                sidebar.style.width = '';
                sidebar.style.minWidth = '';
                sidebar.style.maxWidth = '';
            } else {
                sidebar.style.width = width + 'px';
                sidebar.style.minWidth = width + 'px';
                sidebar.style.maxWidth = width + 'px';
            }
            widthDisplay.textContent = width;

            // Forcer un redessinement pour déclencher ResizeObserver
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 100);
        }

        // Événements des boutons
        document.getElementById('btn-narrow').addEventListener('click', () => updateSidebarWidth(250));
        document.getElementById('btn-normal').addEventListener('click', () => updateSidebarWidth(350));
        document.getElementById('btn-wide').addEventListener('click', () => updateSidebarWidth(500));
        document.getElementById('btn-close').addEventListener('click', () => {
            document.body.removeChild(testPanel);
            updateSidebarWidth('auto'); // Restaurer la largeur normale
        });

        // Slider pour contrôle fin
        widthSlider.addEventListener('input', (e) => {
            updateSidebarWidth(parseInt(e.target.value));
        });

        // Afficher la largeur initiale
        const initialWidth = sidebar.offsetWidth;
        widthDisplay.textContent = initialWidth;
        widthSlider.value = initialWidth;

        console.log('🧪 Test du layout adaptatif activé. Utilisez le panneau en haut à droite pour redimensionner le sidebar.');
    });
})();