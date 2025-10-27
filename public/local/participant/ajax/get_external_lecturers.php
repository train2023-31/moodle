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
 * AJAX script to get external lecturers data
 *
 * @package    local_participant
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');



// Ensure the user is logged in
require_login();

// Set up the context for AJAX calls
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/participant/ajax/get_external_lecturers.php'));

/**
 * Get external lecturers data from database
 */
function get_external_lecturers_data() {
    global $DB;
    
    $lecturers_data = [];

    try {
        // Check if table exists
        $table_exists = $DB->get_manager()->table_exists('externallecturer');
        
        if (!$table_exists) {
            return [];
        }
        
        // Query to get external lecturers details
        $sql = "
            SELECT id, name, organization, specialization, age, degree, passport, courses_count
            FROM {externallecturer}
            ORDER BY name ASC
        ";
        
        $lecturers = $DB->get_records_sql($sql);
        
        foreach ($lecturers as $lecturer) {
            // Skip records with empty or null names
            if (empty($lecturer->name) || $lecturer->name === null) {
                continue;
            }
            
            $display_text = $lecturer->name;
            if (!empty($lecturer->organization)) {
                $display_text .= ' (' . $lecturer->organization . ')';
            }
            if (!empty($lecturer->specialization)) {
                $display_text .= ' - ' . $lecturer->specialization;
            }
            
            $lecturers_data[] = [
                'id' => $lecturer->id,
                'name' => $lecturer->name,
                'organization' => $lecturer->organization ?? '',
                'specialization' => $lecturer->specialization ?? '',
                'age' => $lecturer->age ?? '',
                'degree' => $lecturer->degree ?? '',
                'passport' => $lecturer->passport ?? '',
                'courses_count' => $lecturer->courses_count ?? '',
                'display_text' => $display_text
            ];
        }

        return $lecturers_data;

    } catch (Exception $e) {
        return [];
    }
}

// Get external lecturers data and return as JSON
$lecturers_data = get_external_lecturers_data();





header('Content-Type: application/json');
echo json_encode($lecturers_data); 