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
 * Main index page for the financecalc plugin.
 *
 * @package    local_financecalc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

// Check for valid login and capability.
require_login();
$context = context_system::instance();
require_capability('local/financecalc:view', $context);

// Set up the page.
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/financecalc/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_financecalc'));
$PAGE->set_heading(get_string('pluginname', 'local_financecalc'));
$PAGE->set_pagelayout('standard');

// Output starts here.
echo $OUTPUT->header();

// Add custom CSS
echo html_writer::tag('link', '', array(
    'rel' => 'stylesheet',
    'type' => 'text/css',
    'href' => new moodle_url('/local/shared_styles.css')
));

// Main container
echo html_writer::start_div('financecalc-index-container');

// Page Header
echo html_writer::start_div('financecalc-page-header');
echo html_writer::tag('h1', get_string('welcome_to_finance_calculator', 'local_financecalc'));
echo html_writer::tag('p', get_string('choose_report_type', 'local_financecalc'));
echo html_writer::end_div();

// Cards Container
echo html_writer::start_div('financecalc-cards-container');

// Financial Overview Card
$financial_report_url = new moodle_url('/local/financecalc/pages/report.php');
echo html_writer::start_div('financecalc-card');
echo html_writer::tag('div', html_writer::tag('i', '', array('class' => 'fa fa-chart-line')), array('class' => 'financecalc-card-icon'));
echo html_writer::tag('h3', get_string('financialoverview', 'local_financecalc'));
echo html_writer::tag('p', get_string('financial_overview_description', 'local_financecalc'));
echo html_writer::link($financial_report_url, get_string('financialoverview', 'local_financecalc'), array('class' => 'financecalc-card-btn'));
echo html_writer::end_div();

// Clause Spending Report Card
$clause_report_url = new moodle_url('/local/financecalc/pages/clause_report.php');
echo html_writer::start_div('financecalc-card');
echo html_writer::tag('div', html_writer::tag('i', '', array('class' => 'fa fa-list-alt')), array('class' => 'financecalc-card-icon'));
echo html_writer::tag('h3', get_string('clause_spending_report', 'local_financecalc'));
echo html_writer::tag('p', get_string('clause_spending_description', 'local_financecalc'));
echo html_writer::link($clause_report_url, get_string('clause_spending_report', 'local_financecalc'), array('class' => 'financecalc-card-btn secondary'));
echo html_writer::end_div();

echo html_writer::end_div();

// About section
echo html_writer::start_div('financecalc-about-section');
echo html_writer::tag('h3', get_string('about_finance_calculator', 'local_financecalc'));
echo html_writer::tag('p', get_string('about_finance_calculator_desc', 'local_financecalc'));
echo html_writer::end_div();

echo html_writer::end_div();

echo $OUTPUT->footer();
