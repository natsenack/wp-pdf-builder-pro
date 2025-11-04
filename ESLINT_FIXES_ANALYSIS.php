<?php
/**
 * ESLINT_FIXES_ANALYSIS.php
 * Analyse et plan de correction des erreurs ESLint
 * 
 * Erreurs à corriger:
 * 1. Variables inutilisées (45)
 * 2. Accès avant déclaration (12)
 * 3. Globals non définis (18)
 * 4. React Hooks (13)
 * 5. Syntaxe React/JSX (15)
 * 6. Types TypeScript (300 - optionnel)
 */

class ESLintFixAnalysis {
    private $report = [];
    
    public function __construct() {
        $this->report = [
            'critical_fixes' => $this->getCriticalFixes(),
            'high_priority' => $this->getHighPriorityFixes(),
            'medium_priority' => $this->getMediumPriorityFixes(),
        ];
    }

    private function getCriticalFixes() {
        return [
            [
                'issue' => 'Accès avant déclaration',
                'file' => 'Canvas.tsx',
                'lines' => '74-162',
                'severity' => 'CRITICAL',
                'fix' => 'Déplacer les déclarations avant utilisation',
                'examples' => [
                    'drawRectangle (ligne 74, déclaration 162)',
                    'drawCircle (ligne 77, déclaration 182)',
                    'drawText (ligne 80, déclaration 201)',
                    'drawLine (ligne 83, déclaration 220)',
                ]
            ],
            [
                'issue' => 'Accès avant déclaration',
                'file' => 'useCanvasInteraction.ts',
                'lines' => '185-264, 214-241',
                'severity' => 'CRITICAL',
                'fix' => 'Hoisting des fonctions ou arrow functions avant utilisation',
                'examples' => [
                    'getResizeHandleAtPosition utilisé ligne 185, déclaré ligne 264',
                    'getResizeCursor utilisé ligne 214, déclaré ligne 241',
                ]
            ],
            [
                'issue' => 'setState dans useEffect',
                'file' => 'SaveIndicator.tsx',
                'line' => '42',
                'severity' => 'CRITICAL',
                'fix' => 'Utiliser setTimeout ou créer une fonction de callback',
                'current' => 'setVisible(false); // Directement dans effect',
                'corrected' => 'setTimeout(() => setVisible(false), 0);'
            ],
            [
                'issue' => 'Déclaration lexicale dans switch',
                'file' => 'BuilderContext.tsx',
                'lines' => '315, 329, 387-396',
                'severity' => 'CRITICAL',
                'fix' => 'Ajouter des accolades {} autour des cases',
                'example' => 'case ACTION: { const newState = ...; break; }'
            ]
        ];
    }

    private function getHighPriorityFixes() {
        return [
            [
                'issue' => 'Variables inutilisées',
                'count' => 45,
                'files' => 'Canvas.tsx, Header.tsx, PropertiesPanel.tsx, etc.',
                'fix_type' => 'Ajouter préfixe underscore',
                'example' => [
                    'const _dispatch = useReducer(...)',
                    'const [_Point] = useState()',
                ],
                'estimate' => '1 heure'
            ],
            [
                'issue' => 'Globals non définis',
                'count' => 18,
                'globals_missing' => [
                    'alert', 'navigator', 'URLSearchParams',
                    'AbortController', 'Image', 'process',
                    'queueMicrotask', 'NodeJS'
                ],
                'fix' => 'Ajouter /* global ... */ ou configurer .eslintrc',
                'estimate' => '30 minutes'
            ],
            [
                'issue' => 'React Hooks dépendances',
                'count' => 13,
                'fix' => 'Ajouter dépendances manquantes à useCallback/useEffect',
                'example' => [
                    'useCallback missing dependency: drawElement',
                    'useEffect missing dependency: loadExistingTemplate',
                ],
                'estimate' => '1 heure'
            ],
            [
                'issue' => 'Entités non échappées JSX',
                'count' => 15,
                'files' => 'Header.tsx, CompanyInfoProperties.tsx, etc.',
                'fix' => 'Remplacer apostrophe/guillemets par entités HTML',
                'example' => [
                    'L\'utilisateur → L&apos;utilisateur',
                    '"Test" → &quot;Test&quot;'
                ],
                'estimate' => '45 minutes'
            ]
        ];
    }

    private function getMediumPriorityFixes() {
        return [
            [
                'issue' => 'Try/catch inutiles',
                'count' => 2,
                'file' => 'WooCommerceElementsManager.ts',
                'fix' => 'Supprimer try/catch ou ajouter logique',
                'estimate' => '15 minutes'
            ],
            [
                'issue' => 'Types TypeScript (any)',
                'count' => 300,
                'severity' => 'MEDIUM',
                'fix' => 'Remplacer any par types génériques',
                'estimate' => '3-5 heures (optionnel)',
                'priority' => 'BASSE - Peut être fait incrementalement'
            ]
        ];
    }

    public function printSummary() {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "ESLINT FIXES ANALYSIS - PDF BUILDER PRO\n";
        echo str_repeat("=", 80) . "\n\n";

        echo "📋 ERREURS CRITIQUES À CORRIGER (IMMÉDIATEMENT)\n";
        echo str_repeat("-", 80) . "\n";
        foreach ($this->report['critical_fixes'] as $i => $fix) {
            echo "\n" . ($i + 1) . ". {$fix['issue']}\n";
            echo "   Fichier: {$fix['file']}\n";
            echo "   Ligne(s): {$fix['lines']}\n";
            echo "   Correction: {$fix['fix']}\n";
            
            if (isset($fix['examples'])) {
                echo "   Exemples:\n";
                foreach ($fix['examples'] as $example) {
                    echo "     - " . $example . "\n";
                }
            }
        }

        echo "\n\n📊 ERREURS HAUTE PRIORITÉ (1-2 heures)\n";
        echo str_repeat("-", 80) . "\n";
        foreach ($this->report['high_priority'] as $i => $fix) {
            echo "\n" . ($i + 1) . ". {$fix['issue']} ({$fix['count']} occurrences)\n";
            echo "   Estimation: {$fix['estimate']}\n";
            if (isset($fix['files'])) {
                echo "   Fichiers: {$fix['files']}\n";
            }
        }

        echo "\n\n📅 PLANNING RECOMMANDÉ\n";
        echo str_repeat("-", 80) . "\n";
        echo "Phase 1 (CRITIQUE): 1-2 heures\n";
        echo "  ✓ Corriger accès avant déclaration\n";
        echo "  ✓ Corriger setState dans effects\n";
        echo "  ✓ Corriger switch/case\n\n";

        echo "Phase 2 (HAUTE): 1-2 heures\n";
        echo "  ✓ Variables inutilisées\n";
        echo "  ✓ Globals navigateur\n";
        echo "  ✓ React Hooks\n";
        echo "  ✓ Entités JSX\n\n";

        echo "Phase 3 (OPTIONNELLE): 3-5 heures\n";
        echo "  ✓ Types TypeScript (any → types)\n\n";

        echo "TOTAL TEMPS ESTIMÉ: 4-6 heures pour corrections critiques\n";
        echo "\n" . str_repeat("=", 80) . "\n";
    }

    public function getFixCommands() {
        return [
            'info' => 'Commandes pour corriger ESLint',
            'commands' => [
                'eslint --fix' => 'npx eslint assets/js/src --fix',
                'info' => 'Cela corrigera automatiquement:
                         - Indentation
                         - Espaces
                         - Quelques variables inutilisées'
            ]
        ];
    }
}

$analyzer = new ESLintFixAnalysis();
$analyzer->printSummary();
?>
