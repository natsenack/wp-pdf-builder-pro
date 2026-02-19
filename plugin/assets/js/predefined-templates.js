/**
 * PDF Builder Pro - Predefined Templates Manager JavaScript
 */

(function ($) {
  "use strict";

  let codeMirrorEditor = null;
  let currentEditingSlug = null;

  $(document).ready(function () {
    initializeInterface();
    setupEventListeners();
  });

  /**
   * Initialiser l'interface
   */
  function initializeInterface() {
    // Initialiser CodeMirror pour l'éditeur JSON
    initializeCodeMirror();

    // Masquer la section éditeur au départ
    $(".template-editor-section").hide();
  }

  /**
   * Initialiser CodeMirror
   */
  function initializeCodeMirror() {
    const textArea = document.getElementById("template-json");
    if (textArea && typeof CodeMirror !== "undefined") {
      codeMirrorEditor = CodeMirror.fromTextArea(textArea, {
        mode: "application/json",
        lineNumbers: true,
        theme: "default",
        indentUnit: 2,
        smartIndent: true,
        lineWrapping: true,
        autoCloseBrackets: true,
        matchBrackets: true,
        gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
      });

      // Ajuster la taille
      codeMirrorEditor.setSize("100%", "400px");
    }
  }

  /**
   * Initialiser les event listeners
   */
  function setupEventListeners() {
    // Bouton Nouveau Modèle
    $("#new-template-btn").on("click", function (e) {
      e.preventDefault();
      resetForm();
      currentEditingSlug = null;
      $("#editor-title").text("Créer un nouveau modèle");
      $("#template-slug").prop("disabled", false);
      $(".template-editor-section").slideDown();
    });

    // Bouton Actualiser
    $("#refresh-templates-btn").on("click", function (e) {
      e.preventDefault();
      location.reload();
    });

    // Éditer un modèle
    $(document).on("click", ".edit-template", function (e) {
      e.preventDefault();
      const slug = $(this).data("slug");
      loadTemplate(slug);
    });

    // Supprimer un modèle
    $(document).on("click", ".delete-template", function (e) {
      e.preventDefault();
      const slug = $(this).data("slug");
      if (confirm("Êtes-vous sûr de vouloir supprimer ce modèle ?")) {
        deleteTemplate(slug);
      }
    });

    // Soumettre le formulaire
    $("#template-form").on("submit", function (e) {
      e.preventDefault();
      saveTemplate();
    });

    // Bouton Annuler
    $("#cancel-edit-btn").on("click", function (e) {
      e.preventDefault();
      resetForm();
      currentEditingSlug = null;
      $("#template-slug").prop("disabled", false);
      $(".template-editor-section").slideUp();
    });

    // Valider JSON
    $("#validate-json-btn").on("click", function (e) {
      e.preventDefault();
      validateJson();
    });

    // Close modal on Escape key
    $(document).on("keydown", function (e) {
      if (e.key === "Escape" && $("#template-preview-modal").is(":visible")) {
        closePreviewModal();
      }
    });

    // Modal close button
    $(document).on("click", ".modal-close-btn, .modal-overlay", function (e) {
      if (
        $(this).hasClass("modal-overlay") ||
        $(this).hasClass("modal-close-btn")
      ) {
        closePreviewModal();
      }
    });

    // Modal zoom controls
    $("#zoom-in").on("click", function () {
      adjustZoom(1.1);
    });
    $("#zoom-out").on("click", function () {
      adjustZoom(0.9);
    });
    $("#zoom-fit").on("click", function () {
      resetZoom();
    });

    // Modal rotate controls
    $("#rotate-left").on("click", function () {
      rotatePreview(-90);
    });
    $("#rotate-right").on("click", function () {
      rotatePreview(90);
    });

    // Modal download controls
    $(document).on("click", ".download-preview", function (e) {
      e.preventDefault();
      const format = $(this).data("format");
      downloadPreview(format);
    });
  }

  /**
   * Charger un modèle pour édition
   */
  function loadTemplate(slug) {
    $.ajax({
      url: pdfBuilderPredefined?.ajaxUrl || ajaxurl,
      type: "POST",
      data: {
        action: "pdf_builder_load_predefined_template",
        slug: slug,
        nonce: pdfBuilderPredefined?.nonce || "",
      },
      success: function (response) {
        if (response.success && response.data) {
          const template = response.data;
          $("#template-slug").val(template.slug).prop("disabled", true);
          $("#template-name").val(template.name);
          $("#template-category").val(template.category);
          $("#template-description").val(template.description);
          $("#template-icon").val(template.icon);

          if (codeMirrorEditor) {
            codeMirrorEditor.setValue(template.json);
          } else {
            $("#template-json").val(template.json);
          }

          currentEditingSlug = slug;
          $("#editor-title").text("Éditer: " + template.name);
          $(".template-editor-section").slideDown();
        } else {
          alert(
            "❌ Erreur: " + (response.data?.message || "Modèle non trouvé"),
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("Erreur AJAX:", error);
        alert("❌ Erreur AJAX lors du chargement du modèle");
      },
    });
  }

  /**
   * Sauvegarder le modèle
   */
  function saveTemplate() {
    const slug = $("#template-slug").val();
    const name = $("#template-name").val();
    const category = $("#template-category").val();
    const description = $("#template-description").val();
    const icon = $("#template-icon").val();
    const json = codeMirrorEditor
      ? codeMirrorEditor.getValue()
      : $("#template-json").val();

    if (!slug || !name || !category || !json) {
      alert("❌ Tous les champs obligatoires doivent être remplis");
      return;
    }

    try {
      JSON.parse(json);
    } catch (err) {
      alert("❌ JSON invalide: " + err.message);
      return;
    }

    $.ajax({
      url: pdfBuilderPredefined?.ajaxUrl || ajaxurl,
      type: "POST",
      data: {
        action: "pdf_builder_save_predefined_template",
        slug: slug,
        name: name,
        category: category,
        description: description,
        icon: icon,
        json: json,
        old_slug: currentEditingSlug,
        nonce: pdfBuilderPredefined?.nonce || "",
      },
      success: function (response) {
        if (response.success) {
          alert("✅ Modèle sauvegardé avec succès");
          setTimeout(function () {
            location.reload();
          }, 1000);
        } else {
          alert("❌ Erreur: " + (response.data?.message || "Erreur inconnue"));
        }
      },
      error: function (xhr, status, error) {
        console.error("Erreur AJAX:", error);
        alert("❌ Erreur AJAX lors de la sauvegarde");
      },
    });
  }

  /**
   * Supprimer un modèle
   */
  function deleteTemplate(slug) {
    $.ajax({
      url: pdfBuilderPredefined?.ajaxUrl || ajaxurl,
      type: "POST",
      data: {
        action: "pdf_builder_delete_predefined_template",
        slug: slug,
        nonce: pdfBuilderPredefined?.nonce || "",
      },
      success: function (response) {
        if (response.success) {
          alert("✅ Modèle supprimé avec succès");
          location.reload();
        } else {
          alert("❌ Erreur: " + (response.data?.message || "Erreur inconnue"));
        }
      },
      error: function (xhr, status, error) {
        console.error("Erreur AJAX:", error);
        alert("❌ Erreur AJAX lors de la suppression");
      },
    });
  }

  /**
   * Valider le JSON
   */
  function validateJson() {
    const json = codeMirrorEditor
      ? codeMirrorEditor.getValue()
      : $("#template-json").val();
    try {
      JSON.parse(json);
      alert("✅ JSON valide");
    } catch (err) {
      alert("❌ JSON invalide: " + err.message);
    }
  }

  /**
   * Réinitialiser le formulaire
   */
  function resetForm() {
    $("#template-form")[0].reset();
    $("#template-slug").prop("disabled", false);
    if (codeMirrorEditor) {
      codeMirrorEditor.setValue("{}");
    } else {
      $("#template-json").val("{}");
    }
  }

  /**
   * Fermer le modal de prévisualisation
   */
  function closePreviewModal() {
    $("#template-preview-modal").fadeOut(function () {
      $(this).remove();
    });
  }

  /**
   * Ajuster le zoom
   */
  function adjustZoom(factor) {
    const previewImg = $("#template-preview-img");
    if (previewImg.length) {
      const currentWidth = previewImg.width() || 400;
      previewImg.css({
        width: currentWidth * factor + "px",
        height: "auto",
      });
    }
  }

  /**
   * Réinitialiser le zoom
   */
  function resetZoom() {
    const previewImg = $("#template-preview-img");
    if (previewImg.length) {
      previewImg.css({
        width: "100%",
        height: "auto",
        transform: "none",
      });
    }
  }

  /**
   * Pivoter la prévisualisation
   */
  function rotatePreview(degrees) {
    const previewImg = $("#template-preview-img");
    if (previewImg.length) {
      const currentRotation = parseInt(
        previewImg.css("transform").match(/rotate\(([^)]+)deg/)?.[1] || 0,
      );
      previewImg.css(
        "transform",
        "rotate(" + (currentRotation + degrees) + "deg)",
      );
    }
  }

  /**
   * Télécharger la prévisualisation
   */
  function downloadPreview(format) {
    alert(
      "Téléchargement en " + format.toUpperCase() + " (fonction à implémenter)",
    );
  }
})(jQuery);
