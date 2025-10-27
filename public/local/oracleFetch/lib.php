<?php
/**
 * Library functions for Oracle Fetch plugin
 * 
 * @package    local_oracleFetch
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/classes/oracle_manager.php');

/**
 * Require Select2 assets (local, offline-safe) and jQuery.
 */
function oracle_select2_require_assets() {
    global $PAGE;
    // Load jQuery from local Moodle installation
    $PAGE->requires->jquery();
    // Load Select2 CSS/JS from this plugin (no CDN)
    $PAGE->requires->css(new moodle_url('/local/oracleFetch/lib/select2.min.css'));
    $PAGE->requires->js(new moodle_url('/local/oracleFetch/lib/select2.full.min.js'));
}

/**
 * Initialize Select2 with Arabic defaults and robust matcher.
 * This will merge provided options into sensible defaults.
 *
 * @param string $selector jQuery selector for the element(s)
 * @param string $placeholder Placeholder text (Arabic recommended)
 * @param array $extraoptions Additional Select2 options to merge
 */
function oracle_select2_init($selector, $placeholder = "-- الرجاء الاختيار --", array $extraoptions = []) {
    $selector = (string)$selector;
    $placeholder = (string)$placeholder;
    $jsonExtra = json_encode($extraoptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($jsonExtra === false) {
        $jsonExtra = '{}';
    }
    echo "\n<script>\n(function(){\n  if (typeof jQuery === 'undefined') { return; }\n  jQuery(function($){\n    var defaultOptions = {\n      placeholder: " . json_encode($placeholder, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ",\n      allowClear: true,\n      language: {\n        noResults: function(){ return 'لا توجد نتائج مطابقة'; },\n        searching: function(){ return 'جاري البحث…'; },\n        errorLoading: function(){ return 'حدث خطأ أثناء جلب النتائج'; },\n        inputTooShort: function(){ return 'أدخل مزيدًا من الأحرف'; }\n      },\n      matcher: function(params, data) {\n        if ($.trim(params.term || '') === '') { return data; }\n        if (typeof data.element === 'undefined') { return null; }\n        var searchableText = $(data.element).data('text') || data.text || '';\n        var hay = searchableText.toString().toLowerCase();\n        var needle = (params.term || '').toLowerCase();\n        if (hay.indexOf(needle) > -1) { return data; }\n        return null;\n      }\n    };\n    var extra = " . $jsonExtra . ";\n    try { if (typeof extra !== 'object' || extra === null) extra = {}; } catch(e) { extra = {}; }\n    var opts = $.extend(true, {}, defaultOptions, extra);\n    $('" . addslashes($selector) . "').select2(opts);\n  });\n})();\n</script>\n";
}

/**
 * Get employee name by PF number - Helper function for other plugins
 * @param string $pf_number Employee PF number
 * @return string Employee full name or fallback
 */
function oracle_get_employee_name($pf_number) {
    global $DB;
    
    if (empty($pf_number)) {
        return 'N/A';
    }
    
    // First, try to get the name from Moodle user table
    $pf_number_formatted = $pf_number;
    if (is_numeric($pf_number)) {
        $pf_number_formatted = 'PF' . $pf_number;
    }
    
    $moodle_user = $DB->get_record('user', ['username' => $pf_number_formatted, 'deleted' => 0], 'firstname, lastname');
    if ($moodle_user) {
        $fullname = trim($moodle_user->firstname . ' ' . $moodle_user->lastname);
        if (!empty($fullname)) {
            return $fullname;
        }
    }
    
    // Also try with the original format
    if ($pf_number_formatted !== $pf_number) {
        $moodle_user = $DB->get_record('user', ['username' => $pf_number, 'deleted' => 0], 'firstname, lastname');
        if ($moodle_user) {
            $fullname = trim($moodle_user->firstname . ' ' . $moodle_user->lastname);
            if (!empty($fullname)) {
                return $fullname;
            }
        }
    }
    
    // If not found in Moodle, try Oracle database
    $employee = oracle_manager::get_employee_by_pf($pf_number);
    if ($employee) {
        $fullname = trim($employee['PRS_NAME1_A'] . ' ' . $employee['PRS_NAME2_A'] . ' ' . $employee['PRS_NAME3_A']);
        $tribe = $employee['PRS_TRIBE_A'] ? ' ' . $employee['PRS_TRIBE_A'] : '';
        return $fullname . $tribe;
    }
    
    // Final fallback
    return 'PF: ' . $pf_number;
}

/**
 * Get person name by civil number - Helper function for other plugins
 * @param string $civil_number Civil number
 * @param bool $include_nationality Whether to include nationality in the returned string
 * @return string Person full name or fallback
 */
function oracle_get_person_name($civil_number, $include_nationality = false) {
    if (empty($civil_number)) {
        return 'N/A';
    }
    
    $person = oracle_manager::get_person_by_civil($civil_number);
    if ($person) {
        $fullname = trim($person['NAME_ARABIC_1'] . ' ' . $person['NAME_ARABIC_2'] . ' ' . $person['NAME_ARABIC_3']);
        $tribe = $person['NAME_ARABIC_6'] ? ' ' . $person['NAME_ARABIC_6'] : '';
        $nationality = '';
        
        if ($include_nationality && !empty($person['NATIONALITY_ARABIC_SYS'])) {
            $nationality = ' (' . $person['NATIONALITY_ARABIC_SYS'] . ')';
        }
        
        return $fullname . $tribe . $nationality;
    }
    
    return 'Civil: ' . $civil_number;
}

/**
 * Get person nationality by civil number - Helper function for other plugins
 * @param string $civil_number Civil number
 * @return string Person nationality or fallback
 */
function oracle_get_person_nationality($civil_number) {
    if (empty($civil_number)) {
        return 'غير محدد';
    }
    
    $person = oracle_manager::get_person_by_civil($civil_number);
    if ($person && !empty($person['NATIONALITY_ARABIC_SYS'])) {
        return $person['NATIONALITY_ARABIC_SYS'];
    }
    
    return 'غير محدد';
}

/**
 * Get employee or person data by identifier - Helper function for other plugins
 * @param string $identifier PF number or civil number
 * @return array|false Employee/person data with source type or false on failure
 */
function oracle_get_person_data($identifier) {
    return oracle_manager::get_person_data($identifier);
}