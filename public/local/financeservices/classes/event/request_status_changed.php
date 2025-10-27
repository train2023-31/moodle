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
 * The request_status_changed event.
 *
 * @package    local_financeservices
 * @copyright  2025 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_financeservices\event;
defined('MOODLE_INTERNAL') || die();

/**
 * The request_status_changed event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int course_id: The course ID
 *      - string old_status: The previous status
 *      - string new_status: The new status
 *      - string notes: Additional notes about the status change
 * }
 *
 * @since     Moodle 4.0
 * @copyright 2025 Your Name <your@email.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class request_status_changed extends \core\event\base {
    
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u'; // u(pdate)
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_financeservices';
    }

    /**
     * Get name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventrequeststatuschanged', 'local_financeservices');
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function get_description() {
        global $DB;
        
        $coursename = $DB->get_field('course', 'fullname', array('id' => $this->other['course_id']));
        $statusmap = array(
            'w' => get_string('w', 'local_financeservices'),
            'a' => get_string('a', 'local_financeservices'),
            'r' => get_string('r', 'local_financeservices'),
            's' => get_string('s', 'local_financeservices')
        );
        
        $oldstatus = isset($statusmap[$this->other['old_status']]) ? $statusmap[$this->other['old_status']] : $this->other['old_status'];
        $newstatus = isset($statusmap[$this->other['new_status']]) ? $statusmap[$this->other['new_status']] : $this->other['new_status'];
        
        return "The user with id {$this->userid} changed the status of finance service request {$this->objectid} " .
               "from '{$oldstatus}' to '{$newstatus}'. " .
               "Course: {$coursename}" .
               (empty($this->other['notes']) ? '.' : ". Notes: {$this->other['notes']}");
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
        
        if (!isset($this->other['old_status'])) {
            throw new \coding_exception('The \'old_status\' value must be set in event.');
        }

        if (!isset($this->other['new_status'])) {
            throw new \coding_exception('The \'new_status\' value must be set in event.');
        }
    }
} 