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

namespace test_plugin\hook;

/**
 * Fixture for testing of hooks.
 *
 * @package   local_openlms
 * @author    Petr Skoda
 * @copyright 2022 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class stoppablehook extends \local_openlms\hook\base implements \local_openlms\hook\stoppable {
    /** @var bool stoppable flag */
    private $stopped = false;

    /**
     * Hook description.
     */
    public static function get_hook_description(): string {
        return 'Test hook 2.';
    }

    /**
     * Stop other callbacks.
     */
    public function stop(): void {
        $this->stopped = true;
    }

    /**
     * Indicates if callback propagation should stop.
     */
    public function is_propagation_stopped(): bool {
        return $this->stopped;
    }
}
