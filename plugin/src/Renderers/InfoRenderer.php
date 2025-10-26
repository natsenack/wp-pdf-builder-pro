<?php
/**
 * PDF Builder Pro - InfoRenderer
 * Phase 3.3.5 - Renderer spécialisé pour les informations structurées
 *
 * Gère le rendu des éléments d'information :
 * - customer_info : Informations client (nom, adresse, contact)
 * - company_info : Informations société avec templates prédéfinis
 * - mentions : Mentions légales et conditions
 */

namespace PDF_Builder\Renderers;

// Sécurité WordPress
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// Import du système de cache
use PDF_Builder\Cache\RendererCache;

class InfoRenderer {

    /**
     * Types d'éléments supportés par ce renderer
     */
    const SUPPORTED_TYPES = ['customer_info', 'company_info', 'mentions'];

    /**
     * Styles CSS par défaut pour les informations
     */
    const DEFAULT_STYLES = [
        'font-family' => 'Arial, sans-serif',
        'font-size' => '12px',
        'color' => '#000000',
        'line-height' => '1.4',
        'margin-bottom' => '10px'
    ];

    /**
     * Templates prédéfinis pour company_info
     */
    const COMPANY_TEMPLATES = [
        'default' => [
            'fields' => ['name', 'address', 'phone', 'email'],
            'layout' => 'vertical',
            'separator' => ' | '
        ],
        'commercial' => [
            'fields' => ['name', 'address', 'phone', 'email', 'website'],
            'layout' => 'vertical',
            'separator' => ' • '
        ],
        'legal' => [
            'fields' => ['name', 'address', 'vat', 'rcs', 'capital', 'legal_form'],
            'layout' => 'vertical',
            'separator' => "\n"
        ],
        'minimal' => [
            'fields' => ['name', 'phone', 'email'],
            'layout' => 'horizontal',
            'separator' => ' | '
        ]
    ];

    /**
     * Templates prédéfinis pour les mentions légales
     */
    const MENTIONS_TEMPLATES = [
        'default' => [
            'title' => 'Mentions légales',
            'content' => "Conformément à la loi Informatique et Libertés du 6 janvier 1978, vous disposez d'un droit d'accès, de rectification et de suppression des données vous concernant.",
            'show_date' => true
        ],
        'commercial' => [
            'title' => 'Conditions générales de vente',
            'content' => "Les présentes conditions générales de vente s'appliquent à toutes nos prestations. Tout litige sera soumis aux tribunaux compétents.",
            'show_date' => true
        ],
        'legal' => [
            'title' => 'Informations légales',
            'content' => "Société enregistrée au RCS. Capital social : [capital] €. TVA : [vat]. Tous droits réservés.",
            'show_date' => false
        ]
    ];

    /**
     * Rend un élément d'information
     *
     * @param array $elementData Données de l'élément
     * @param array $context Contexte de rendu (données du provider)
     * @return array Résultat du rendu HTML/CSS
     */
    public function render(array $elementData, array $context = []): array {
        // Validation des données d'entrée
        if (!$this->validateElementData($elementData)) {
            return [
                'html' => '<!-- Erreur: Données élément invalides -->',
                'css' => '',
                'error' => 'Données élément invalides'
            ];
        }

        $type = $elementData['type'] ?? 'customer_info';
        $properties = $elementData['properties'] ?? [];

        // Rendu selon le type d'élément
        switch ($type) {
            case 'customer_info':
                return $this->renderCustomerInfo($properties, $context);

            case 'company_info':
                return $this->renderCompanyInfo($properties, $context);

            case 'mentions':
                return $this->renderMentions($properties, $context);

            default:
                return [
                    'html' => '<!-- Erreur: Type d\'élément non supporté -->',
                    'css' => '',
                    'error' => 'Type d\'élément non supporté: ' . $type
                ];
        }
    }

    /**
     * Rend les informations client
     *
     * @param array $properties Propriétés du rendu
     * @param array $context Données du contexte
     * @return array Résultat du rendu
     */
    private function renderCustomerInfo(array $properties, array $context): array {
        // Vérifier si les propriétés individuelles sont définies (contrôle UI)
        $useIndividualProperties = isset($properties['customerName']) ||
                                   isset($properties['customerEmail']) ||
                                   isset($properties['customerAddress']) ||
                                   isset($properties['customerPhone']);

        if ($useIndividualProperties) {
            // Utiliser les propriétés individuelles définies dans l'UI
            $customerData = [
                'first_name' => $properties['customerName'] ?? '',
                'last_name' => '',
                'email' => $properties['customerEmail'] ?? '',
                'phone' => $properties['customerPhone'] ?? '',
                'address' => $properties['customerAddress'] ?? ''
            ];
        } else {
            // Récupération automatique des données client depuis le contexte
            $customerData = $this->getCustomerData($context);
        }

        if (empty($customerData) || (empty($customerData['first_name']) && empty($customerData['email']) && empty($customerData['phone']) && empty($customerData['address']))) {
            return [
                'html' => '<div class="customer-info-placeholder">Informations client non disponibles</div>',
                'css' => '.customer-info-placeholder { padding: 10px; color: #999; font-style: italic; }',
                'error' => null
            ];
        }

        // Configuration des champs à afficher
        $fields = $properties['fields'] ?? ['first_name', 'last_name', 'address', 'email', 'phone'];
        $layout = $properties['layout'] ?? 'vertical';
        $showLabels = $properties['show_labels'] ?? true;

        // Génération du HTML
        $html = '<div class="customer-info">';

        if ($layout === 'vertical') {
            $html .= $this->renderVerticalLayout($customerData, $fields, $showLabels);
        } else {
            $html .= $this->renderHorizontalLayout($customerData, $fields, $showLabels);
        }

        $html .= '</div>';

        // Génération des styles CSS (avec cache)
        $styleKey = RendererCache::generateStyleKey($properties, 'info_customer');
        $css = RendererCache::get($styleKey);

        if ($css === null) {
            $css = $this->generateInfoStyles($properties, 'customer-info');
            RendererCache::set($styleKey, $css, 600); // Cache 10 minutes
        }

        return [
            'html' => $html,
            'css' => $css,
            'error' => null
        ];
    }

    /**
     * Rend les informations société
     *
     * @param array $properties Propriétés du rendu
     * @param array $context Données du contexte
     * @return array Résultat du rendu
     */
    private function renderCompanyInfo(array $properties, array $context): array {
        // Récupération des données société
        $companyData = $this->getCompanyData($context);

        // Template à utiliser
        $template = $properties['template'] ?? 'default';
        $templateConfig = self::COMPANY_TEMPLATES[$template] ?? self::COMPANY_TEMPLATES['default'];

        // Champs à afficher selon le template
        $fields = $templateConfig['fields'];
        $layout = $templateConfig['layout'];
        $separator = $templateConfig['separator'];

        // Génération du HTML
        $html = '<div class="company-info">';

        if ($layout === 'vertical') {
            $html .= $this->renderCompanyVerticalLayout($companyData, $fields, $separator);
        } else {
            $html .= $this->renderCompanyHorizontalLayout($companyData, $fields, $separator);
        }

        $html .= '</div>';

        // Génération des styles CSS (avec cache)
        $styleKey = RendererCache::generateStyleKey($properties, 'info_company');
        $css = RendererCache::get($styleKey);

        if ($css === null) {
            $css = $this->generateInfoStyles($properties, 'company-info');
            RendererCache::set($styleKey, $css, 600); // Cache 10 minutes
        }

        return [
            'html' => $html,
            'css' => $css,
            'error' => null
        ];
    }

    /**
     * Rend les mentions légales
     *
     * @param array $properties Propriétés du rendu
     * @param array $context Données du contexte
     * @return array Résultat du rendu
     */
    private function renderMentions(array $properties, array $context): array {
        // Template à utiliser
        $template = $properties['template'] ?? 'default';
        $templateConfig = self::MENTIONS_TEMPLATES[$template] ?? self::MENTIONS_TEMPLATES['default'];

        // Récupération des données société pour remplacer les variables
        $companyData = $this->getCompanyData($context);

        // Remplacement des variables dans le contenu
        $content = $this->replaceMentionsVariables($templateConfig['content'], $companyData);

        // Génération du HTML
        $html = '<div class="mentions">';
        $html .= '<h4 class="mentions-title">' . htmlspecialchars($templateConfig['title']) . '</h4>';
        $html .= '<div class="mentions-content">' . nl2br(htmlspecialchars($content)) . '</div>';

        if ($templateConfig['show_date']) {
            $html .= '<div class="mentions-date">Document généré le ' . date('d/m/Y') . '</div>';
        }

        $html .= '</div>';

        // Génération des styles CSS (avec cache)
        $styleKey = RendererCache::generateStyleKey($properties, 'info_mentions');
        $css = RendererCache::get($styleKey);

        if ($css === null) {
            $css = $this->generateMentionsStyles($properties);
            RendererCache::set($styleKey, $css, 600); // Cache 10 minutes
        }

        return [
            'html' => $html,
            'css' => $css,
            'error' => null
        ];
    }

    /**
     * Valide les données de l'élément
     *
     * @param array $elementData Données à valider
     * @return bool True si valide
     */
    private function validateElementData(array $elementData): bool {
        return isset($elementData['type']) &&
               in_array($elementData['type'], self::SUPPORTED_TYPES);
    }

    /**
     * Récupère les données client depuis le contexte
     *
     * @param array $context Données du contexte
     * @return array Données client
     */
    private function getCustomerData(array $context): array {
        return $context['customer'] ?? [];
    }

    /**
     * Récupère les données société depuis le contexte
     *
     * @param array $context Données du contexte
     * @return array Données société
     */
    private function getCompanyData(array $context): array {
        return $context['company'] ?? [];
    }

    /**
     * Rend un layout vertical pour customer_info
     *
     * @param array $data Données à afficher
     * @param array $fields Champs à afficher
     * @param bool $showLabels Afficher les labels
     * @return string HTML généré
     */
    private function renderVerticalLayout(array $data, array $fields, bool $showLabels): string {
        $html = '';

        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) continue;

            $label = $showLabels ? $this->getFieldLabel($field) . ': ' : '';
            $value = $this->formatFieldValue($field, $data[$field]);

            $html .= '<div class="info-row">';
            $html .= '<span class="info-label">' . htmlspecialchars($label) . '</span>';
            $html .= '<span class="info-value">' . htmlspecialchars($value) . '</span>';
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Rend un layout horizontal pour customer_info
     *
     * @param array $data Données à afficher
     * @param array $fields Champs à afficher
     * @param bool $showLabels Afficher les labels
     * @return string HTML généré
     */
    private function renderHorizontalLayout(array $data, array $fields, bool $showLabels): string {
        $html = '<div class="info-row">';

        $items = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) continue;

            $label = $showLabels ? $this->getFieldLabel($field) . ': ' : '';
            $value = $this->formatFieldValue($field, $data[$field]);

            $items[] = htmlspecialchars($label . $value);
        }

        $html .= implode(' | ', $items);
        $html .= '</div>';

        return $html;
    }

    /**
     * Rend un layout vertical pour company_info
     *
     * @param array $data Données société
     * @param array $fields Champs à afficher
     * @param string $separator Séparateur
     * @return string HTML généré
     */
    private function renderCompanyVerticalLayout(array $data, array $fields, string $separator): string {
        $html = '';

        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) continue;

            $value = $this->formatCompanyFieldValue($field, $data[$field]);

            $html .= '<div class="company-row">' . htmlspecialchars($value) . '</div>';
        }

        return $html;
    }

    /**
     * Rend un layout horizontal pour company_info
     *
     * @param array $data Données société
     * @param array $fields Champs à afficher
     * @param string $separator Séparateur
     * @return string HTML généré
     */
    private function renderCompanyHorizontalLayout(array $data, array $fields, string $separator): string {
        $items = [];

        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) continue;

            $value = $this->formatCompanyFieldValue($field, $data[$field]);
            $items[] = htmlspecialchars($value);
        }

        return '<div class="company-row">' . implode($separator, $items) . '</div>';
    }

    /**
     * Remplace les variables dans le contenu des mentions
     *
     * @param string $content Contenu avec variables
     * @param array $companyData Données société
     * @return string Contenu avec variables remplacées
     */
    private function replaceMentionsVariables(string $content, array $companyData): string {
        $replacements = [
            '[capital]' => $companyData['capital'] ?? 'XXX',
            '[vat]' => $companyData['vat'] ?? 'XXX',
            '[name]' => $companyData['name'] ?? 'XXX',
            '[date]' => date('d/m/Y')
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Obtient le label d'un champ
     *
     * @param string $field Nom du champ
     * @return string Label du champ
     */
    private function getFieldLabel(string $field): string {
        $labels = [
            'first_name' => 'Prénom',
            'last_name' => 'Nom',
            'address' => 'Adresse',
            'email' => 'Email',
            'phone' => 'Téléphone',
            'company' => 'Société'
        ];

        return $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    /**
     * Formate la valeur d'un champ
     *
     * @param string $field Nom du champ
     * @param mixed $value Valeur du champ
     * @return string Valeur formatée
     */
    private function formatFieldValue(string $field, $value): string {
        if (is_array($value)) {
            return implode(', ', array_filter($value));
        }

        return (string)$value;
    }

    /**
     * Formate la valeur d'un champ société
     *
     * @param string $field Nom du champ
     * @param mixed $value Valeur du champ
     * @return string Valeur formatée
     */
    private function formatCompanyFieldValue(string $field, $value): string {
        // Si la valeur est un array (comme l'adresse), la convertir en string
        if (is_array($value)) {
            switch ($field) {
                case 'address':
                    return implode(', ', array_filter($value));
                default:
                    return implode(', ', $value);
            }
        }

        switch ($field) {
            case 'capital':
                return 'Capital social : ' . number_format((float)$value, 2, ',', ' ') . ' €';

            case 'vat':
                return 'TVA : ' . $value;

            case 'rcs':
                return 'RCS : ' . $value;

            case 'phone':
                return 'Tél : ' . $value;

            case 'email':
                return 'Email : ' . $value;

            case 'website':
                return 'Web : ' . $value;

            default:
                return (string)$value;
        }
    }

    /**
     * Génère les styles CSS pour les informations
     *
     * @param array $properties Propriétés
     * @param string $className Classe CSS
     * @return string CSS généré
     */
    private function generateInfoStyles(array $properties, string $className): string {
        $css = [];

        // Styles de base
        $css[] = '.' . $className . ' {';
        $css[] = '  margin: 10px 0;';
        $css[] = '  padding: 5px;';
        $css[] = '}';

        // Styles des lignes
        $css[] = '.' . $className . ' .info-row {';
        $css[] = '  margin-bottom: 3px;';
        $css[] = '  line-height: 1.4;';
        $css[] = '}';

        // Styles des labels
        $css[] = '.' . $className . ' .info-label {';
        $css[] = '  font-weight: bold;';
        $css[] = '  margin-right: 5px;';
        $css[] = '}';

        // Styles des valeurs
        $css[] = '.' . $className . ' .info-value {';
        $css[] = '  color: #333;';
        $css[] = '}';

        // Styles spécifiques company
        if ($className === 'company-info') {
            $css[] = '.company-info .company-row {';
            $css[] = '  margin-bottom: 2px;';
            $css[] = '  font-size: 11px;';
            $css[] = '}';
        }

        return implode("\n", $css);
    }

    /**
     * Génère les styles CSS pour les mentions
     *
     * @param array $properties Propriétés
     * @return string CSS généré
     */
    private function generateMentionsStyles(array $properties): string {
        $css = [];

        // Styles de base
        $css[] = '.mentions {';
        $css[] = '  margin: 15px 0;';
        $css[] = '  padding: 10px;';
        $css[] = '  border-top: 1px solid #ddd;';
        $css[] = '  font-size: 10px;';
        $css[] = '  color: #666;';
        $css[] = '}';

        // Styles du titre
        $css[] = '.mentions .mentions-title {';
        $css[] = '  margin: 0 0 8px 0;';
        $css[] = '  font-size: 11px;';
        $css[] = '  font-weight: bold;';
        $css[] = '  text-transform: uppercase;';
        $css[] = '  color: #333;';
        $css[] = '}';

        // Styles du contenu
        $css[] = '.mentions .mentions-content {';
        $css[] = '  margin-bottom: 8px;';
        $css[] = '  line-height: 1.3;';
        $css[] = '}';

        // Styles de la date
        $css[] = '.mentions .mentions-date {';
        $css[] = '  font-style: italic;';
        $css[] = '  text-align: right;';
        $css[] = '  font-size: 9px;';
        $css[] = '}';

        return implode("\n", $css);
    }
}