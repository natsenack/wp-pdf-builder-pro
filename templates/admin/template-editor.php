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

        // Sanitize template data to prevent JavaScript injection
        if (is_array($template_data)) {
            $template_data = sanitize_template_data_recursive($template_data);
        }
    }
}

// Function to recursively sanitize template data
function sanitize_template_data_recursive($data) {
    if (is_array($data)) {
        $sanitized = array();
        foreach ($data as $key => $value) {
            $sanitized[$key] = sanitize_template_data_recursive($value);
        }
        return $sanitized;
    } elseif (is_string($data)) {
        // Remove any HTML tags and encode special characters
        $data = wp_strip_all_tags($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    } else {
        // For numbers, booleans, null, return as-is
        return $data;
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

// Localize script data - moved to after DOM elements to ensure script is registered
// Use wp_add_inline_script instead of wp_localize_script to avoid JSON encoding issues
wp_add_inline_script('pdf-builder-vanilla-bundle', '
    window.pdfBuilderData = ' . wp_json_encode(array(
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
    )) . ';
', 'before');
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
            <div class="toolbar-section">
                <h4>Outils</h4>
                <button id="add-text-btn" class="btn">
                    <span class="dashicons dashicons-editor-textcolor"></span>
                    Texte
                </button>
                <button id="add-rectangle-btn" class="btn">
                    <span class="dashicons dashicons-screenoptions"></span>
                    Rectangle
                </button>
                <button id="add-circle-btn" class="btn">
                    <span class="dashicons dashicons-marker"></span>
                    Cercle
                </button>
                <button id="add-line-btn" class="btn">
                    <span class="dashicons dashicons-minus"></span>
                    Ligne
                </button>
            </div>
            <div class="toolbar-section">
                <h4>Actions</h4>
                <button id="save-btn" class="btn btn-primary">
                    <span class="dashicons dashicons-saved"></span>
                    Sauvegarder
                </button>
                <button id="export-pdf-btn" class="btn">
                    <span class="dashicons dashicons-pdf"></span>
                    Exporter PDF
                </button>
                <button id="preview-btn" class="btn">
                    <span class="dashicons dashicons-visibility"></span>
                    Aperçu
                </button>
            </div>
        </div>

        <div id="pdf-builder-workspace">
            <div id="pdf-builder-canvas-container" style="display: none;">
                <canvas id="pdf-builder-canvas"></canvas>
            </div>

            <div id="pdf-builder-properties" style="display: none;">
                <div class="properties-header">
                    <h3>Propriétés</h3>
                </div>
                <div class="properties-content">
                    <div id="no-selection" class="properties-message">
                        <p>Sélectionnez un élément pour modifier ses propriétés.</p>
                    </div>
                    <div id="element-properties" style="display: none;">
                        <div class="properties-group">
                            <label for="element-x">Position X:</label>
                            <input type="number" id="element-x" step="1">
                        </div>
                        <div class="properties-group">
                            <label for="element-y">Position Y:</label>
                            <input type="number" id="element-y" step="1">
                        </div>
                        <div class="properties-group">
                            <label for="element-width">Largeur:</label>
                            <input type="number" id="element-width" step="1" min="1">
                        </div>
                        <div class="properties-group">
                            <label for="element-height">Hauteur:</label>
                            <input type="number" id="element-height" step="1" min="1">
                        </div>
                        <div class="properties-group" id="text-properties" style="display: none;">
                            <label for="element-text">Texte:</label>
                            <textarea id="element-text" rows="3"></textarea>
                            <label for="element-font-size">Taille police:</label>
                            <input type="number" id="element-font-size" step="1" min="8" max="72">
                            <label for="element-color">Couleur:</label>
                            <input type="color" id="element-color">
                        </div>
                        <div class="properties-group" id="shape-properties" style="display: none;">
                            <label for="element-fill-color">Couleur de remplissage:</label>
                            <input type="color" id="element-fill-color">
                            <label for="element-stroke-color">Couleur de bordure:</label>
                            <input type="color" id="element-stroke-color">
                            <label for="element-stroke-width">Épaisseur bordure:</label>
                            <input type="number" id="element-stroke-width" step="1" min="0" max="20">
                        </div>
                    </div>
                </div>
            </div>
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

#pdf-builder-workspace {
    display: flex;
    min-height: 600px;
}

#pdf-builder-canvas-container {
    flex: 1;
    border: 1px solid #ddd;
    margin: 0;
    background: #f9f9f9;
    overflow: auto;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 20px;
}

#pdf-builder-canvas {
    display: block;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    background: white;
}

/* Toolbar Styles */
#pdf-builder-toolbar {
    display: flex;
    gap: 20px;
    padding: 15px;
    background: #ffffff;
    border-bottom: 1px solid #e1e5e9;
    flex-wrap: wrap;
}

.toolbar-section {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.toolbar-section h4 {
    margin: 0 0 8px 0;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: #ffffff;
    color: #374151;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
    text-decoration: none;
}

.btn:hover {
    background: #f9fafb;
    border-color: #9ca3af;
    transform: translateY(-1px);
}

.btn-primary {
    background: #2563eb;
    color: white;
    border-color: #2563eb;
}

.btn-primary:hover {
    background: #1d4ed8;
    border-color: #1d4ed8;
}

/* Properties Panel Styles */
#pdf-builder-properties {
    width: 320px;
    background: #fafbfc;
    border-left: 1px solid #e1e5e9;
    display: flex;
    flex-direction: column;
}

.properties-header {
    padding: 16px;
    border-bottom: 1px solid #e1e5e9;
    background: #ffffff;
}

.properties-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.properties-content {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
}

.properties-message {
    text-align: center;
    color: #6b7280;
    font-style: italic;
}

.properties-group {
    margin-bottom: 16px;
}

.properties-group label {
    display: block;
    font-weight: 500;
    margin-bottom: 4px;
    color: #374151;
    font-size: 14px;
}

.properties-group input,
.properties-group select,
.properties-group textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 14px;
    background: #ffffff;
    box-sizing: border-box;
}

.properties-group input:focus,
.properties-group select:focus,
.properties-group textarea:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    console.log("DOM ready, initializing PDF Builder Vanilla JS...");

    // Function to initialize the editor once script is loaded
    function initializeEditor() {
        if (typeof window.PDFBuilderPro !== "undefined") {
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
        } else {
            console.error("PDFBuilderPro global not found after script load");
            showError();
        }
    }

    // Function to handle script load errors
    function handleScriptError() {
        console.error("PDFBuilderPro script failed to load");
        console.error("Checking script tags...");
        // Debug: check if script tag exists
        const scripts = document.querySelectorAll('script[src*="pdf-builder-admin"]');
        console.log("Found script tags:", scripts);
        scripts.forEach(script => {
            console.log("Script src:", script.src, "loaded:", script.complete, "error:", script.onerror);
        });
        showError();
    }

    // Check if script is already loaded (in case it loaded before DOM ready)
    if (typeof window.PDFBuilderPro !== "undefined") {
        console.log("Script already loaded, initializing immediately");
        initializeEditor();
        return;
    }

    // Use MutationObserver to detect when the script is added to DOM
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.tagName === 'SCRIPT' && node.src && node.src.includes('pdf-builder-admin')) {
                    console.log("PDF Builder script detected in DOM:", node.src);

                    // Stop observing once we find the script
                    observer.disconnect();

                    // Listen for load and error events
                    node.addEventListener('load', function() {
                        console.log("PDF Builder script loaded successfully");
                        // Small delay to ensure global variables are set
                        setTimeout(initializeEditor, 100);
                    });

                    node.addEventListener('error', handleScriptError);

                    // If script is already loaded (cached), initialize immediately
                    if (node.complete) {
                        console.log("Script was already loaded (cached)");
                        setTimeout(initializeEditor, 100);
                    }
                }
            });
        });
    });

    // Start observing
    observer.observe(document.head || document.documentElement, {
        childList: true,
        subtree: true
    });

    // Fallback timeout in case script loading takes too long
    setTimeout(function() {
        if (typeof window.PDFBuilderPro === "undefined") {
            console.error("PDFBuilderPro script failed to load within timeout");
            observer.disconnect();
            handleScriptError();
        }
    }, 10000); // 10 second timeout

function setupToolbarEvents(pdfCanvas) {
    // Add element buttons
    document.getElementById("add-text-btn").addEventListener("click", () => {
        const elementId = pdfCanvas.addElement("text", {
            x: 50,
            y: 50,
            text: "Nouveau texte",
            fontSize: 16,
            color: "#000000"
        });
        console.log("Added text element:", elementId);
    });

    document.getElementById("add-rectangle-btn").addEventListener("click", () => {
        const elementId = pdfCanvas.addElement("rectangle", {
            x: 100,
            y: 100,
            width: 100,
            height: 60,
            fillColor: "#cccccc",
            strokeColor: "#000000",
            strokeWidth: 1
        });
        console.log("Added rectangle element:", elementId);
    });

    document.getElementById("add-circle-btn").addEventListener("click", () => {
        const elementId = pdfCanvas.addElement("circle", {
            x: 150,
            y: 150,
            width: 80,
            height: 80,
            fillColor: "#cccccc",
            strokeColor: "#000000",
            strokeWidth: 1
        });
        console.log("Added circle element:", elementId);
    });

    document.getElementById("add-line-btn").addEventListener("click", () => {
        const elementId = pdfCanvas.addElement("line", {
            x: 200,
            y: 200,
            width: 100,
            height: 2,
            strokeColor: "#000000",
            strokeWidth: 2
        });
        console.log("Added line element:", elementId);
    });

    // Action buttons
    document.getElementById("save-btn").addEventListener("click", () => {
        alert("Fonction de sauvegarde à implémenter");
    });

    document.getElementById("export-pdf-btn").addEventListener("click", () => {
        alert("Fonction d'export PDF à implémenter");
    });

    document.getElementById("preview-btn").addEventListener("click", () => {
        alert("Fonction d'aperçu à implémenter");
    });
}

function setupPropertiesPanel(pdfCanvas) {
    // This will be enhanced when selection system is implemented
    console.log("Properties panel initialized");
}

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

    // Initialize canvas - pass container ID, not canvas element
    const pdfCanvas = new window.PDFBuilderPro.PDFCanvasVanilla("pdf-builder-canvas-container", {
        width: 595, // A4 width in points
        height: 842, // A4 height in points
        templateData: templateData
    });

    console.log("PDF Editor initialized successfully");

    // Setup toolbar event listeners
    setupToolbarEvents(pdfCanvas);

    // Setup properties panel
    setupPropertiesPanel(pdfCanvas);

    // Hide loading, show editor
    document.getElementById("pdf-builder-loading").style.display = "none";
    document.getElementById("pdf-builder-toolbar").style.display = "flex";
    document.getElementById("pdf-builder-canvas-container").style.display = "flex";
    document.getElementById("pdf-builder-properties").style.display = "flex";
}

function setupToolbarEvents(pdfCanvas) {
    // Add element buttons
    document.getElementById("add-text-btn").addEventListener("click", () => {
        const elementId = pdfCanvas.addElement("text", {
            x: 50,
            y: 50,
            text: "Nouveau texte",
            fontSize: 16,
            color: "#000000"
        });
        console.log("Added text element:", elementId);
    });

    document.getElementById("add-rectangle-btn").addEventListener("click", () => {
        const elementId = pdfCanvas.addElement("rectangle", {
            x: 100,
            y: 100,
            width: 100,
            height: 60,
            fillColor: "#cccccc",
            strokeColor: "#000000",
            strokeWidth: 1
        });
        console.log("Added rectangle element:", elementId);
    });

    document.getElementById("add-circle-btn").addEventListener("click", () => {
        const elementId = pdfCanvas.addElement("circle", {
            x: 150,
            y: 150,
            width: 80,
            height: 80,
            fillColor: "#cccccc",
            strokeColor: "#000000",
            strokeWidth: 1
        });
        console.log("Added circle element:", elementId);
    });

    document.getElementById("add-line-btn").addEventListener("click", () => {
        const elementId = pdfCanvas.addElement("line", {
            x: 200,
            y: 200,
            width: 100,
            height: 2,
            strokeColor: "#000000",
            strokeWidth: 2
        });
        console.log("Added line element:", elementId);
    });

    // Action buttons
    document.getElementById("save-btn").addEventListener("click", () => {
        alert("Fonction de sauvegarde à implémenter");
    });

    document.getElementById("export-pdf-btn").addEventListener("click", () => {
        alert("Fonction d'export PDF à implémenter");
    });

    document.getElementById("preview-btn").addEventListener("click", () => {
        alert("Fonction d'aperçu à implémenter");
    });
}

function setupPropertiesPanel(pdfCanvas) {
    // This will be enhanced when selection system is implemented
    console.log("Properties panel initialized");
}
