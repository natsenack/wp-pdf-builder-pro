-- Commandes SQL pour modifier la table wp_pdf_builder_settings
-- pour correspondre à la structure de wp_options

-- 1. Renommer la colonne setting_key en option_name
ALTER TABLE `wp_pdf_builder_settings` CHANGE `setting_key` `option_name` VARCHAR(191) NOT NULL;

-- 2. Renommer la colonne setting_value en option_value
ALTER TABLE `wp_pdf_builder_settings` CHANGE `setting_value` `option_value` LONGTEXT NOT NULL;

-- 3. Ajouter la colonne autoload (comme dans wp_options)
ALTER TABLE `wp_pdf_builder_settings` ADD `autoload` VARCHAR(20) NOT NULL DEFAULT 'yes' AFTER `option_value`;

-- 4. Supprimer les colonnes qui ne sont pas dans wp_options
ALTER TABLE `wp_pdf_builder_settings` DROP COLUMN `setting_type`;
ALTER TABLE `wp_pdf_builder_settings` DROP COLUMN `created_at`;
ALTER TABLE `wp_pdf_builder_settings` DROP COLUMN `updated_at`;

-- 5. Renommer la colonne id en option_id et changer le type
ALTER TABLE `wp_pdf_builder_settings` CHANGE `id` `option_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;

-- 6. Recréer les index pour correspondre à wp_options
ALTER TABLE `wp_pdf_builder_settings` DROP INDEX `setting_key`;
ALTER TABLE `wp_pdf_builder_settings` DROP INDEX `setting_type`;
ALTER TABLE `wp_pdf_builder_settings` DROP INDEX `updated_at`;
ALTER TABLE `wp_pdf_builder_settings` ADD UNIQUE KEY `option_name` (`option_name`);
ALTER TABLE `wp_pdf_builder_settings` ADD KEY `autoload` (`autoload`);