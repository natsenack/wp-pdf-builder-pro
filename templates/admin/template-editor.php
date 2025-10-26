<?php
/**
 * Template for PDF Builder Pro Editor
 * WordPress Admin Page Template
 */

// Prevent direct access
if (!defined("ABSPATH")) {
    exit;
}

// Get template data
$template_id = isset($_GET["template_id"]) ? intval($_GET["template_id"]) : 0;
$template_data = array();

// Load template data if editing existing template
if ($template_id > 0) {
    global $wpdb;
    $table_name = $wpdb->prefix . "pdf_builder_templates";
    $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $template_id));

    if ($template) {
        $template_data = json_decode($template->template_data, true);
        $template_name = $template->name;
    }
}

// Default template data if none exists
if (empty($template_data)) {
    $template_data = array(
        "elements" => array(),
        "settings" => array(
            "pageSize" => "A4",
            "orientation" => "portrait",
            "margins" => array(20, 20, 20, 20)
        )
    );
}

// Localize script data
wp_localize_script("pdf-builder-vanilla-bundle", "pdfBuilderData", array(
    "templateData" => $template_data,
    "templateId" => $template_id,
    "ajaxUrl" => admin_url("admin-ajax.php"),
    "nonce" => wp_create_nonce("pdf_builder_nonce"),
    "strings" => array(
        "loading" => __("Loading PDF Editor...", "pdf-builder-pro"),
        "error" => __("Error loading editor", "pdf-builder-pro"),
        "save" => __("Save Template", "pdf-builder-pro"),
        "preview" => __("Preview PDF", "pdf-builder-pro")
    )
));
?>

<div class="wrap">
    <h1><?php echo $template_id ? __("Edit PDF Template", "pdf-builder-pro") : __("Create PDF Template", "pdf-builder-pro"); ?></h1>

    <div id="pdf-builder-editor-container">
        <div id="pdf-builder-loading">
            <div class="spinner"></div>
            <p><?php _e("Loading PDF Editor...", "pdf-builder-pro"); ?></p>
        </div>

        <div id="pdf-builder-error" style="display: none;">
            <div class="notice notice-error">
                <p><?php _e("Error loading PDF editor. Please check console for details.", "pdf-builder-pro"); ?></p>
            </div>
        </div>

        <div id="pdf-builder-toolbar" style="display: none;">
            <!-- Toolbar will be populated by JavaScript -->
        </div>

        <div id="pdf-builder-canvas-container" style="display: none;">
            <canvas id="pdf-builder-canvas"></canvas>
        </div>

        <div id="pdf-builder-properties" style="display: none;">
            <!-- Properties panel will be populated by JavaScript -->
        </div>
    </div>
</div>

<style>
#pdf-builder-loading {
    text-align: center;
    padding: 50px;
}

#pdf-builder-loading .spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 2s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

#pdf-builder-canvas-container {
    border: 1px solid #ddd;
    margin: 20px 0;
    background: #f9f9f9;
    overflow: auto;
}

#pdf-builder-canvas {
    display: block;
    margin: 0 auto;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    console.log("DOM ready, initializing PDF Builder Vanilla JS...");

    // Function to check if script is loaded
    function checkScriptLoaded() {
        if (typeof window.PDFBuilderPro === "undefined") {
            console.log("PDFBuilderPro not yet loaded, waiting...");
            setTimeout(checkScriptLoaded, 100);
            return;
        }

        console.log("PDFBuilderPro bundle loaded:", window.PDFBuilderPro);

        // Check if required classes are available
        if (typeof window.PDFBuilderPro.PDFCanvasVanilla === "undefined") {
            console.error("PDFCanvasVanilla class not found in bundle");
            showError();
            return;
        }

        console.log("All required classes found, initializing editor...");

        try {
            // Initialize the editor
            initializePDFEditor();
        } catch (error) {
            console.error("Error initializing PDF editor:", error);
            showError();
        }
    }

    // Start checking for script load
    checkScriptLoaded();
});

function showError() {
    document.getElementById("pdf-builder-loading").style.display = "none";
    document.getElementById("pdf-builder-error").style.display = "block";
}

function initializePDFEditor() {
    const canvas = document.getElementById("pdf-builder-canvas");
    const toolbar = document.getElementById("pdf-builder-toolbar");
    const properties = document.getElementById("pdf-builder-properties");

    if (!canvas || !toolbar || !properties) {
        console.error("Required DOM elements not found");
        showError();
        return;
    }

    // Get template data from localized script
    const templateData = window.pdfBuilderData ? window.pdfBuilderData.templateData : {};
    const templateId = window.pdfBuilderData ? window.pdfBuilderData.templateId : 0;

    console.log("Initializing with template data:", templateData);

    // Initialize canvas
    const pdfCanvas = new window.PDFBuilderPro.PDFCanvasVanilla(canvas, {
        width: 595, // A4 width in points
        height: 842, // A4 height in points,
        templateData: templateData
    });

    console.log("PDF Editor initialized successfully");

    // Hide loading, show editor
    document.getElementById("pdf-builder-loading").style.display = "none";
    document.getElementById("pdf-builder-toolbar").style.display = "block";
    document.getElementById("pdf-builder-canvas-container").style.display = "block";
    document.getElementById("pdf-builder-properties").style.display = "block";
}
</script>
