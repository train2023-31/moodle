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
 * Scheduled task for refreshing financial data.
 *
 * @package    local_financecalc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_financecalc\tasks;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/financecalc/classes/data_manager.php');

/**
 * Scheduled task class.
 *
 * @package    local_financecalc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class refresh_financial_data extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_refresh_financial_data', 'local_financecalc');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG;

        // Check if the required plugins exist.
        if (!file_exists($CFG->dirroot . '/local/financeservices')) {
            mtrace('Finance Services plugin not found, skipping financial data refresh.');
            return;
        }

        if (!file_exists($CFG->dirroot . '/local/participant')) {
            mtrace('Participant plugin not found, skipping financial data refresh.');
            return;
        }

        mtrace('Starting financial data refresh...');

        try {
            $success = \local_financecalc\data_manager::refresh_cached_data();
            
            if ($success) {
                mtrace('Financial data refresh completed successfully.');
            } else {
                mtrace('Financial data refresh failed.');
            }
            
        } catch (Exception $e) {
            mtrace('Error during financial data refresh: ' . $e->getMessage());
            throw $e;
        }
    }
}
