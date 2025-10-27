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
 * Financial report page for the financecalc plugin.
 *
 * @package    local_financecalc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Check for valid login and capability.
require_login();
$context = context_system::instance();
require_capability('local/financecalc:view', $context);

// Set up the page.
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/financecalc/pages/report.php'));
$PAGE->set_title(get_string('financialreport', 'local_financecalc'));
$PAGE->set_heading(get_string('financialoverview', 'local_financecalc'));
$PAGE->set_pagelayout('standard');

// Handle refresh action.
$refresh = optional_param('refresh', 0, PARAM_INT);
if ($refresh && has_capability('local/financecalc:manage', $context)) {
    require_sesskey();
    
    try {
        require_once($CFG->dirroot . '/local/financecalc/classes/data_manager.php');
        $success = \local_financecalc\data_manager::refresh_cached_data();
        if ($success) {
            redirect($PAGE->url, get_string('refresh_data_success', 'local_financecalc'), null, \core\output\notification::NOTIFY_SUCCESS);
        } else {
            redirect($PAGE->url, get_string('refresh_data_live', 'local_financecalc'), null, \core\output\notification::NOTIFY_SUCCESS);
        }
    } catch (Exception $e) {
        // Log the error for debugging
        debugging('Finance calc refresh error: ' . $e->getMessage(), DEBUG_DEVELOPER);
        redirect($PAGE->url, get_string('refresh_data_error', 'local_financecalc') . ' - ' . $e->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
    }
}

// Get filter parameters.
$yearfilter = optional_param('year', 0, PARAM_INT);

// Output starts here.
echo $OUTPUT->header();

// Add custom CSS
echo html_writer::tag('link', '', array(
    'rel' => 'stylesheet',
    'type' => 'text/css',
    'href' => new moodle_url('/local/financecalc/styles.css')
));

// Page Header
echo html_writer::start_div('financecalc-page-header');
echo html_writer::tag('h1', get_string('financialoverview', 'local_financecalc'));
echo html_writer::tag('p', get_string('comprehensive_financial_analysis', 'local_financecalc'));
echo html_writer::end_div();

// Navigation and Filter Container
echo html_writer::start_div('financecalc-nav-container');
echo html_writer::tag('h3', get_string('navigation_filters', 'local_financecalc'));

// Navigation Buttons
echo html_writer::start_div('financecalc-nav-buttons');
$clause_report_url = new moodle_url('/local/financecalc/pages/clause_report.php');
echo html_writer::link($clause_report_url, 
    html_writer::tag('i', '', array('class' => 'fa fa-list-alt')) . ' ' . get_string('clause_spending_report', 'local_financecalc'), 
    array('class' => 'financecalc-nav-btn'));

$index_url = new moodle_url('/local/financecalc/index.php');
echo html_writer::link($index_url, 
    html_writer::tag('i', '', array('class' => 'fa fa-home')) . ' ' . get_string('dashboard', 'local_financecalc'), 
    array('class' => 'financecalc-nav-btn'));
echo html_writer::end_div();

// Filter Container
echo html_writer::start_div('financecalc-filter-container');
echo html_writer::tag('h4', get_string('filter_options', 'local_financecalc'), array('style' => 'color: #495057; margin: 0 0 15px 0; font-size: 1.1em;'));

// Create filter form.
require_once($CFG->dirroot . '/local/financecalc/forms/filter_form.php');
$filterform = new \local_financecalc\forms\filter_form($PAGE->url);
$filterform->set_data(array('year' => $yearfilter));

if ($filterform->is_cancelled()) {
    redirect($PAGE->url);
}

// Process form data
if ($filterform->is_submitted() && $filterform->is_validated()) {
    $formdata = $filterform->get_data();
    if ($formdata && isset($formdata->year)) {
        $yearfilter = $formdata->year;
    }
}

echo $filterform->render();
echo html_writer::end_div();

// Action Buttons
echo html_writer::start_div('financecalc-actions');
if (has_capability('local/financecalc:manage', $context)) {
    $refreshurl = new moodle_url($PAGE->url, array('refresh' => 1, 'sesskey' => sesskey()));
    echo html_writer::link($refreshurl, 
        html_writer::tag('i', '', array('class' => 'fa fa-refresh')) . ' ' . get_string('refresh_data', 'local_financecalc'), 
        array('class' => 'financecalc-action-btn refresh'));
}
echo html_writer::end_div();

echo html_writer::end_div();

// Get and display the financial data.
require_once($CFG->dirroot . '/local/financecalc/classes/data_manager.php');
try {
    $data = \local_financecalc\data_manager::get_financial_data($yearfilter);
    
    if (empty($data)) {
        echo $OUTPUT->notification(get_string('no_data_available', 'local_financecalc'), 'info');
    } else {
        // Create and display the table.
        require_once($CFG->dirroot . '/local/financecalc/output/financial_table.php');
        $table = new \local_financecalc\output\financial_table('financial-report');
        $table->define_columns(array('year', 'spending', 'budget', 'balance'));
        $table->define_headers(array(
            get_string('year', 'local_financecalc'),
            get_string('spending', 'local_financecalc'),
            get_string('budget', 'local_financecalc'),
            get_string('balance', 'local_financecalc')
        ));
        $table->setup();
        
        foreach ($data as $row) {
            $balance = $row->budget_omr - $row->spending_omr;
            $balanceclass = $balance >= 0 ? 'text-success' : 'text-danger';
            
            $table->add_data(array(
                $row->year,
                number_format($row->spending_omr, 2) . ' OMR',
                number_format($row->budget_omr, 2) . ' OMR',
                html_writer::span(number_format($balance, 2) . ' OMR', $balanceclass)
            ));
        }
        
        $table->finish_output();
    }
    
    // Show last updated time.
    require_once($CFG->dirroot . '/local/financecalc/classes/data_manager.php');
    $lastupdated = \local_financecalc\data_manager::get_last_updated_time();
    if ($lastupdated) {
        echo html_writer::div(
            get_string('data_last_updated', 'local_financecalc', userdate($lastupdated)),
            'text-muted mt-3'
        );
    }
    
} catch (Exception $e) {
    echo $OUTPUT->notification($e->getMessage(), 'error');
}

echo $OUTPUT->footer();
