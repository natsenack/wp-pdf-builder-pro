<?php
// DERNIÈRE SOLUTION - SCRIPT CLIENT QUI AJOUTE LE BOUTON DIRECTEMENT
echo "<script>
// Fonction pour ajouter le bouton flottant côté client
function addFloatingSaveButton() {
    // Vérifier si le bouton existe déjà
    if (document.getElementById('pdf-builder-floating-save-btn')) {
        console.log('🎯 Bouton flottant déjà présent');
        return;
    }

    // Créer le conteneur
    var container = document.createElement('div');
    container.id = 'pdf-builder-floating-save-container';
    container.style.cssText = 'position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: block; background: #fff; border: 2px solid #007cba; border-radius: 8px; padding: 5px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);';

    // Créer le bouton
    var button = document.createElement('button');
    button.id = 'pdf-builder-floating-save-btn';
    button.className = 'button button-primary';
    button.innerHTML = '💾 CLIENT-SIDE - Enregistrer';
    button.style.cssText = 'padding: 12px 20px; font-size: 16px; border-radius: 8px; transition: all 0.3s ease; cursor: pointer;';

    // Ajouter le bouton au conteneur
    container.appendChild(button);

    // Ajouter le conteneur au body
    document.body.appendChild(container);

    console.log('🎯 BOUTON FLOTTANT AJOUTÉ CÔTÉ CLIENT - ' + new Date().toLocaleTimeString());

    // Ajouter un événement de clic
    button.addEventListener('click', function() {
        console.log('💾 Bouton flottant cliqué !');
        alert('Bouton flottant fonctionnel !');
    });
}

// Exécuter immédiatement et après chargement du DOM
addFloatingSaveButton();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', addFloatingSaveButton);
} else {
    addFloatingSaveButton();
}

// Retry toutes les secondes pendant 10 secondes
var retryCount = 0;
var retryInterval = setInterval(function() {
    retryCount++;
    if (retryCount > 10) {
        clearInterval(retryInterval);
        return;
    }
    addFloatingSaveButton();
}, 1000);

console.log('🚀 SCRIPT CLIENT CHARGÉ - ' + new Date().toLocaleTimeString());
</script>";
?>