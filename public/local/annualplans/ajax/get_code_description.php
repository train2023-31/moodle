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
 * AJAX script to get code description by ID
 *
 * @package    local_annualplans
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');

// Ensure the user is logged in
require_login();

// Check if the ID parameter is set
$id = required_param('id', PARAM_INT);

// Get code description from database
global $DB;
$description = '';

if ($code = $DB->get_record('local_annual_plan_course_codes', ['id' => $id])) {
    if (current_language() === 'ar' && !empty($code->description_ar)) {
        $description = $code->description_ar;
    } else {
        $description = $code->description_en;
    }
}

// Output the code description
echo $description; 