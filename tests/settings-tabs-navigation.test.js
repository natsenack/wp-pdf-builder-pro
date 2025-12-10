/**
 * Tests pour la navigation des onglets PDF Builder
 * Tests unitaires pour le système de tabs
 */

describe('Settings Tabs Navigation', () => {
    let mockLocalStorage;

    beforeEach(() => {
        // Setup DOM pour les tests
        document.body.innerHTML = `
            <div id="pdf-builder-tabs" class="nav-tabs">
                <a href="#" class="nav-tab" data-tab="general" aria-selected="false">Général</a>
                <a href="#" class="nav-tab" data-tab="templates" aria-selected="false">Templates</a>
                <a href="#" class="nav-tab" data-tab="advanced" aria-selected="false">Avancé</a>
            </div>
            <div id="pdf-builder-tab-content">
                <div id="tab-content-general" class="tab-content">Contenu Général</div>
                <div id="tab-content-templates" class="tab-content">Contenu Templates</div>
                <div id="tab-content-advanced" class="tab-content">Contenu Avancé</div>
            </div>
        `;

        // Mock localStorage
        mockLocalStorage = {
            getItem: jest.fn(),
            setItem: jest.fn(),
            removeItem: jest.fn(),
            clear: jest.fn()
        };
        Object.defineProperty(window, 'localStorage', {
            value: mockLocalStorage,
            writable: true
        });

        // Charger le script
        require('../assets/js/settings-tabs.js');

        // Simuler l'événement DOMContentLoaded pour initialiser les tabs
        const event = new Event('DOMContentLoaded');
        document.dispatchEvent(event);
    });

    afterEach(() => {
        document.body.innerHTML = '';
        jest.clearAllMocks();
    });

    test('should initialize tabs system', () => {
        const tabsContainer = document.getElementById('pdf-builder-tabs');
        const contentContainer = document.getElementById('pdf-builder-tab-content');

        expect(tabsContainer).toBeTruthy();
        expect(contentContainer).toBeTruthy();
    });

    test('should switch to clicked tab', () => {
        const generalTab = document.querySelector('[data-tab="general"]');
        const templatesTab = document.querySelector('[data-tab="templates"]');
        const generalContent = document.getElementById('tab-content-general');
        const templatesContent = document.getElementById('tab-content-templates');

        // Simuler un clic sur l'onglet templates
        templatesTab.click();

        // Vérifier que l'onglet templates est actif
        expect(templatesTab.classList.contains('nav-tab-active')).toBe(true);
        expect(templatesTab.getAttribute('aria-selected')).toBe('true');

        // Vérifier que l'onglet general n'est plus actif
        expect(generalTab.classList.contains('nav-tab-active')).toBe(false);
        expect(generalTab.getAttribute('aria-selected')).toBe('false');

        // Vérifier que le contenu templates est actif
        expect(templatesContent.classList.contains('active')).toBe(true);

        // Vérifier que le contenu general n'est plus actif
        expect(generalContent.classList.contains('active')).toBe(false);
    });

    test('should save active tab to localStorage', () => {
        const templatesTab = document.querySelector('[data-tab="templates"]');

        templatesTab.click();

        expect(mockLocalStorage.setItem).toHaveBeenCalledWith('pdf_builder_active_tab', 'templates');
    });

    test('should restore saved tab from localStorage', () => {
        mockLocalStorage.getItem.mockReturnValue('advanced');

        // Simuler l'événement DOMContentLoaded après avoir mocké localStorage
        const event = new Event('DOMContentLoaded');
        document.dispatchEvent(event);

        // Le script devrait avoir appelé getItem
        expect(mockLocalStorage.getItem).toHaveBeenCalledWith('pdf_builder_active_tab');
    });

    test('should handle localStorage errors gracefully', () => {
        // Mock localStorage pour qu'il lance une erreur
        mockLocalStorage.setItem.mockImplementation(() => {
            throw new Error('localStorage not available');
        });

        const templatesTab = document.querySelector('[data-tab="templates"]');

        // Ne devrait pas planter même si localStorage échoue
        expect(() => {
            templatesTab.click();
        }).not.toThrow();
    });

    test('should handle localStorage getItem errors gracefully', () => {
        mockLocalStorage.getItem.mockImplementation(() => {
            throw new Error('localStorage not available');
        });

        // Recharger le script - ne devrait pas planter
        expect(() => {
            require('../assets/js/settings-tabs.js');
        }).not.toThrow();
    });

    test('should not switch tab if data-tab attribute is missing', () => {
        const tabsContainer = document.getElementById('pdf-builder-tabs');

        // Créer un élément sans data-tab
        const invalidTab = document.createElement('a');
        invalidTab.className = 'nav-tab';
        invalidTab.href = '#';
        tabsContainer.appendChild(invalidTab);

        // Compter les onglets actifs avant le clic
        const activeTabsBefore = document.querySelectorAll('.nav-tab-active').length;

        // Simuler un clic
        invalidTab.click();

        // Le nombre d'onglets actifs ne devrait pas changer
        const activeTabsAfter = document.querySelectorAll('.nav-tab-active').length;
        expect(activeTabsAfter).toBe(activeTabsBefore);
    });

    test('should handle non-existent tab content gracefully', () => {
        const tabsContainer = document.getElementById('pdf-builder-tabs');

        // Créer un onglet pointant vers un contenu inexistant
        const invalidTab = document.createElement('a');
        invalidTab.className = 'nav-tab';
        invalidTab.href = '#';
        invalidTab.setAttribute('data-tab', 'nonexistent');
        tabsContainer.appendChild(invalidTab);

        // Simuler un clic - ne devrait pas planter
        expect(() => {
            invalidTab.click();
        }).not.toThrow();

        // L'onglet devrait quand même être marqué comme actif
        expect(invalidTab.classList.contains('nav-tab-active')).toBe(true);
    });

    test('should ignore clicks on non-tab elements', () => {
        const tabsContainer = document.getElementById('pdf-builder-tabs');

        // Créer un élément qui n'est pas un onglet
        const nonTabElement = document.createElement('span');
        nonTabElement.textContent = 'Not a tab';
        tabsContainer.appendChild(nonTabElement);

        // Compter les onglets actifs avant le clic
        const activeTabsBefore = document.querySelectorAll('.nav-tab-active').length;

        // Simuler un clic - ne devrait rien faire
        nonTabElement.click();

        // Le nombre d'onglets actifs ne devrait pas changer
        const activeTabsAfter = document.querySelectorAll('.nav-tab-active').length;
        expect(activeTabsAfter).toBe(activeTabsBefore);
    });
});