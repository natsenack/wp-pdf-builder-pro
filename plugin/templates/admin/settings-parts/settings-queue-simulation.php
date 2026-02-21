<!-- PDF Builder Pro - Queue Simulation Control Panel -->
<div id="pdf-builder-queue-simulation-panel" class="pdf-builder-settings-section">
    <div class="pdfb-settings-card">
        <div class="pdfb-settings-card-header">
            <h3>
                <span class="test-icon">🔬</span>
                Mode Simulation de Queue
                <span id="pdf-queue-sim-status" class="status-badge">⚫ INACTIF</span>
            </h3>
            <p class="description">
                Testez le fonctionnement de la modal de position de queue sans attendre une vraie queue.
                Idéal pour valider le comportement avant déploiement en production.
            </p>
        </div>
        
        <div class="pdfb-settings-card-body">
            <!-- Section de contrôle principal -->
            <div class="control-section">
                <div class="control-buttons">
                    <button id="pdf-queue-sim-toggle" class="button button-primary" style="margin-right: 10px;">
                        🟢 Activer
                    </button>
                    <small style="color: #999;">
                        Activez le mode simulation pour que chaque génération PDF crée une fausse queue
                    </small>
                </div>
            </div>
            
            <!-- Section de configuration -->
            <div class="pdf-queue-sim-config" style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px; opacity: 0.5; pointer-events: none;">
                <h4>⚙️ Paramètres de Simulation</h4>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="pdf-queue-sim-position">
                        <strong>Position initiale dans la queue :</strong>
                        <span style="color: #999;">(1-50)</span>
                    </label>
                    <input 
                        type="number" 
                        id="pdf-queue-sim-position" 
                        min="1" 
                        max="50" 
                        value="5"
                        style="width: 100px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                    />
                    <small style="display: block; color: #666; margin-top: 5px;">
                        La position diminuera de 1 toutes les 3 secondes. À 1, le PDF sera prêt.
                    </small>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="pdf-queue-sim-wait-time">
                        <strong>Temps d'attente estimé :</strong>
                        <span style="color: #999;">(5-300 secondes)</span>
                    </label>
                    <input 
                        type="number" 
                        id="pdf-queue-sim-wait-time" 
                        min="5" 
                        max="300" 
                        value="30"
                        style="width: 120px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                    />
                    <small style="display: block; color: #666; margin-top: 5px;">
                        Le temps estimé diminuera avec le temps réel. Affichage dans la modal.
                    </small>
                </div>
                
                <button id="pdf-queue-sim-config" class="button button-secondary">
                    💾 Enregistrer les paramètres
                </button>
            </div>
            
            <!-- Informations -->
            <div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-left: 4px solid #2196F3; border-radius: 4px;">
                <h4 style="margin-top: 0;">ℹ️ Comment ca fonctionne ?</h4>
                <ol style="margin: 10px 0; padding-left: 20px;">
                    <li>Activez le mode simulation ci-dessus</li>
                    <li>Allez sur une page WooCommerce pour générer un PDF</li>
                    <li>La modal de queue apparaîtra immédiatement</li>
                    <li>La position diminuera jusqu'à atteindre 1</li>
                    <li>Le PDF sera téléchargé automatiquement quand prêt</li>
                </ol>
                <p style="color: #666; font-size: 13px;">
                    <strong>Note:</strong> Le mode simulation n'affecte que les PDF générés via le formulaire.
                    Pas d'impact sur la génération réelle via le service Puppeteer.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    #pdf-builder-queue-simulation-panel .pdfb-settings-card {
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    #pdf-builder-queue-simulation-panel .pdfb-settings-card-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
    }
    
    #pdf-builder-queue-simulation-panel .pdfb-settings-card-header h3 {
        margin: 0 0 10px 0;
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 18px;
    }
    
    #pdf-builder-queue-simulation-panel .test-icon {
        font-size: 24px;
    }
    
    #pdf-builder-queue-simulation-panel .status-badge {
        margin-left: auto;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    #pdf-builder-queue-simulation-panel .description {
        margin: 0;
        color: #666;
        font-size: 14px;
    }
    
    #pdf-builder-queue-simulation-panel .pdfb-settings-card-body {
        padding: 20px;
    }
    
    #pdf-builder-queue-simulation-panel .control-section {
        padding-bottom: 20px;
    }
    
    #pdf-builder-queue-simulation-panel .form-group {
        display: flex;
        flex-direction: column;
    }
    
    #pdf-builder-queue-simulation-panel .form-group label {
        margin-bottom: 8px;
    }
</style>
