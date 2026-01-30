<?php
/**
 * PDF Builder Pro - Database Repair Script
 * Exécuter ce script pour créer les tables manquantes
 * 
 * Usage: Accédez à cette URL depuis le navigateur:
 * https://threeaxe.fr/wp-content/plugins/wp-pdf-builder-pro/repair-database.php
 */

// Charger WordPress
require_once(__DIR__ . '/../../wp-load.php');

// Vérifier que l'utilisateur est administrateur
if (!current_user_can('manage_options')) {
    wp_die('Vous n\'êtes pas autorisé à accéder à cette page.');
}

global $wpdb;

echo '<h1>PDF Builder Pro - Réparation de la Base de Données</h1>';
echo '<p>Création des tables manquantes...</p>';

// 1. Créer la table wp_pdf_builder_settings
$table_settings = $wpdb->prefix . 'pdf_builder_settings';
$charset_collate = $wpdb->get_charset_collate();

$table_exists = $wpdb->get_var($wpdb->prepare(
    "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
    DB_NAME,
    $table_settings
));

if (!$table_exists) {
    $sql = "CREATE TABLE $table_settings (
        option_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        option_name varchar(191) NOT NULL DEFAULT '',
        option_value longtext NOT NULL,
        autoload varchar(20) NOT NULL DEFAULT 'yes',
        PRIMARY KEY (option_id),
        UNIQUE KEY option_name (option_name)
    ) $charset_collate;";
    
    if ($wpdb->query($sql)) {
        echo '<p style="color: green;">✓ Table <code>' . $table_settings . '</code> créée avec succès</p>';
    } else {
        echo '<p style="color: red;">✗ ERREUR lors de la création de <code>' . $table_settings . '</code></p>';
        echo '<p>Erreur: ' . $wpdb->last_error . '</p>';
    }
} else {
    echo '<p style="color: blue;">✓ Table <code>' . $table_settings . '</code> existe déjà</p>';
}

// 2. Créer la table wp_pdf_builder_templates
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

$table_exists = $wpdb->get_var($wpdb->prepare(
    "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
    DB_NAME,
    $table_templates
));

if (!$table_exists) {
    $sql = "CREATE TABLE $table_templates (
        template_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        template_name varchar(255) NOT NULL DEFAULT '',
        template_type varchar(100) NOT NULL DEFAULT 'custom',
        template_data longtext NOT NULL,
        template_preview longtext,
        template_created datetime DEFAULT CURRENT_TIMESTAMP,
        template_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_default tinyint(1) NOT NULL DEFAULT 0,
        is_public tinyint(1) NOT NULL DEFAULT 0,
        thumbnail_url varchar(500) DEFAULT '',
        PRIMARY KEY (template_id),
        KEY template_type (template_type),
        KEY is_default (is_default)
    ) $charset_collate;";
    
    if ($wpdb->query($sql)) {
        echo '<p style="color: green;">✓ Table <code>' . $table_templates . '</code> créée avec succès</p>';
    } else {
        echo '<p style="color: red;">✗ ERREUR lors de la création de <code>' . $table_templates . '</code></p>';
        echo '<p>Erreur: ' . $wpdb->last_error . '</p>';
    }
} else {
    echo '<p style="color: blue;">✓ Table <code>' . $table_templates . '</code> existe déjà</p>';
}

echo '<hr>';
echo '<p><a href="' . admin_url() . '">← Retour au tableau de bord</a></p>';
