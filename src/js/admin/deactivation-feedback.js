/**
 * PDF Builder Pro - Deactivation Feedback Modal
 */
(function($) {
    'use strict';

    // CSS du modal
    var css = [
        '#pbp-modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:999999;background:rgba(0,0,0,.6);justify-content:center;align-items:center}',
        '#pbp-modal.open{display:flex}',
        '#pbp-modal-box{background:#fff;border-radius:8px;width:90%;max-width:480px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.3)}',
        '#pbp-modal-box h2{margin:0;padding:20px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-size:17px}',
        '#pbp-modal-body{padding:20px}',
        '#pbp-modal-body p{margin:0 0 12px}',
        '.pbp-opt{display:flex;align-items:center;gap:10px;padding:9px 12px;border:1px solid #ddd;border-radius:5px;margin-bottom:6px;cursor:pointer}',
        '.pbp-opt:hover{background:#f5f5f5;border-color:#667eea}',
        '.pbp-opt input{margin:0;cursor:pointer}',
        '.pbp-opt label{cursor:pointer;margin:0}',
        '#pbp-extra{display:none;margin-top:12px}',
        '#pbp-extra.show{display:block}',
        '#pbp-extra input,#pbp-extra textarea{width:100%;box-sizing:border-box;border:1px solid #ddd;border-radius:4px;padding:8px;font-family:inherit;margin-top:6px}',
        '#pbp-extra textarea{min-height:70px;resize:vertical}',
        '#pbp-modal-footer{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#f7f7f7;border-top:1px solid #eee}',
        '#pbp-btn-skip{background:none;border:none;color:#888;cursor:pointer;text-decoration:underline;font-size:13px;padding:0}',
        '#pbp-btn-send{background:#dc3545;color:#fff;border:none;border-radius:5px;padding:10px 22px;cursor:pointer;font-size:14px;font-weight:600}',
        '#pbp-btn-send:hover{background:#c82333}',
        '#pbp-btn-send:disabled{opacity:.6;cursor:not-allowed}'
    ].join('');

    $('<style>').text(css).appendTo('head');

    // HTML du modal
    var html = [
        '<div id="pbp-modal">',
        '  <div id="pbp-modal-box">',
        '    <h2>Nous serions tristes de vous voir partir 😢</h2>',
        '    <div id="pbp-modal-body">',
        '      <p>Pourquoi désactivez-vous PDF Builder Pro ?</p>',
        '      <div class="pbp-opt"><input type="radio" name="pbp_reason" id="pbp_r1" value="dont_need"><label for="pbp_r1">Je n\'en ai plus besoin</label></div>',
        '      <div class="pbp-opt"><input type="radio" name="pbp_reason" id="pbp_r2" value="not_working"><label for="pbp_r2">Le plugin ne fonctionne pas</label></div>',
        '      <div class="pbp-opt"><input type="radio" name="pbp_reason" id="pbp_r3" value="slow"><label for="pbp_r3">Ralentit mon site</label></div>',
        '      <div class="pbp-opt"><input type="radio" name="pbp_reason" id="pbp_r4" value="confusing"><label for="pbp_r4">Difficile à utiliser</label></div>',
        '      <div class="pbp-opt"><input type="radio" name="pbp_reason" id="pbp_r5" value="expensive"><label for="pbp_r5">Trop cher</label></div>',
        '      <div class="pbp-opt"><input type="radio" name="pbp_reason" id="pbp_r6" value="alternative"><label for="pbp_r6">Meilleure alternative trouvée</label></div>',
        '      <div class="pbp-opt"><input type="radio" name="pbp_reason" id="pbp_r7" value="temporary"><label for="pbp_r7">Désactivation temporaire</label></div>',
        '      <div class="pbp-opt"><input type="radio" name="pbp_reason" id="pbp_r8" value="autre"><label for="pbp_r8">Autre</label></div>',
        '      <div id="pbp-extra">',
        '        <input type="email" id="pbp-email" placeholder="Votre email (optionnel)">',
        '        <textarea id="pbp-message" placeholder="Détails supplémentaires (optionnel)"></textarea>',
        '      </div>',
        '    </div>',
        '    <div id="pbp-modal-footer">',
        '      <button type="button" id="pbp-btn-skip">Passer et désactiver</button>',
        '      <button type="button" id="pbp-btn-send">Envoyer et désactiver</button>',
        '    </div>',
        '  </div>',
        '</div>'
    ].join('');

    $('body').append(html);

    var deactivateUrl = null;
    var $modal = $('#pbp-modal');

    // Au chargement : remplacer le href du lien de désactivation par # 
    // pour empêcher WordPress d'attacher ses propres handlers
    function patchDeactivationLink() {
        $('a[href*="action=deactivate"][href*="pdf-builder"]').each(function() {
            var $a = $(this);
            var realUrl = $a.attr('href');
            if (realUrl && realUrl !== '#') {
                deactivateUrl = realUrl;
                $a.attr('href', '#').attr('data-pbp-url', realUrl);
                console.log('[PDF Builder] Lien patché, URL sauvegardée:', realUrl);
            }
        });
    }

    // Exécuter après le DOM mais avant que WP attache ses handlers (window.load)
    patchDeactivationLink();

    // Clic sur le lien patché
    $(document).on('click', 'a[data-pbp-url]', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        deactivateUrl = $(this).attr('data-pbp-url');
        console.log('[PDF Builder] Lien cliqué, URL:', deactivateUrl);
        $modal.addClass('open');
    });

    // Afficher les champs extra quand une raison est sélectionnée
    $(document).on('change', 'input[name="pbp_reason"]', function() {
        $('#pbp-extra').addClass('show');
    });

    // Bouton Skip — désactiver sans envoyer
    $('#pbp-btn-skip').on('click', function() {
        console.log('[PDF Builder] Skip, redirection vers:', deactivateUrl);
        if (deactivateUrl) window.location.href = deactivateUrl;
    });

    // Bouton Envoyer
    $('#pbp-btn-send').on('click', function() {
        console.log('[PDF Builder] Send clicked');
        var $btn = $(this).prop('disabled', true).text('Envoi...');
        var reason = $('input[name="pbp_reason"]:checked').val() || 'autre';
        var email  = $('#pbp-email').val();
        var msg    = $('#pbp-message').val();

        $.ajax({
            url: pdfBuilderDeactivation.ajaxUrl,
            type: 'POST',
            data: {
                action: 'pdf_builder_send_deactivation_feedback',
                nonce:  pdfBuilderDeactivation.nonce,
                reason: reason,
                email:  email,
                message: msg
            },
            complete: function() {
                console.log('[PDF Builder] AJAX terminé, redirection');
                if (deactivateUrl) window.location.href = deactivateUrl;
            }
        });
    });

    console.log('[PDF Builder] Modal de désactivation prêt');

})(jQuery);
