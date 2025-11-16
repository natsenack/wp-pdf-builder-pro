/**
 * PDF Builder Pro - Tutorial System JavaScript
 */

(function($) {
    'use strict';

    class TutorialManager {
        constructor() {
            this.currentTutorial = null;
            this.currentStep = 0;
            this.tooltips = [];
            this.init();
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            // Événements pour le wizard de bienvenue
            $(document).on('click', '#start-wizard', (e) => {
                e.preventDefault();
                this.startWelcomeWizard();
            });

            $(document).on('click', '#skip-wizard', (e) => {
                e.preventDefault();
                this.skipWelcomeWizard();
            });

            // Événements pour les tooltips de tutoriel
            $(document).on('click', '.tutorial-start', (e) => {
                e.preventDefault();
                const $tooltip = $(e.target).closest('.tutorial-tooltip');
                const tutorialId = $tooltip.data('tutorial');
                this.startTutorial(tutorialId);
            });

            $(document).on('click', '.tutorial-skip', (e) => {
                e.preventDefault();
                const $tooltip = $(e.target).closest('.tutorial-tooltip');
                const tutorialId = $tooltip.data('tutorial');
                this.markTutorialAsSkipped(tutorialId);
                $tooltip.fadeOut();
            });

            $(document).on('click', '.tutorial-close', (e) => {
                e.preventDefault();
                $(e.target).closest('.tutorial-tooltip').fadeOut();
            });

            // Événements de navigation dans les tutoriels
            $(document).on('click', '.tutorial-next', (e) => {
                e.preventDefault();
                this.nextStep();
            });

            $(document).on('click', '.tutorial-prev', (e) => {
                e.preventDefault();
                this.prevStep();
            });
        }

        showWelcomeWizard() {
            // Le wizard est affiché par PHP, on l'active juste
            $('#pdf-builder-welcome-wizard').fadeIn();
        }

        startWelcomeWizard() {
            // Fermer le modal et commencer le tutoriel étape par étape
            this.closeModal();
            this.startTutorial('welcome_wizard');
        }

        skipWelcomeWizard() {
            this.markTutorialAsSkipped('welcome_wizard');
            this.closeModal();
        }

        startTutorial(tutorialId) {
            this.currentTutorial = tutorialId;
            this.currentStep = 0;
            this.showCurrentStep();
        }

        showCurrentStep() {
            const tutorial = window.pdfBuilderTutorial.tutorials[this.currentTutorial];
            if (!tutorial || !tutorial.steps) {
                return;
            }

            const step = tutorial.steps[this.currentStep];
            if (!step) {
                this.completeTutorial();
                return;
            }

            this.showTooltip(step);
            this.updateNavigation();
        }

        showTooltip(step) {
            // Masquer les tooltips existants
            $('.tutorial-tooltip').removeClass('active').hide();

            // Créer ou mettre à jour le tooltip
            let $tooltip = $(`.tutorial-tooltip[data-tutorial="${this.currentTutorial}"][data-step="${this.currentStep}"]`);

            if ($tooltip.length === 0) {
                $tooltip = this.createTooltip(step);
            }

            // Positionner le tooltip
            this.positionTooltip($tooltip, step);

            // Afficher le tooltip
            $tooltip.addClass('active').show();
        }

        createTooltip(step) {
            const $tooltip = $(`
                <div class="tutorial-tooltip" data-tutorial="${this.currentTutorial}" data-step="${this.currentStep}">
                    <div class="tutorial-tooltip-header">
                        <h4>${step.title}</h4>
                        <button class="tutorial-close">&times;</button>
                    </div>
                    <div class="tutorial-tooltip-content">
                        <p>${step.content}</p>
                    </div>
                    <div class="tutorial-tooltip-footer">
                        <button class="tutorial-prev">${window.pdfBuilderTutorial.tutorials[this.currentTutorial].prevText || 'Précédent'}</button>
                        <span class="tutorial-progress">${this.currentStep + 1} / ${window.pdfBuilderTutorial.tutorials[this.currentTutorial].steps.length}</span>
                        <button class="tutorial-next">${window.pdfBuilderTutorial.tutorials[this.currentTutorial].nextText || 'Suivant'}</button>
                    </div>
                </div>
            `);

            $('body').append($tooltip);
            return $tooltip;
        }

        positionTooltip($tooltip, step) {
            const $target = $(step.target);
            if ($target.length === 0) {
                return;
            }

            const targetOffset = $target.offset();
            const targetWidth = $target.outerWidth();
            const targetHeight = $target.outerHeight();
            const tooltipWidth = $tooltip.outerWidth();
            const tooltipHeight = $tooltip.outerHeight();

            let top, left;

            switch (step.position) {
                case 'top':
                    top = targetOffset.top - tooltipHeight - 10;
                    left = targetOffset.left + (targetWidth / 2) - (tooltipWidth / 2);
                    break;
                case 'bottom':
                    top = targetOffset.top + targetHeight + 10;
                    left = targetOffset.left + (targetWidth / 2) - (tooltipWidth / 2);
                    break;
                case 'left':
                    top = targetOffset.top + (targetHeight / 2) - (tooltipHeight / 2);
                    left = targetOffset.left - tooltipWidth - 10;
                    break;
                case 'right':
                default:
                    top = targetOffset.top + (targetHeight / 2) - (tooltipHeight / 2);
                    left = targetOffset.left + targetWidth + 10;
                    break;
            }

            // Ajustements pour rester dans la fenêtre
            const windowWidth = $(window).width();
            const windowHeight = $(window).height();
            const scrollTop = $(window).scrollTop();

            if (left < 10) left = 10;
            if (left + tooltipWidth > windowWidth - 10) left = windowWidth - tooltipWidth - 10;
            if (top < scrollTop + 10) top = scrollTop + 10;
            if (top + tooltipHeight > scrollTop + windowHeight - 10) top = scrollTop + windowHeight - tooltipHeight - 10;

            $tooltip.css({
                top: top,
                left: left
            }).attr('data-position', step.position);
        }

        updateNavigation() {
            const tutorial = window.pdfBuilderTutorial.tutorials[this.currentTutorial];
            const $tooltip = $(`.tutorial-tooltip[data-tutorial="${this.currentTutorial}"][data-step="${this.currentStep}"]`);

            // Mettre à jour la progression
            $tooltip.find('.tutorial-progress').text(`${this.currentStep + 1} / ${tutorial.steps.length}`);

            // Gérer les boutons précédent/suivant
            const $prevBtn = $tooltip.find('.tutorial-prev');
            const $nextBtn = $tooltip.find('.tutorial-next');

            $prevBtn.prop('disabled', this.currentStep === 0);

            if (this.currentStep === tutorial.steps.length - 1) {
                $nextBtn.text('Terminer');
            } else {
                $nextBtn.text('Suivant');
            }
        }

        nextStep() {
            const tutorial = pdfBuilderTutorial.tutorials[this.currentTutorial];
            if (this.currentStep < tutorial.steps.length - 1) {
                this.currentStep++;
                this.showCurrentStep();
            } else {
                this.completeTutorial();
            }
        }

        prevStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
                this.showCurrentStep();
            }
        }

        completeTutorial() {
            this.closeTutorial();
            this.markTutorialAsCompleted(this.currentTutorial);
            this.showCompletionMessage();
        }

        closeTutorial() {
            $('.tutorial-tooltip').removeClass('active').hide();
            this.currentTutorial = null;
            this.currentStep = 0;
        }

        skipTutorial() {
            this.closeTutorial();
            this.markTutorialAsSkipped(this.currentTutorial);
        }

        closeModal() {
            $('.pdf-builder-modal').fadeOut();
        }

        markTutorialAsCompleted(tutorialId) {
            this.saveTutorialProgress(tutorialId, true);
        }

        markTutorialAsSkipped(tutorialId) {
            this.saveTutorialProgress(tutorialId, false, true);
        }

        saveTutorialProgress(tutorialId, completed = false, skipped = false) {
            $.ajax({
                url: window.pdfBuilderTutorial.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_tutorial_progress',
                    tutorial_id: tutorialId,
                    completed: completed,
                    skipped: skipped,
                    nonce: window.pdfBuilderTutorial.nonce
                },
                success: (response) => {
                    if (response.success) {
                        console.log('Tutorial progress saved:', tutorialId);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error saving tutorial progress:', error);
                }
            });
        }

        getCompletedTutorials() {
            // Cette fonction pourrait récupérer les tutoriels complétés depuis localStorage ou une API
            const completed = localStorage.getItem('pdf_builder_completed_tutorials');
            return completed ? JSON.parse(completed) : [];
        }

        showCompletionMessage() {
            // Afficher un message de félicitations
            const $message = $(`
                <div class="tutorial-completion-message">
                    <div class="tutorial-completion-content">
                        <span class="tutorial-completion-icon">🎉</span>
                        <h3>Tutoriel terminé !</h3>
                        <p>Vous maîtrisez maintenant les bases de PDF Builder Pro.</p>
                        <button class="tutorial-completion-close">Fermer</button>
                    </div>
                </div>
            `);

            $('body').append($message);

            setTimeout(() => {
                $message.fadeIn();
            }, 100);

            $message.find('.tutorial-completion-close').on('click', () => {
                $message.fadeOut(() => {
                    $message.remove();
                });
            });
        }
    }

    // Styles pour le message de completion
    const completionStyles = `
        <style>
        .tutorial-completion-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007cba;
            color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            z-index: 10001;
            display: none;
            max-width: 300px;
        }
        .tutorial-completion-content {
            text-align: center;
        }
        .tutorial-completion-icon {
            font-size: 32px;
            display: block;
            margin-bottom: 8px;
        }
        .tutorial-completion-close {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 12px;
        }
        .tutorial-completion-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        </style>
    `;

    $('head').append(completionStyles);

    // Initialiser le système de tutoriels quand le DOM est prêt
    $(document).ready(() => {
        window.pdfBuilderTutorialManager = new TutorialManager();
    });

})(jQuery);