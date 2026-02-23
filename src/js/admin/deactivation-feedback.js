// @ts-nocheck
/**
 * PDF Builder Pro - Deactivation Feedback Modal
 */
(function ($) {
  "use strict";

  // CSS du modal
  var css = [
    "#pbp-modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:999999;background:rgba(0,0,0,.65);justify-content:center;align-items:center}",
    "#pbp-modal.open{display:flex}",
    '#pbp-modal-box{background:#fff;border-radius:10px;width:90%;max-width:520px;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,.35);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}',
    "#pbp-modal-header{padding:22px 24px 18px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff}",
    "#pbp-modal-header h2{margin:0 0 4px;font-size:18px;font-weight:700;color:#fff}",
    "#pbp-modal-header p{margin:0;font-size:13px;opacity:.85;color:#fff}",
    "#pbp-modal-body{padding:20px 24px}",
    "#pbp-modal-body > p{margin:0 0 14px;font-size:14px;color:#333}",
    ".pbp-opt{display:flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;margin-bottom:7px;cursor:pointer;transition:all .15s}",
    ".pbp-opt:hover{background:#f8f8ff;border-color:#667eea}",
    ".pbp-opt input[type=radio]{margin:0;width:16px;height:16px;cursor:pointer;accent-color:#667eea}",
    ".pbp-opt label{cursor:pointer;margin:0;font-size:13.5px;color:#333}",
    "#pbp-error{display:none;color:#dc3545;font-size:12px;margin-top:8px;padding:8px 12px;background:#fff5f5;border:1px solid #ffc0c0;border-radius:5px}",
    "#pbp-error.show{display:block}",
    "#pbp-extra{display:none;margin-top:14px;padding-top:14px;border-top:1px solid #eee}",
    "#pbp-extra.show{display:block}",
    "#pbp-extra label{display:block;font-size:12px;color:#555;margin-bottom:4px}",
    "#pbp-extra textarea{width:100%;box-sizing:border-box;border:1px solid #ddd;border-radius:5px;padding:9px 10px;font-family:inherit;font-size:13px;margin-bottom:10px;color:#333}",
    "#pbp-extra textarea:focus{outline:none;border-color:#667eea;box-shadow:0 0 0 2px rgba(102,126,234,.15)}",
    "#pbp-extra textarea{min-height:75px;resize:vertical}",
    "#pbp-modal-footer{display:flex;justify-content:space-between;align-items:center;padding:14px 24px;background:#f7f7f7;border-top:1px solid #eee}",
    "#pbp-btn-cancel{background:#fff;border:1px solid #ccc;color:#555;cursor:pointer;font-size:13px;padding:8px 16px;border-radius:6px;transition:all .15s}",
    "#pbp-btn-cancel:hover{background:#f0f0f0;border-color:#999;color:#333}",
    ".pbp-footer-right{display:flex;align-items:center;gap:14px}",
    "#pbp-btn-skip{background:none;border:none;color:#999;cursor:pointer;font-size:13px;padding:0;text-decoration:underline;opacity:.8}",
    "#pbp-btn-skip:hover{opacity:1;color:#666}",
    "#pbp-btn-send{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border:none;border-radius:6px;padding:10px 24px;cursor:pointer;font-size:14px;font-weight:600;transition:opacity .15s}",
    "#pbp-btn-send:hover{opacity:.88}",
    "#pbp-btn-send:disabled{opacity:.6;cursor:not-allowed}",
  ].join("");

  $("<style>").text(css).appendTo("head");

  // HTML du modal
  var html = [
    '<div id="pbp-modal">',
    '  <div id="pbp-modal-box">',
    '    <div id="pbp-modal-header"><h2>Nous serions tristes de vous voir partir 😢</h2><p>Aidez-nous à améliorer PDF Builder Pro</p></div>',
    '    <div id="pbp-modal-body">',
    "      <p>Pourquoi désactivez-vous PDF Builder Pro ?</p>",
    '      <div class="pbp-opt"><input type="radio" name="pbp_reason" id="pbp_r1" value="dont_need"><label for="pbp_r1">Je n\'en ai plus besoin</label></div>',
    '      <div class="pbp-opt"><input type="radio" name="pbp_reason" id="pbp_r2" value="not_working"><label for="pbp_r2">Le plugin ne fonctionne pas</label></div>',
    '      <div class="pbp-opt"><input type="radio" name="pbp_reason" id="pbp_r3" value="slow"><label for="pbp_r3">Ralentit mon site</label></div>',
    '      <div class="pbp-opt"><input type="radio" name="pbp_reason" id="pbp_r4" value="confusing"><label for="pbp_r4">Difficile à utiliser</label></div>',
    '      <div class="pbp-opt"><input type="radio" name="pbp_reason" id="pbp_r5" value="expensive"><label for="pbp_r5">Trop cher</label></div>',
    '      <div class="pbp-opt"><input type="radio" name="pbp_reason" id="pbp_r6" value="alternative"><label for="pbp_r6">Meilleure alternative trouvée</label></div>',
    '      <div class="pbp-opt"><input type="radio" name="pbp_reason" id="pbp_r7" value="temporary"><label for="pbp_r7">Désactivation temporaire</label></div>',
    '      <div class="pbp-opt"><input type="radio" name="pbp_reason" id="pbp_r8" value="autre"><label for="pbp_r8">Autre</label></div>',
    '      <div id="pbp-error">⚠️ Veuillez sélectionner une raison avant d\'envoyer.</div>',
    '      <div id="pbp-extra">',
    '        <label for="pbp-message">Commentaire (optionnel) :</label>',
    '        <textarea id="pbp-message" placeholder="Détails supplémentaires..."></textarea>',
    "      </div>",
    "    </div>",
    '    <div id="pbp-modal-footer">',
    '      <button type="button" id="pbp-btn-cancel">Annuler</button>',
    '      <div class="pbp-footer-right">',
    '        <button type="button" id="pbp-btn-skip">Passer et désactiver</button>',
    '        <button type="button" id="pbp-btn-send">Envoyer et désactiver</button>',
    "      </div>",
    "    </div>",
    "  </div>",
    "</div>",
  ].join("");

  $("body").append(html);

  var deactivateUrl = null;
  var $modal = $("#pbp-modal");

  // Au chargement : remplacer le href du lien de désactivation par #
  // pour empêcher WordPress d'attacher ses propres handlers
  function patchDeactivationLink() {
    $('a[href*="action=deactivate"][href*="pdf-builder"]').each(function () {
      var $a = $(this);
      var realUrl = $a.attr("href");
      if (realUrl && realUrl !== "#") {
        deactivateUrl = realUrl;
        $a.attr("href", "#").attr("data-pbp-url", realUrl);
        console.log("[PDF Builder] Lien patché, URL sauvegardée:", realUrl);
      }
    });
  }

  // Exécuter après le DOM mais avant que WP attache ses handlers (window.load)
  patchDeactivationLink();

  // Clic sur le lien patché
  $(document).on("click", "a[data-pbp-url]", function (e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    deactivateUrl = $(this).attr("data-pbp-url");
    console.log("[PDF Builder] Lien cliqué, URL:", deactivateUrl);
    $modal.addClass("open");
  });

  // Afficher les champs extra quand une raison est sélectionnée
  $(document).on("change", 'input[name="pbp_reason"]', function () {
    $("#pbp-extra").addClass("show");
  });

  // Bouton Annuler — fermer le modal sans rien faire
  $("#pbp-btn-cancel").on("click", function () {
    $modal.removeClass("open");
    $('input[name="pbp_reason"]').prop("checked", false);
    $("#pbp-error").removeClass("show");
    $("#pbp-extra").removeClass("show");
  });

  // Bouton Skip — désactiver sans envoyer
  $("#pbp-btn-skip").on("click", function () {
    console.log("[PDF Builder] Skip, redirection vers:", deactivateUrl);
    if (deactivateUrl) window.location.href = deactivateUrl;
  });

  // Bouton Envoyer
  $("#pbp-btn-send").on("click", function () {
    console.log("[PDF Builder] Send clicked");
    var reason = $('input[name="pbp_reason"]:checked').val();

    // Validation : raison obligatoire
    if (!reason) {
      $("#pbp-error").addClass("show");
      return;
    }
    $("#pbp-error").removeClass("show");

    var $btn = $(this).prop("disabled", true).text("Envoi en cours...");
    var msg = $("#pbp-message").val();

    $.ajax({
      url: pdfBuilderDeactivation.ajaxUrl,
      type: "POST",
      data: {
        action: "pdf_builder_send_deactivation_feedback",
        nonce: pdfBuilderDeactivation.nonce,
        reason: reason,
        message: msg,
      },
      complete: function () {
        console.log("[PDF Builder] AJAX terminé, redirection");
        if (deactivateUrl) window.location.href = deactivateUrl;
      },
    });
  });

  console.log("[PDF Builder] Modal de désactivation prêt");
})(jQuery);
