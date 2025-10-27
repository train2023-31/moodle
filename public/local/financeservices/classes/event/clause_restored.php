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
 * The clause_restored event.
 *
 * @package    local_financeservices
 * @copyright  2025 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_financeservices\event;
defined('MOODLE_INTERNAL') || die();

/**
 * The clause_restored event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - string clause_name_en: The English name of the clause
 *      - string clause_name_ar: The Arabic name of the clause
 * }
 *
 * @since     Moodle 4.0
 * @copyright 2025 Your Name <your@email.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class clause_restored extends \core\event\base {
    
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u'; // Using update since we're modifying the deleted flag
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_financeservices_clause';
    }

    /**
     * Get name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventclauserestored', 'local_financeservices');
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id {$this->userid} restored the finance clause with id {$this->objectid}. " .
               "English name: {$this->other['clause_name_en']}, Arabic name: {$this->other['clause_name_ar']}.";
    }

    /**
     * Get URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/local/financeservices/pages/manage_clauses.php', 
                             array('action' => 'manage', 'showhidden' => 1));
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
    }
} 