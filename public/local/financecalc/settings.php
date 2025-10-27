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
 * Settings for the financecalc plugin.
 *
 * @package    local_financecalc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_financecalc', get_string('pluginname', 'local_financecalc'));
    $ADMIN->add('localplugins', $settings);

    // Add navigation link to the financial report.
    $ADMIN->add('reports', new admin_externalpage(
        'local_financecalc_report',
        get_string('financialreport', 'local_financecalc'),
        new moodle_url('/local/financecalc/pages/report.php'),
        'local/financecalc:view'
    ));
}
