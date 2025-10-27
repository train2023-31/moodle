<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Post installation hook for local_reports plugin
 *
 * @package    local_reports
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Post installation hook to populate default report types
 */
function xmldb_local_reports_install() {
    global $DB;
    
    // Default report types with English and Arabic translations
    $default_types = [
        [
            'report_type_string_en' => 'Research Report',
            'report_type_string_ar' => 'تقرير بحثي'
        ],
        [
            'report_type_string_en' => 'Project Report',
            'report_type_string_ar' => 'تقرير مشروع'
        ],
        [
            'report_type_string_en' => 'Internship Report',
            'report_type_string_ar' => 'تقرير تدريب'
        ],
        [
            'report_type_string_en' => 'Field Work Report',
            'report_type_string_ar' => 'تقرير عمل ميداني'
        ],
        [
            'report_type_string_en' => 'Analysis Report',
            'report_type_string_ar' => 'تقرير تحليل'
        ],
        [
            'report_type_string_en' => 'Evaluation Report',
            'report_type_string_ar' => 'تقرير تقييم'
        ]
    ];
    
    // Insert default report types
    foreach ($default_types as $type) {
        $record = new stdClass();
        $record->report_type_string_en = $type['report_type_string_en'];
        $record->report_type_string_ar = $type['report_type_string_ar'];
        
        $DB->insert_record('local_report_type', $record);
    }
    
    return true;
}
