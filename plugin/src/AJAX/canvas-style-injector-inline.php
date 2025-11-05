<?php
/**
 * PDF Builder Pro - Canvas Style Injector (inlined in PHP)
 * Instead of loading a separate JS file, we inline the style injector script
 */

add_action('wp_footer', function() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Check if we're on the React editor page
    $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
    if ($page !== 'pdf-builder-react-editor') {
        return;
    }
    
    ?>
    <script type="text/javascript">
    /* PDF Builder Pro - Canvas Element Style Injector */
    (function() {
        'use strict';

        // Function to apply element styles
        function applyElementStyles(element, elementData) {
            if (!elementData || !elementData.properties) {
                return;
            }

            const props = elementData.properties;
            const elementId = elementData.id;
            const type = elementData.type;
            const styles = [];

            // Apply styles based on element type
            if (type === 'rectangle' || type === 'shape') {
                if (props.fillColor) {
                    styles.push('background-color', props.fillColor);
                }
                if (props.strokeColor && props.strokeWidth) {
                    styles.push('border', props.strokeWidth + 'px solid ' + props.strokeColor);
                }
            }

            if (type === 'circle') {
                if (props.fillColor) {
                    styles.push('background-color', props.fillColor);
                }
                if (props.strokeColor && props.strokeWidth) {
                    styles.push('border', props.strokeWidth + 'px solid ' + props.strokeColor);
                }
                styles.push('border-radius', '50%');
            }

            if (type === 'line') {
                if (props.strokeColor) {
                    const strokeWidth = props.strokeWidth || 1;
                    styles.push('border-top', strokeWidth + 'px solid ' + props.strokeColor);
                    styles.push('height', '0px');
                }
            }

            if (['text', 'document_type', 'order_number', 'dynamic-text', 'company_info', 'customer_info'].includes(type)) {
                if (props.color || props.textColor) {
                    const color = props.color || props.textColor;
                    styles.push('color', color);
                }
                if (props.fontSize) {
                    styles.push('font-size', props.fontSize + 'px');
                }
                if (props.fontFamily) {
                    styles.push('font-family', props.fontFamily + ', sans-serif');
                }
                if (props.fontWeight) {
                    styles.push('font-weight', props.fontWeight);
                }
                if (props.textAlign) {
                    styles.push('text-align', props.textAlign);
                }
                if (props.backgroundColor) {
                    styles.push('background-color', props.backgroundColor);
                }
            }

            if (['product_table', 'items_table'].includes(type)) {
                if (props.backgroundColor) {
                    styles.push('background-color', props.backgroundColor);
                }
                if (props.borderColor && props.borderWidth) {
                    styles.push('border', props.borderWidth + 'px solid ' + props.borderColor);
                }
            }

            // Apply styles to element
            for (let i = 0; i < styles.length; i += 2) {
                if (element && element.style) {
                    const prop = styles[i];
                    const value = styles[i + 1];
                    element.style[prop.replace(/-([a-z])/g, function(g) { return g[1].toUpperCase(); })] = value;
                }
            }
        }

        // Monitor DOM for new canvas elements and apply styles
        function monitorCanvas() {
            const styleSheet = document.createElement('style');
            styleSheet.id = 'pdf-builder-element-styles-applied';
            styleSheet.textContent = '';
            document.head.appendChild(styleSheet);

            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) {
                            // Check if this is a canvas element
                            if (node.classList && node.classList.contains('canvas-element')) {
                                const elementId = node.getAttribute('data-element-id') || node.id;
                                if (elementId && window.pdfBuilderCanvasElements) {
                                    // Try to find element data
                                    const element = window.pdfBuilderCanvasElements.find(function(e) {
                                        return e.id === elementId;
                                    });
                                    if (element) {
                                        applyElementStyles(node, element);
                                    }
                                }
                            }
                        }
                    });
                });
            });

            const canvas = document.querySelector('.canvas, [class*="canvas"]');
            if (canvas) {
                observer.observe(canvas, {
                    childList: true,
                    subtree: true
                });
            }
        }

        // Wait for document to load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(monitorCanvas, 500);
            });
        } else {
            setTimeout(monitorCanvas, 500);
        }

        // Expose global object for storing element data from Redux/store
        window.pdfBuilderCanvasElements = [];

        // Listen for AJAX calls that load templates
        const originalFetch = window.fetch;
        window.fetch = function() {
            const result = originalFetch.apply(this, arguments);
            return result.then(function(response) {
                if (response.ok && (arguments[0].includes('load_builtin_template') || arguments[0].includes('load_template'))) {
                    response.clone().json().then(function(data) {
                        if (data.success && data.data && data.data.template && data.data.template.elements) {
                            window.pdfBuilderCanvasElements = data.data.template.elements;
                            // Trigger re-application of styles after a short delay
                            setTimeout(function() {
                                const elements = document.querySelectorAll('.canvas-element');
                                elements.forEach(function(el) {
                                    const elementId = el.getAttribute('data-element-id') || el.id;
                                    if (elementId) {
                                        const element = window.pdfBuilderCanvasElements.find(function(e) {
                                            return e.id === elementId;
                                        });
                                        if (element) {
                                            applyElementStyles(el, element);
                                        }
                                    }
                                });
                            }, 100);
                        }
                    }).catch(function() {});
                }
                return response;
            });
        };
    })();
    </script>
    <?php
}, 10);
