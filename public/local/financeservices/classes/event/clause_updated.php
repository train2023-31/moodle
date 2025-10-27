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
 * The clause_updated event.
 *
 * @package    local_financeservices
 * @copyright  2025 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_financeservices\event;
defined('MOODLE_INTERNAL') || die();

/**
 * The clause_updated event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - string clause_name_en: The English name of the clause
 *      - string clause_name_ar: The Arabic name of the clause
 *      - float amount: The current amount associated with the clause
 *      - float initial_amount: The original amount when first created
 *      - int clause_year: Calendar year of the clause
 *      - int modified_by: User ID who modified the clause
 * }
 *
 * @since     Moodle 4.0
 * @copyright 2025 Your Name <your@email.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class clause_updated extends \core\event\base {
    
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_financeservices_clause';
    }

    /**
     * Get name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventclauseupdated', 'local_financeservices');
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function get_description() {
        $year = isset($this->other['clause_year']) ? $this->other['clause_year'] : 'N/A';
        return "The user with id {$this->userid} updated the finance clause with id {$this->objectid}. " .
               "English name: {$this->other['clause_name_en']}, Arabic name: {$this->other['clause_name_ar']}, " .
               "Amount: {$this->other['amount']}, Year: {$year}.";
    }

    /**
     * Get URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/local/financeservices/pages/edit_clause.php', 
                             array('id' => $this->objectid));
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['clause_name_en'])) {
            throw new \coding_exception('The \'clause_name_en\' value must be set in event.');
        }
        
        if (!isset($this->other['clause_name_ar'])) {
            throw new \coding_exception('The \'clause_name_ar\' value must be set in event.');
        }

        if (!isset($this->other['amount'])) {
            throw new \coding_exception('The \'amount\' value must be set in event.');
        }
        if (!isset($this->other['clause_year'])) {
            throw new \coding_exception('The \'clause_year\' value must be set in event.');
        }
    }
} 