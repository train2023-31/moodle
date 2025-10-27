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
 * Approved Courses Block
 *
 * @package    block_approved_courses
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/approved_courses/classes/approved_courses_repository.php');

class block_approved_courses extends block_base {

    /**
     * Initialize the block.
     */
    public function init() {
        $configtitle = get_config('block_approved_courses', 'defaulttitle');
        $this->title = !empty($configtitle) ? $configtitle : get_string('pluginname', 'block_approved_courses');
    }

    /**
     * Specialization method
     */
    public function specialization() {
        // This method is called when the block is first loaded
    }

    /**
     * Return the content of this block.
     *
     * @return stdClass the content
     */
    public function get_content() {
        global $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        // Check if we have the required tables
        if (!$this->has_required_tables()) {
            $this->content->text = '<div class="alert alert-info">' . get_string('missingtables', 'block_approved_courses') . '</div>';
            return $this->content;
        }

        try {
            // Get approved courses for current year
            $courses = block_approved_courses_repository::get_approved_courses_for_current_year();
            
            if (empty($courses)) {
                $this->content->text = get_string('nocoursesavailable', 'block_approved_courses');
                return $this->content;
            }

            // Apply configuration settings
            $maxcourses = get_config('block_approved_courses', 'maxcourses');
            if ($maxcourses > 0 && count($courses) > $maxcourses) {
                $courses = array_slice($courses, 0, $maxcourses);
            }

            $currentyear = (int)date('Y');
            
            // Prepare data for template
            $template_data = [
                'courses' => array_values($courses),
                'hascourses' => !empty($courses),
                'course_count' => count($courses),
                'plan_year' => $currentyear,
                'course_count_more_than_10' => count($courses) > 10,
                'show_codes' => get_config('block_approved_courses', 'showcodes'),
                'show_plans' => get_config('block_approved_courses', 'showplans')
            ];

            // Render using template
            $this->content->text = $OUTPUT->render_from_template('block_approved_courses/course_list', $template_data);

        } catch (Exception $e) {
            debugging('Error in approved courses block: ' . $e->getMessage(), DEBUG_DEVELOPER);
            $this->content->text = get_string('errorloadingcourses', 'block_approved_courses');
        }

        return $this->content;
    }

    /**
     * Check if the required database tables exist.
     *
     * @return bool
     */
    private function has_required_tables() {
        global $DB;
        
        $tables = [
            'local_annual_plan',
            'local_annual_plan_course'
        ];
        
        foreach ($tables as $table) {
            if (!$DB->get_manager()->table_exists($table)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Define if the block has configuration.
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * Allow the block to have a configuration page.
     *
     * @return bool
     */
    public function instance_allow_config() {
        return true;
    }

    /**
     * Define the format types where this block can be used.
     *
     * @return array
     */
    public function applicable_formats() {
        return [
            'site' => true,
            'course' => true,
            'course-view' => true,
            'my' => true,
            'site-index' => true  // Allow on home page
        ];
    }

    /**
     * Define if the block can be docked.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return false;
    }
}
