<?php
$content = file_get_contents('plugin/src/Security/Role_Manager.php');

// Rename class to PascalCase
$content = str_replace('class Role_Manager', 'class RoleManager', $content);

// Convert method names from snake_case to camelCase
$content = str_replace('register_capabilities', 'registerCapabilities', $content);
$content = str_replace('check_pdf_builder_capability', 'checkPdfBuilderCapability', $content);
$content = str_replace('user_can_access_pdf_builder', 'userCanAccessPdfBuilder', $content);
$content = str_replace('get_allowed_roles', 'getAllowedRoles', $content);
$content = str_replace('set_allowed_roles', 'setAllowedRoles', $content);
$content = str_replace('user_has_access', 'userHasAccess', $content);
$content = str_replace('check_and_block_access', 'checkAndBlockAccess', $content);
$content = str_replace('get_required_capability', 'getRequiredCapability', $content);
$content = str_replace('get_role_info', 'getRoleInfo', $content);

file_put_contents('plugin/src/Security/Role_Manager.php', $content);
echo "Fixed Role_Manager.php\n";