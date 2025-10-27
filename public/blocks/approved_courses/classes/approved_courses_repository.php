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
 * Approved Courses Repository
 *
 * @package    block_approved_courses
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_approved_courses_repository {

    /**
     * Get approved courses for the current year with course codes.
     *
     * @return array Array of course objects with formatted display names
     */
    public static function get_approved_courses_for_current_year() {
        global $DB;
        
        $currentyear = (int)date('Y');
        
        try {
            // Get approved annual plan courses for current year with course codes
            $sql = "SELECT DISTINCT c.id, c.fullname, c.shortname, c.summary, cap.courseid as course_code,
                           ap.title as annual_plan_title, ap.year as plan_year
                    FROM {course} c
                    JOIN {local_annual_plan_course} cap ON c.fullname = cap.coursename
                    JOIN {local_annual_plan} ap ON cap.annualplanid = ap.id
                    WHERE cap.approve = 1 
                    AND cap.disabled = 0
                    AND ap.year = :currentyear
                    AND c.visible = 1
                    AND c.id != 1
                    ORDER BY c.fullname ASC";
            
            $courses = $DB->get_records_sql($sql, ['currentyear' => $currentyear]);
        } catch (Exception $e) {
            debugging('Error fetching approved courses: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [];
        }
        
        // Format the results
        $formatted_courses = [];
        foreach ($courses as $course) {
            $display_name = $course->fullname;
            $course_code = '';
            
            if (!empty($course->course_code)) {
                $course_code = $course->course_code;
                $display_name = $course->fullname . "   -   " . $course->course_code;
            }
            
            $formatted_courses[] = [
                'id' => $course->id,
                'fullname' => $course->fullname,
                'shortname' => $course->shortname,
                'course_code' => $course_code,
                'display_name' => $display_name,
                'summary' => $course->summary,
                'annual_plan_title' => $course->annual_plan_title,
                'plan_year' => $course->plan_year,
                'course_url' => new \moodle_url('/course/view.php', ['id' => $course->id])
            ];
        }
        
        return $formatted_courses;
    }

    /**
     * Get approved courses count for current year.
     *
     * @return int Number of approved courses
     */
    public static function get_approved_courses_count() {
        global $DB;
        
        $currentyear = (int)date('Y');
        
        $sql = "SELECT COUNT(DISTINCT c.id) as course_count
                FROM {course} c
                JOIN {local_annual_plan_course} cap ON c.fullname = cap.coursename
                JOIN {local_annual_plan} ap ON cap.annualplanid = ap.id
                WHERE cap.approve = 1 
                AND cap.disabled = 0
                AND ap.year = :currentyear
                AND c.visible = 1
                AND c.id != 1";
        
        $result = $DB->get_record_sql($sql, ['currentyear' => $currentyear]);
        
        return $result ? $result->course_count : 0;
    }

    /**
     * Get approved courses grouped by annual plan.
     *
     * @return array Array of annual plans with their courses
     */
    public static function get_approved_courses_grouped_by_plan() {
        global $DB;
        
        $currentyear = (int)date('Y');
        
        $sql = "SELECT DISTINCT ap.id as plan_id, ap.title as plan_title, ap.year as plan_year,
                       c.id as course_id, c.fullname, c.shortname, cap.courseid as course_code
                FROM {local_annual_plan} ap
                JOIN {local_annual_plan_course} cap ON ap.id = cap.annualplanid
                JOIN {course} c ON c.fullname = cap.coursename
                WHERE cap.approve = 1 
                AND cap.disabled = 0
                AND ap.year = :currentyear
                AND c.visible = 1
                AND c.id != 1
                ORDER BY ap.title ASC, c.fullname ASC";
        
        $results = $DB->get_records_sql($sql, ['currentyear' => $currentyear]);
        
        $grouped = [];
        foreach ($results as $result) {
            if (!isset($grouped[$result->plan_id])) {
                $grouped[$result->plan_id] = [
                    'plan_id' => $result->plan_id,
                    'plan_title' => $result->plan_title,
                    'plan_year' => $result->plan_year,
                    'courses' => []
                ];
            }
            
            $display_name = $result->fullname;
            if (!empty($result->course_code)) {
                $display_name = $result->fullname . "   -   " . $result->course_code;
            }
            
            $grouped[$result->plan_id]['courses'][] = [
                'id' => $result->course_id,
                'fullname' => $result->fullname,
                'shortname' => $result->shortname,
                'course_code' => $result->course_code,
                'display_name' => $display_name,
                'course_url' => new \moodle_url('/course/view.php', ['id' => $result->course_id])
            ];
        }
        
        return array_values($grouped);
    }
}
