/**
 * HTML Generator API
 * Provides functions to generate HTML from JSON template data
 */

export async function renderTemplateHTML(templateData, orderData = {}) {
    try {
        const response = await fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'pdf_builder_render_template_html',
                template_data: JSON.stringify(templateData),
                order_data: JSON.stringify(orderData),
            })
        });

        const data = await response.json();

        if (data.success) {
            return data.data.html;
        } else {
            console.error('HTML generation failed:', data.data.error);
            throw new Error(data.data.error || 'Failed to generate HTML');
        }
    } catch (error) {
        console.error('Error calling renderTemplateHTML:', error);
        throw error;
    }
}

/**
 * Preview HTML in a modal or container
 */
export function previewHTML(html, containerId = 'pdf-preview-container') {
    const container = document.getElementById(containerId);
    if (!container) {
        console.error('Preview container not found:', containerId);
        return;
    }

    container.innerHTML = html;
}

/**
 * Export HTML as a downloadable file
 */
export function downloadHTML(html, filename = 'template.html') {
    const blob = new Blob([html], { type: 'text/html;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Copy HTML to clipboard
 */
export async function copyHTMLToClipboard(html) {
    try {
        await navigator.clipboard.writeText(html);
        return true;
    } catch (error) {
        console.error('Failed to copy HTML:', error);
        return false;
    }
}
