<?php
/**
 * Exemple d'utilisation des managers d'interactions canvas
 * PDF Builder Pro
 *
 * Ce fichier montre comment utiliser les nouveaux managers pour gérer
 * les interactions de drag & drop et de redimensionnement.
 */

// Inclure les fichiers nécessaires (dans un vrai plugin, ce serait fait automatiquement)
require_once 'PDF_Builder_Canvas_Elements_Manager.php';
require_once 'PDF_Builder_Drag_Drop_Manager.php';
require_once 'PDF_Builder_Resize_Manager.php';
require_once 'PDF_Builder_Canvas_Interactions_Manager.php';

/**
 * Exemple d'utilisation basique
 */
class Canvas_Interactions_Example {

    public function run_examples() {
        echo "<h2>Exemples d'utilisation des managers d'interactions canvas</h2>";

        $this->example_drag_drop();
        $this->example_resize();
        $this->example_canvas_elements();
        $this->example_interactions_manager();
    }

    /**
     * Exemple d'utilisation du Drag & Drop Manager
     */
    private function example_drag_drop() {
        echo "<h3>1. Drag & Drop Manager</h3>";

        $drag_manager = PDF_Builder_Drag_Drop_Manager::getInstance();

        // Simuler des éléments sélectionnés
        $element_ids = ['element_1', 'element_2'];
        $initial_positions = [
            'element_1' => ['x' => 100, 'y' => 100],
            'element_2' => ['x' => 200, 'y' => 200]
        ];

        // Démarrer une session de drag
        $session = $drag_manager->start_drag_session(
            'session_123',
            $element_ids,
            $initial_positions,
            ['width' => 800, 'height' => 600] // Canvas bounds
        );

        echo "<p>Session de drag démarrée: " . $session['id'] . "</p>";

        // Simuler un mouvement
        $new_positions = $drag_manager->update_drag_position(
            'session_123',
            50, // delta_x
            30, // delta_y
            true, // snap to grid
            10   // grid size
        );

        echo "<p>Nouvelles positions après mouvement:</p>";
        echo "<pre>" . print_r($new_positions, true) . "</pre>";

        // Terminer la session
        $result = $drag_manager->end_drag_session('session_123');
        echo "<p>Session terminée. Durée: " . $result['duration'] . " secondes</p>";
    }

    /**
     * Exemple d'utilisation du Resize Manager
     */
    private function example_resize() {
        echo "<h3>2. Resize Manager</h3>";

        $resize_manager = PDF_Builder_Resize_Manager::getInstance();

        // Démarrer une session de redimensionnement
        $session = $resize_manager->start_resize_session(
            'resize_session_123',
            'element_1',
            'se', // Sud-Est (coin inférieur droit)
            ['x' => 150, 'y' => 150], // Position de départ
            ['width' => 200, 'height' => 100] // Taille initiale
        );

        echo "<p>Session de redimensionnement démarrée: " . $session['id'] . "</p>";

        // Simuler un redimensionnement
        $dimensions = $resize_manager->update_resize_dimensions(
            'resize_session_123',
            50, // delta_x
            25  // delta_y
        );

        echo "<p>Nouvelles dimensions:</p>";
        echo "<pre>" . print_r($dimensions, true) . "</pre>";

        // Terminer la session
        $result = $resize_manager->end_resize_session('resize_session_123');
        echo "<p>Session de redimensionnement terminée.</p>";
    }

    /**
     * Exemple d'utilisation du Canvas Elements Manager
     */
    private function example_canvas_elements() {
        echo "<h3>3. Canvas Elements Manager</h3>";

        $elements_manager = PDF_Builder_Canvas_Elements_Manager::getInstance();

        // Créer un élément d'exemple
        $element = [
            'id' => 'text_element_1',
            'type' => 'text',
            'position' => ['x' => 50, 'y' => 50],
            'size' => ['width' => 150, 'height' => 30],
            'style' => ['fontSize' => 14, 'color' => '#000000'],
            'content' => 'Hello World'
        ];

        // Valider l'élément
        $errors = $elements_manager->validate_element_data($element);
        if (empty($errors)) {
            echo "<p>✅ Élément valide</p>";
        } else {
            echo "<p>❌ Erreurs de validation: " . implode(', ', $errors) . "</p>";
        }

        // Calculer un déplacement
        $new_position = $elements_manager->calculate_drag_position(
            $element,
            25, // delta_x
            15  // delta_y
        );

        echo "<p>Nouvelle position après déplacement: (" . $new_position['x'] . ", " . $new_position['y'] . ")</p>";

        // Calculer un redimensionnement
        $new_dimensions = $elements_manager->calculate_resize_dimensions(
            $element,
            'se', // Poignée sud-est
            30,  // delta_x
            10   // delta_y
        );

        echo "<p>Nouvelles dimensions après redimensionnement:</p>";
        echo "<pre>" . print_r($new_dimensions, true) . "</pre>";
    }

    /**
     * Exemple d'utilisation du Canvas Interactions Manager
     */
    private function example_interactions_manager() {
        echo "<h3>4. Canvas Interactions Manager</h3>";

        $interactions_manager = PDF_Builder_Canvas_Interactions_Manager::getInstance();

        // Obtenir les statistiques
        $stats = $interactions_manager->get_interaction_stats();

        echo "<p>Statistiques des interactions:</p>";
        echo "<pre>" . print_r($stats, true) . "</pre>";

        // Traiter une interaction complète
        $interaction_result = $interactions_manager->process_canvas_interaction('drag', [
            'element_ids' => ['elem1', 'elem2'],
            'delta_x' => 10,
            'delta_y' => 5
        ]);

        echo "<p>Résultat du traitement d'interaction:</p>";
        echo "<pre>" . print_r($interaction_result, true) . "</pre>";
    }
}

// Fonction pour exécuter les exemples (uniquement si appelé directement)
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    $example = new Canvas_Interactions_Example();
    $example->run_examples();
}

