<?php
/**
 * Diagnostic script for tab functionality
 * This will help identify why tabs are not switching properly
 */
echo "<!-- PDF Builder Tab Diagnostic Loaded -->";
?>

<script>
// Diagnostic for tab functionality
if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
    console.log('=== PDF BUILDER TAB DIAGNOSTIC ===');
}

// Check if DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
        console.log('DOM loaded, running diagnostic...');
    }

    // Check navigation tabs
    const navTabs = document.querySelectorAll('.nav-tab');
    if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
        console.log('Found nav tabs:', navTabs.length);
    }
    navTabs.forEach((tab, index) => {
        if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
            console.log(`Tab ${index}:`, {
                href: tab.getAttribute('href'),
                text: tab.textContent.trim(),
                classes: tab.className,
                visible: tab.offsetWidth > 0 && tab.offsetHeight > 0
            });
        }
    });

    // Check tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
        console.log('Found tab contents:', tabContents.length);
    }
    tabContents.forEach((content, index) => {
        if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
            console.log(`Content ${index}:`, {
                id: content.id,
                classes: content.className,
                display: window.getComputedStyle(content).display,
                visibility: window.getComputedStyle(content).visibility,
                hasActiveClass: content.classList.contains('active'),
                childElements: content.children.length
            });
        }
    });

    // Check if initializeTabs function exists
    if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
        console.log('initializeTabs function exists:', typeof window.initializeTabs === 'function');
    }

    // Test manual tab switching
    if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
        console.log('Testing manual tab switch to "general"...');
    }
    const generalTab = document.querySelector('.nav-tab[href="#general"]');
    const generalContent = document.getElementById('general');

    if (generalTab && generalContent) {
        if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
            console.log('Found general tab and content, simulating click...');
        }

        // Hide all contents
        document.querySelectorAll('.tab-content').forEach(c => {
            c.classList.remove('active');
            c.style.display = 'none';
        });

        // Show general content
        generalContent.classList.add('active');
        generalContent.style.display = 'block';

        // Update tab active state
        document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('nav-tab-active'));
        generalTab.classList.add('nav-tab-active');

        if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
            console.log('Manual switch completed. General content should now be visible.');
            console.log('General content display:', window.getComputedStyle(generalContent).display);
            console.log('General tab active:', generalTab.classList.contains('nav-tab-active'));
        }
    } else {
        if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
            console.log('ERROR: Could not find general tab or content');
            console.log('General tab found:', !!generalTab);
            console.log('General content found:', !!generalContent);
        }
    }

    if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
        console.log('=== DIAGNOSTIC COMPLETE ===');
    }
});
</script>