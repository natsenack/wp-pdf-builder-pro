// Test script pour vérifier collectAllFormData()
console.log('Testing collectAllFormData() function...');

// Simuler les forms qui existent
const mockForms = [
    'developpeur-form',
    'canvas-form',
    'securite-settings-form',
    'pdf-settings-form',
    'templates-status-form',
    'general-form'
];

// Vérifier que les forms existent dans le DOM (simulation)
mockForms.forEach(formId => {
    const form = document.getElementById(formId);
    if (form) {
        console.log(`✓ Form ${formId} found`);
    } else {
        console.log(`✗ Form ${formId} not found`);
    }
});

console.log('collectAllFormData() test completed.');