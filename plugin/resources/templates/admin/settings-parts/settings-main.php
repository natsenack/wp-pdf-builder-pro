<?php
if (!defined('ABSPATH')) exit('Direct access forbidden');
if (!is_user_logged_in() || !current_user_can('manage_options')) wp_die('Access denied');
$settings = get_option('pdf_builder_settings', array());

// DEBUG: Force visible output to verify file is loaded
echo "<div style='position:fixed;top:10px;left:10px;background:red;color:white;padding:10px;z-index:999999;border:3px solid yellow;font-size:16px;font-weight:bold;'>DEBUG: settings-main.php LOADED!</div>";
?>
<!-- Settings page loaded -->
<main class="wrap" id="pdf-builder-settings-wrapper">
    <header class="pdf-builder-header">
        <h1>Paramètres PDF Builder Pro</h1>
    </header>



    <nav class="nav-tab-wrapper wp-clearfix" id="pdf-builder-tabs" role="tablist" aria-label="Onglets des paramètres PDF Builder">
        <a id="tab-general" href="#general" class="nav-tab nav-tab-active" data-tab="general" role="tab" aria-selected="true" aria-controls="general">Général</a>
        <a id="tab-licence" href="#licence" class="nav-tab" data-tab="licence" role="tab" aria-selected="false" aria-controls="licence">Licence</a>
        <a id="tab-systeme" href="#systeme" class="nav-tab" data-tab="systeme" role="tab" aria-selected="false" aria-controls="systeme">Système</a>
        <a id="tab-securite" href="#securite" class="nav-tab" data-tab="securite" role="tab" aria-selected="false" aria-controls="securite">Sécurité</a>
        <a id="tab-pdf" href="#pdf" class="nav-tab" data-tab="pdf" role="tab" aria-selected="false" aria-controls="pdf">PDF</a>
        <a id="tab-contenu" href="#contenu" class="nav-tab" data-tab="contenu" role="tab" aria-selected="false" aria-controls="contenu">Contenu</a>
        <a id="tab-templates" href="#templates" class="nav-tab" data-tab="templates" role="tab" aria-selected="false" aria-controls="templates">Modèles</a>
        <a id="tab-developpeur" href="#developpeur" class="nav-tab" data-tab="developpeur" role="tab" aria-selected="false" aria-controls="developpeur">Développeur</a>
    </nav>

    <section id="pdf-builder-tab-content" class="tab-content-wrapper" role="tabpanel" aria-live="polite">
        <div id="general" class="tab-content active" role="tabpanel" aria-labelledby="tab-general">
            <?php require_once 'settings-general.php'; ?>
        </div>

        <div id="licence" class="tab-content" role="tabpanel" aria-labelledby="tab-licence">
            <?php require_once 'settings-licence.php'; ?>
        </div>

        <div id="systeme" class="tab-content" role="tabpanel" aria-labelledby="tab-systeme">
            <?php require_once 'settings-systeme.php'; ?>
        </div>

        <div id="securite" class="tab-content" role="tabpanel" aria-labelledby="tab-securite">
            <?php require_once 'settings-securite.php'; ?>
        </div>

        <div id="pdf" class="tab-content" role="tabpanel" aria-labelledby="tab-pdf">
            <?php require_once 'settings-pdf.php'; ?>
        </div>

        <div id="contenu" class="tab-content" role="tabpanel" aria-labelledby="tab-contenu">
            <?php require_once 'settings-contenu.php'; ?>
        </div>

        <div id="templates" class="tab-content" role="tabpanel" aria-labelledby="tab-templates">
            <?php require_once 'settings-templates.php'; ?>
        </div>

        <div id="developpeur" class="tab-content" role="tabpanel" aria-labelledby="tab-developpeur">
            <?php require_once 'settings-developpeur.php'; ?>
        </div>
    </section>

    <!-- Modales de configuration - Chargées après tous les onglets pour éviter les conflits de structure -->
    <?php require_once 'settings-modals.php'; ?>

    <!-- Navigation JavaScript - Gérée par assets/js/settings-tabs.js -->
    <!-- Le fichier settings-tabs.js fournit PDFBuilderTabsAPI avec switchToTab(), getActiveTab() -->

    <!-- Styles inline de secours (au cas où le CSS ne chargerait pas) -->
    <style>
    /* Styles pour la navigation par onglets */
    #pdf-builder-tab-content .tab-content {
        display: none;
        padding: 20px 0;
    }
    #pdf-builder-tab-content .tab-content.active {
        display: block;
    }
    #pdf-builder-tabs .nav-tab {
        cursor: pointer;
    }
    </style>
</main>

<!-- Bouton de sauvegarde flottant global - Updated: 2025-12-03 15:20:00 -->
<div id="pdf-builder-save-floating" class="pdf-builder-save-floating">
    <button type="button" id="pdf-builder-save-floating-btn" class="button button-primary button-hero pdf-builder-save-btn">
        <span class="dashicons dashicons-saved"></span>
        💾 Enregistrer
    </button>
</div>

<!-- DEBUG: JavaScript pour vérifier le bouton flottant -->
<script>
console.log('🚨 DEBUG: JavaScript de débogage CHARGÉ');
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚨 DEBUG: DOMContentLoaded déclenché');
    setTimeout(function() {
        console.log('🚨 DEBUG: Timeout 1s écoulé');
        const floatingBtn = document.getElementById('pdf-builder-save-floating-btn');
        const floatingContainer = document.getElementById('pdf-builder-save-floating');
        
        console.log('🚨 DEBUG: Bouton flottant - Container:', floatingContainer);
        console.log('🚨 DEBUG: Bouton flottant - Button:', floatingBtn);
        
        if (floatingContainer) {
            console.log('🚨 DEBUG: Container trouvé, styles:', getComputedStyle(floatingContainer));
            console.log('🚨 DEBUG: Container rect:', floatingContainer.getBoundingClientRect());
        } else {
            console.error('🚨 DEBUG: Container NON trouvé!');
        }
        
        if (floatingBtn) {
            console.log('🚨 DEBUG: Button trouvé, styles:', getComputedStyle(floatingBtn));
            console.log('🚨 DEBUG: Button rect:', floatingBtn.getBoundingClientRect());
            
            // Force visible pour test
            floatingBtn.style.background = 'red';
            floatingBtn.style.color = 'white';
            floatingBtn.style.border = '3px solid yellow';
            floatingBtn.style.fontSize = '20px';
            floatingBtn.style.padding = '15px';
            floatingBtn.style.zIndex = '999999';
            floatingBtn.style.position = 'fixed';
            floatingBtn.style.bottom = '50px';
            floatingBtn.style.right = '50px';
            floatingBtn.style.borderRadius = '10px';
            
            console.log('🚨 DEBUG: Styles forcés appliqués');
            
            // Ajouter un événement click visible
            floatingBtn.addEventListener('click', function() {
                alert('🚨 BOUTON CLIQUÉ! Le bouton fonctionne!');
            });
        } else {
            console.error('🚨 DEBUG: Bouton flottant NON trouvé dans le DOM!');
            
            // Chercher tous les boutons qui contiennent "enregistrer"
            const allButtons = document.querySelectorAll('button');
            console.log('🚨 DEBUG: Tous les boutons sur la page:', allButtons);
            allButtons.forEach((btn, i) => {
                if (btn.textContent.toLowerCase().includes('enregistrer')) {
                    console.log('🚨 DEBUG: Bouton "enregistrer" trouvé:', btn);
                }
            });
        }
    }, 1000);
});
</script>

<!-- Styles pour le bouton flottant -->
<style>
.pdf-builder-save-floating {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 999999 !important; /* Très haute priorité */
    display: flex !important;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
    background: rgba(255, 0, 0, 0.8) !important; /* Fond rouge visible pour debug */
    padding: 10px !important;
    border-radius: 10px !important;
    border: 3px solid yellow !important;
}

.pdf-builder-save-btn {
    background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%) !important; /* Rouge visible pour debug */
    color: white !important;
    border: 3px solid yellow !important;
    font-size: 16px !important;
    font-weight: bold !important;
    border-radius: 25px !important;
    box-shadow: 0 4px 12px rgba(255,0,0,0.8) !important;
    transition: all 0.3s ease !important;
    min-width: 180px;
    max-width: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    white-space: nowrap;
    padding: 12px 20px !important;
}

.pdf-builder-save-btn .dashicons {
    font-size: 16px !important;
    width: 16px !important;
    height: 16px !important;
    flex-shrink: 0;
    margin-right: 4px;
    vertical-align: middle;
}

.pdf-builder-save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.4) !important;
}

.pdf-builder-save-btn:active {
    transform: translateY(0);
}

.pdf-builder-save-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

.pdf-builder-save-btn.saving {
    opacity: 0.7;
    pointer-events: none;
}

.pdf-builder-save-btn.saving::after {
    content: '';
    width: 16px;
    height: 16px;
    border: 2px solid #fff;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-left: 8px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 782px) {
    .pdf-builder-save-floating {
        bottom: 10px;
        right: 10px;
    }

    .pdf-builder-save-btn {
        font-size: 13px !important;
        min-width: 160px;
    }
}
</style>

<!-- Script de secours inline (APRÈS le main pour que les éléments existent) -->
<script>
(function() {
    // Vérifier si settings-tabs.js est chargé
    setTimeout(function() {
        if (!window.PDFBuilderTabsAPI) {
            initMinimalTabs();
        }
    }, 1000);

    function initMinimalTabs() {
        const tabsContainer = document.getElementById('pdf-builder-tabs');
        const contentContainer = document.getElementById('pdf-builder-tab-content');

        if (!tabsContainer || !contentContainer) {
            return;
        }

        // Gestionnaire de clic pour les onglets
        tabsContainer.addEventListener('click', function(e) {
            const tab = e.target.closest('.nav-tab');
            if (!tab) return;

            e.preventDefault();
            const tabId = tab.getAttribute('data-tab');
            if (!tabId) return;

            // Désactiver tous les onglets
            tabsContainer.querySelectorAll('.nav-tab').forEach(t => {
                t.classList.remove('nav-tab-active');
                t.setAttribute('aria-selected', 'false');
            });

            // Désactiver tous les contenus
            contentContainer.querySelectorAll('.tab-content').forEach(c => {
                c.classList.remove('active');
            });

            // Activer l'onglet cliqué
            tab.classList.add('nav-tab-active');
            tab.setAttribute('aria-selected', 'true');

            // Activer le contenu correspondant
            const content = document.getElementById(tabId);
            if (content) {
                content.classList.add('active');
            }

            // Sauvegarder dans localStorage
            try {
                localStorage.setItem('pdf_builder_active_tab', tabId);
            } catch (e) {
                // Ignore
            }
        });

        // Restaurer l'onglet sauvegardé
        try {
            const savedTab = localStorage.getItem('pdf_builder_active_tab');
            if (savedTab) {
                const savedTabElement = tabsContainer.querySelector('[data-tab="' + savedTab + '"]');
                if (savedTabElement) {
                    savedTabElement.click();
                    return;
                }
            }
        } catch (e) {
            // Ignore
        }

        // Activer le premier onglet par défaut
        const firstTab = tabsContainer.querySelector('.nav-tab');
        if (firstTab) {
            firstTab.click();
        }
    }
})();
</script>
