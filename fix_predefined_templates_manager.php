<?php
$content = file_get_contents('plugin/templates/admin/predefined-templates-manager.php');

// Rename class to PascalCase
$content = str_replace('class PDF_Builder_Predefined_Templates_Manager', 'class PdfBuilderPredefinedTemplatesManager', $content);

// Convert method names from snake_case to camelCase
$content = str_replace('add_admin_menu', 'addAdminMenu', $content);
$content = str_replace('enqueue_admin_scripts', 'enqueueAdminScripts', $content);
$content = str_replace('register_developer_settings', 'registerDeveloperSettings', $content);
$content = str_replace('developer_settings_section_callback', 'developerSettingsSectionCallback', $content);
$content = str_replace('developer_enabled_field_callback', 'developerEnabledFieldCallback', $content);
$content = str_replace('developer_password_field_callback', 'developerPasswordFieldCallback', $content);
$content = str_replace('is_developer_authenticated', 'isDeveloperAuthenticated', $content);
$content = str_replace('ajax_developer_auth', 'ajaxDeveloperAuth', $content);
$content = str_replace('ajax_developer_logout', 'ajaxDeveloperLogout', $content);
$content = str_replace('render_admin_page', 'renderAdminPage', $content);
$content = str_replace('render_developer_login_form', 'renderDeveloperLoginForm', $content);
$content = str_replace('get_predefined_templates', 'getPredefinedTemplates', $content);
$content = str_replace('load_template_from_file', 'loadTemplateFromFile', $content);
$content = str_replace('ajax_save_predefined_template', 'ajaxSavePredefinedTemplate', $content);
$content = str_replace('ajax_load_predefined_template', 'ajaxLoadPredefinedTemplate', $content);
$content = str_replace('ajax_delete_predefined_template', 'ajaxDeletePredefinedTemplate', $content);
$content = str_replace('ajax_refresh_nonce', 'ajaxRefreshNonce', $content);
$content = str_replace('ajax_generate_template_preview', 'ajaxGenerateTemplatePreview', $content);
$content = str_replace('generate_svg_preview', 'generateSvgPreview', $content);
$content = str_replace('get_element_preview_style', 'getElementPreviewStyle', $content);
$content = str_replace('clean_template_json_for_predefined', 'cleanTemplateJsonForPredefined', $content);

file_put_contents('plugin/templates/admin/predefined-templates-manager.php', $content);
echo "Fixed predefined-templates-manager.php\n";