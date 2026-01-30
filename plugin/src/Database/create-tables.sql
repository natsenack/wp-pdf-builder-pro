-- Création des tables PDF Builder Pro
-- À exécuter si les tables n'existent pas

-- Table wp_pdf_builder_settings
CREATE TABLE IF NOT EXISTS `wp_pdf_builder_settings` (
    `option_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `option_name` varchar(191) NOT NULL DEFAULT '',
    `option_value` longtext NOT NULL,
    `autoload` varchar(20) NOT NULL DEFAULT 'yes',
    PRIMARY KEY (`option_id`),
    UNIQUE KEY `option_name` (`option_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table wp_pdf_builder_templates
CREATE TABLE IF NOT EXISTS `wp_pdf_builder_templates` (
    `template_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `template_name` varchar(255) NOT NULL DEFAULT '',
    `template_type` varchar(100) NOT NULL DEFAULT 'custom',
    `template_data` longtext NOT NULL,
    `template_preview` longtext,
    `template_created` datetime DEFAULT CURRENT_TIMESTAMP,
    `template_modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `is_default` tinyint(1) NOT NULL DEFAULT 0,
    `is_public` tinyint(1) NOT NULL DEFAULT 0,
    `thumbnail_url` varchar(500) DEFAULT '',
    PRIMARY KEY (`template_id`),
    KEY `template_type` (`template_type`),
    KEY `is_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
