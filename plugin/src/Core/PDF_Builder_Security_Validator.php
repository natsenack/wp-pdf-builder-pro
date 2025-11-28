<?php
namespace PDF_Builder\Core;
class PDF_Builder_Security_Validator {
    public static function get_instance() { static $i; return $i ?: $i = new self(); }
    public function init() {}
    public static function sanitizeHtmlContent($c) { return $c; }
    public static function validateJsonData($j) { return json_decode($j, true) ?: false; }
    public static function validateNonce() { return true; }
    public static function checkPermissions() { return true; }
    public function validate_ajax_request() { return true; }
    public function sanitize_template_data($d) { return $d; }
    public function sanitize_settings($s) { return $s; }
}
function pdf_builder_validate_ajax_request() { return true; }
function pdf_builder_sanitize_template_data($d) { return $d; }
function pdf_builder_sanitize_settings($s) { return $s; }