
// Inclure jsPDF (à ajouter via CDN ou localement)
// <script src='https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js'></script>

function generatePDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Configuration de base
    doc.setFont('helvetica');

    // Élément texte
    doc.setFontSize(16);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(0, 0, 0);
    doc.text('Test PDF Client-Side Generation', 13.22915, 13.22915);

    // Élément texte
    doc.setFontSize(12);
    doc.setTextColor(102, 102, 102);
    doc.text('Généré avec jsPDF', 13.22915, 26.4583);


    // Sauvegarder le PDF
    doc.save('document.pdf');
}

// Générer automatiquement ou sur clic bouton
document.addEventListener('DOMContentLoaded', function() {
    const generateBtn = document.getElementById('generate-pdf-btn');
    if (generateBtn) {
        generateBtn.addEventListener('click', generatePDF);
    }
});
