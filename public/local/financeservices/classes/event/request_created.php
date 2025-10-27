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
 * The request_created event.
 *
 * @package    local_financeservices
 * @copyright  2025 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_financeservices\event;
defined('MOODLE_INTERNAL') || die();

/**
 * The request_created event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int course_id: The course ID
 *      - int funding_type_id: The funding type ID
 *      - float price_requested: The requested price
 *      - string notes: Additional notes
 *      - int clause_id: The clause ID (if applicable)
 * }
 *
 * @since     Moodle 4.0
 * @copyright 2025 Your Name <your@email.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class request_created extends \core\event\base {
    
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c'; // c(reate)
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_financeservices';
    }

    /**
     * Get name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventrequestcreated', 'local_financeservices');
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function get_description() {
        global $DB;
        
        $coursename = $DB->get_field('course', 'fullname', array('id' => $this->other['course_id']));
        $fundingtype = $DB->get_field('local_financeservices_funding_type', 'funding_type_en', array('id' => $this->other['funding_type_id']));
        
        return "The user with id {$this->userid} created a new finance service request with id {$this->objectid}. " .
               "Course: {$coursename}, Funding Type: {$fundingtype}, " .
               "Price Requested: {$this->other['price_requested']}.";
    }

    /**
     * Get URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/local/financeservices/index.php', 
                             array('tab' => 'list'));
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['course_id'])) {
            throw new \coding_exception('The \'course_id\' value must be set in event.');
        }
        
        if (!isset($this->other['funding_type_id'])) {
            throw new \coding_exception('The \'funding_type_id\' value must be set in event.');
        }

        if (!isset($this->other['price_requested'])) {
            throw new \coding_exception('The \'price_requested\' value must be set in event.');
        }
    }
} 