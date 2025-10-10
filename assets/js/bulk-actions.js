/**
 * JavaScript pour les Bulk Actions - PDF Builder Pro
 *
 * Gestion des actions en masse côté client
 */

(function($) {
    'use strict';

    /**
     * Classe Bulk Actions Handler
     */
    class PDFBuilderBulkActions {

        constructor() {
            this.tasks = {};
            this.init();
        }

        /**
         * Initialisation
         */
        init() {
            this.bindEvents();
            this.checkRunningTasks();
        }

        /**
         * Lier les événements
         */
        bindEvents() {
            // Boutons de génération bulk
            $(document).on('click', '.pdf-builder-bulk-generate', this.handleBulkGenerate.bind(this));

            // Boutons de statut des tâches
            $(document).on('click', '.pdf-builder-task-status', this.showTaskStatus.bind(this));

            // Mise à jour périodique du statut
            setInterval(this.updateTaskStatuses.bind(this), 5000);
        }

        /**
         * Gérer la génération en masse
         */
        handleBulkGenerate(e) {
            e.preventDefault();

            const $button = $(e.target);
            const templateId = $button.data('template-id');
            const itemIds = this.getSelectedItemIds();

            if (!templateId || itemIds.length === 0) {
                this.showNotice('Veuillez sélectionner des éléments et un modèle.', 'error');
                return;
            }

            // Désactiver le bouton
            $button.prop('disabled', true).text(pdfBuilderBulk.strings.processing);

            // Créer la tâche
            $.ajax({
                url: pdfBuilderBulk.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_bulk_generate',
                    template_id: templateId,
                    item_ids: itemIds,
                    nonce: pdfBuilderBulk.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotice(`Tâche créée avec succès. ID: ${response.data.task_id}`, 'success');
                        this.trackTask(response.data.task_id);
                    } else {
                        this.showNotice(response.data.message || 'Erreur lors de la création de la tâche.', 'error');
                    }
                },
                error: () => {
                    this.showNotice('Erreur de communication avec le serveur.', 'error');
                },
                complete: () => {
                    $button.prop('disabled', false).text('Générer en masse');
                }
            });
        }

        /**
         * Obtenir les IDs des éléments sélectionnés
         */
        getSelectedItemIds() {
            const ids = [];

            // Pour les commandes WooCommerce
            $('input[name="post[]"]:checked').each(function() {
                ids.push(parseInt($(this).val()));
            });

            // Pour les utilisateurs
            $('input[name="users[]"]:checked').each(function() {
                ids.push(parseInt($(this).val()));
            });

            // Pour les cases à cocher génériques
            $('input.pdf-builder-item-checkbox:checked').each(function() {
                ids.push(parseInt($(this).val()));
            });

            return ids;
        }

        /**
         * Suivre une tâche
         */
        trackTask(taskId) {
            this.tasks[taskId] = {
                id: taskId,
                status: 'pending',
                progress: 0,
                interval: setInterval(() => {
                    this.updateTaskStatus(taskId);
                }, 2000)
            };

            this.showTaskProgress(taskId);
        }

        /**
         * Mettre à jour le statut d'une tâche
         */
        updateTaskStatus(taskId) {
            $.ajax({
                url: pdfBuilderBulk.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_bulk_status',
                    task_id: taskId,
                    nonce: pdfBuilderBulk.nonce
                },
                success: (response) => {
                    if (response.success) {
                        const task = response.data;
                        this.tasks[taskId].status = task.status;
                        this.tasks[taskId].progress = task.progress;

                        this.updateTaskProgress(taskId, task);

                        // Arrêter le suivi si terminé
                        if (task.status === 'completed' ||
                            task.status === 'failed' ||
                            task.status === 'completed_with_errors') {
                            clearInterval(this.tasks[taskId].interval);
                            delete this.tasks[taskId];
                        }
                    }
                },
                error: () => {
                    console.error('Erreur lors de la récupération du statut de la tâche:', taskId);
                }
            });
        }

        /**
         * Mettre à jour toutes les tâches
         */
        updateTaskStatuses() {
            Object.keys(this.tasks).forEach(taskId => {
                this.updateTaskStatus(taskId);
            });
        }

        /**
         * Afficher la progression d'une tâche
         */
        showTaskProgress(taskId, taskData = null) {
            let $progress = $(`#pdf-builder-task-${taskId}`);

            if ($progress.length === 0) {
                $progress = $(`
                    <div id="pdf-builder-task-${taskId}" class="pdf-builder-task-progress notice notice-info">
                        <p>
                            <strong>Tâche #${taskId}</strong>
                            <span class="task-status">En attente...</span>
                        </p>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 0%"></div>
                        </div>
                        <div class="task-details">
                            <span class="processed">0</span> / <span class="total">0</span> éléments traités
                        </div>
                    </div>
                `);

                $('.wp-header-end').after($progress);
            }

            if (taskData) {
                const statusText = this.getStatusText(taskData.status);
                $progress.find('.task-status').text(statusText);
                $progress.find('.progress-fill').css('width', taskData.progress + '%');
                $progress.find('.processed').text(taskData.processed);
                $progress.find('.total').text(taskData.total);

                // Changer la classe selon le statut
                $progress.removeClass('notice-info notice-success notice-error notice-warning');
                if (taskData.status === 'completed') {
                    $progress.addClass('notice-success');
                } else if (taskData.status === 'failed') {
                    $progress.addClass('notice-error');
                } else if (taskData.status === 'completed_with_errors') {
                    $progress.addClass('notice-warning');
                } else {
                    $progress.addClass('notice-info');
                }
            }
        }

        /**
         * Mettre à jour la progression d'une tâche
         */
        updateTaskProgress(taskId, task) {
            const $progress = $(`#pdf-builder-task-${taskId}`);
            if ($progress.length === 0) return;

            const statusText = this.getStatusText(task.status);
            $progress.find('.task-status').text(statusText);
            $progress.find('.progress-fill').css('width', task.progress + '%');
            $progress.find('.processed').text(task.processed);
            $progress.find('.total').text(task.total);

            // Changer la classe selon le statut
            $progress.removeClass('notice-info notice-success notice-error notice-warning');
            if (task.status === 'completed') {
                $progress.addClass('notice-success');
            } else if (task.status === 'failed') {
                $progress.addClass('notice-error');
            } else if (task.status === 'completed_with_errors') {
                $progress.addClass('notice-warning');
            } else {
                $progress.addClass('notice-info');
            }
        }

        /**
         * Obtenir le texte du statut
         */
        getStatusText(status) {
            switch (status) {
                case 'pending': return 'En attente...';
                case 'running': return 'En cours...';
                case 'completed': return 'Terminé';
                case 'failed': return 'Échec';
                case 'completed_with_errors': return 'Terminé avec erreurs';
                default: return 'Inconnu';
            }
        }

        /**
         * Afficher le statut détaillé d'une tâche
         */
        showTaskStatus(e) {
            e.preventDefault();

            const taskId = $(e.target).data('task-id');

            if (!taskId || !this.tasks[taskId]) {
                this.showNotice('Tâche introuvable.', 'error');
                return;
            }

            const task = this.tasks[taskId];

            // Ouvrir une modal ou afficher les détails
            const details = `
                <strong>ID de tâche:</strong> ${task.id}<br>
                <strong>Statut:</strong> ${this.getStatusText(task.status)}<br>
                <strong>Progression:</strong> ${task.progress.toFixed(1)}%<br>
                <strong>Démarré:</strong> ${task.started_at || 'N/A'}<br>
                <strong>Terminé:</strong> ${task.completed_at || 'N/A'}
            `;

            // Créer une modal simple
            const $modal = $(`
                <div class="pdf-builder-modal">
                    <div class="pdf-builder-modal-content">
                        <div class="pdf-builder-modal-header">
                            <h3>Détails de la tâche</h3>
                            <button class="pdf-builder-modal-close">&times;</button>
                        </div>
                        <div class="pdf-builder-modal-body">
                            ${details}
                        </div>
                    </div>
                </div>
            `);

            $('body').append($modal);

            $modal.find('.pdf-builder-modal-close').on('click', function() {
                $modal.remove();
            });

            $modal.on('click', function(e) {
                if (e.target === this) {
                    $modal.remove();
                }
            });
        }

        /**
         * Vérifier les tâches en cours au chargement
         */
        checkRunningTasks() {
            // Récupérer les tâches depuis le localStorage ou une API
            const runningTasks = JSON.parse(localStorage.getItem('pdf_builder_running_tasks') || '[]');

            runningTasks.forEach(taskId => {
                this.trackTask(taskId);
            });
        }

        /**
         * Sauvegarder les tâches en cours
         */
        saveRunningTasks() {
            const taskIds = Object.keys(this.tasks);
            localStorage.setItem('pdf_builder_running_tasks', JSON.stringify(taskIds));
        }

        /**
         * Afficher une notice
         */
        showNotice(message, type = 'info') {
            const $notice = $(`
                <div class="notice notice-${type} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Fermer cette notice.</span>
                    </button>
                </div>
            `);

            $('.wp-header-end').after($notice);

            // Auto-dismiss après 5 secondes
            setTimeout(() => {
                $notice.fadeOut(() => $notice.remove());
            }, 5000);

            // Gestionnaire de fermeture manuelle
            $notice.find('.notice-dismiss').on('click', function() {
                $notice.fadeOut(() => $notice.remove());
            });
        }
    }

    // Styles CSS pour les éléments personnalisés
    const styles = `
        <style>
        .pdf-builder-task-progress {
            margin: 10px 0;
            padding: 15px;
            border-left: 4px solid #007cba;
        }

        .pdf-builder-task-progress .progress-bar {
            background: #f0f0f0;
            height: 20px;
            border-radius: 10px;
            margin: 10px 0;
            overflow: hidden;
        }

        .pdf-builder-task-progress .progress-fill {
            background: #3b82f6;
            height: 100%;
            transition: width 0.3s ease;
        }

        .pdf-builder-task-progress .task-details {
            font-size: 12px;
            color: #666;
        }

        .pdf-builder-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pdf-builder-modal-content {
            background: white;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .pdf-builder-modal-header {
            padding: 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pdf-builder-modal-header h3 {
            margin: 0;
        }

        .pdf-builder-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .pdf-builder-modal-body {
            padding: 20px;
        }

        .pdf-builder-bulk-generate {
            margin-left: 10px;
        }
        </style>
    `;

    // Injecter les styles
    $('head').append(styles);

    // Initialiser quand le DOM est prêt
    $(document).ready(function() {
        window.pdfBuilderBulkActions = new PDFBuilderBulkActions();
    });

})(jQuery);