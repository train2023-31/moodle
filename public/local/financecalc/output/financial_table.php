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
 * Financial table output class for the financecalc plugin.
 *
 * @package    local_financecalc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_financecalc\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

/**
 * Financial table class.
 *
 * @package    local_financecalc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class financial_table extends \table_sql {

    /**
     * Constructor.
     *
     * @param string $uniqueid Unique ID for the table
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        
        // Set up the table.
        $this->define_baseurl(new \moodle_url('/local/financecalc/pages/report.php'));
        $this->set_attribute('class', 'generaltable generalbox');
        
        // Set up columns.
        $this->define_columns(array('year', 'spending', 'budget', 'balance'));
        $this->define_headers(array(
            get_string('year', 'local_financecalc'),
            get_string('spending', 'local_financecalc'),
            get_string('budget', 'local_financecalc'),
            get_string('balance', 'local_financecalc')
        ));
        
        // Set up sorting.
        $this->sortable(true, 'year', SORT_DESC);
        $this->no_sorting('spending');
        $this->no_sorting('budget');
        $this->no_sorting('balance');
        
        // Set up pagination.
        $this->pageable(false);
    }

    /**
     * Format the year column.
     *
     * @param object $row Row data
     * @return string Formatted year
     */
    public function col_year($row) {
        return $row->year;
    }

    /**
     * Format the spending column.
     *
     * @param object $row Row data
     * @return string Formatted spending amount
     */
    public function col_spending($row) {
        return number_format($row->spending_omr, 2) . ' OMR';
    }

    /**
     * Format the budget column.
     *
     * @param object $row Row data
     * @return string Formatted budget amount
     */
    public function col_budget($row) {
        return number_format($row->budget_omr, 2) . ' OMR';
    }

    /**
     * Format the balance column.
     *
     * @param object $row Row data
     * @return string Formatted balance amount with color coding
     */
    public function col_balance($row) {
        $balance = $row->budget_omr - $row->spending_omr;
        $balanceclass = $balance >= 0 ? 'text-success' : 'text-danger';
        
        return \html_writer::span(
            number_format($balance, 2) . ' OMR',
            $balanceclass
        );
    }
}
