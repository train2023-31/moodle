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
 * Filter form for the financecalc plugin.
 *
 * @package    local_financecalc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_financecalc\forms;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Filter form class.
 *
 * @package    local_financecalc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;
        
        // Set the form action
        if (!empty($this->_customdata)) {
            $mform->setAction($this->_customdata);
        }

        // Add custom CSS class to the form
        $mform->addElement('html', '<div class="financecalc-filter-form">');

        // Year filter.
        $years = $this->get_available_years();
        $yearoptions = array(0 => get_string('filter_all_years', 'local_financecalc'));
        foreach ($years as $year) {
            $yearoptions[$year] = $year;
        }

        $mform->addElement('html', '<div class="financecalc-filter-group">');
        $mform->addElement('select', 'year', get_string('filter_year', 'local_financecalc'), $yearoptions);
        $mform->setType('year', PARAM_INT);
        $mform->setDefault('year', 0);
        $mform->addElement('html', '</div>');
        
        // Add a hidden field to preserve the form action
        $mform->addElement('hidden', 'action', 'filter');
        $mform->setType('action', PARAM_ALPHA);

        // Buttons.
        $mform->addElement('submit', 'submitbutton', get_string('filter', 'local_financecalc'), array('class' => 'financecalc-filter-btn'));
        
        $mform->addElement('html', '</div>');
    }

    /**
     * Get available years for filtering.
     *
     * @return array Array of years
     */
    private function get_available_years() {
        global $DB;

        $sql = "SELECT DISTINCT year FROM (
                    SELECT c.clause_year AS year
                    FROM {local_financeservices_clause} c
                    WHERE c.deleted = 0
                    UNION
                    SELECT ap.year
                    FROM {local_participant_requests} r
                    JOIN {local_annual_plan} ap ON ap.id = r.annual_plan_id
                    WHERE r.is_approved = 1
                ) years
                ORDER BY year DESC";

        $years = $DB->get_records_sql($sql);
        return array_keys($years);
    }
}
