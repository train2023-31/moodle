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
 * Clause spending report page for the financecalc plugin.
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
$PAGE->set_url(new moodle_url('/local/financecalc/pages/clause_report.php'));
$PAGE->set_title(get_string('clause_spending_report', 'local_financecalc'));
$PAGE->set_heading(get_string('clause_spending_overview', 'local_financecalc'));
$PAGE->set_pagelayout('standard');

// Get parameters
$yearfilter = optional_param('year', 0, PARAM_INT);
$clause_id = optional_param('clause_id', 0, PARAM_INT);
$showhidden = optional_param('showhidden', 0, PARAM_BOOL);

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
echo html_writer::tag('h1', get_string('clause_spending_report', 'local_financecalc'));
echo html_writer::tag('p', get_string('detailed_spending_analysis', 'local_financecalc'));
echo html_writer::end_div();

// Navigation and Filter Container
echo html_writer::start_div('financecalc-nav-container');
echo html_writer::tag('h3', get_string('navigation_filters', 'local_financecalc'));

// Navigation Buttons
echo html_writer::start_div('financecalc-nav-buttons');
$main_report_url = new moodle_url('/local/financecalc/pages/report.php');
echo html_writer::link($main_report_url, 
    html_writer::tag('i', '', array('class' => 'fa fa-arrow-left')) . ' ' . get_string('financialoverview', 'local_financecalc'), 
    array('class' => 'financecalc-nav-btn'));

$index_url = new moodle_url('/local/financecalc/index.php');
echo html_writer::link($index_url, 
    html_writer::tag('i', '', array('class' => 'fa fa-home')) . ' ' . get_string('dashboard', 'local_financecalc'), 
    array('class' => 'financecalc-nav-btn'));
echo html_writer::end_div();

// Filter Container
echo html_writer::start_div('financecalc-filter-container');
echo html_writer::tag('h4', get_string('filter_options', 'local_financecalc'), array('style' => 'color: #495057; margin: 0 0 15px 0; font-size: 1.1em;'));

// Create filter form for year selection
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
        redirect(new moodle_url($PAGE->url, array('year' => $yearfilter)));
    }
}

echo $filterform->render();
echo html_writer::end_div();

// Action Buttons
echo html_writer::start_div('financecalc-actions');
if ($yearfilter > 0) {
    $toggleurl = new moodle_url('/local/financecalc/pages/clause_report.php', 
        array('year' => $yearfilter, 'showhidden' => $showhidden ? 0 : 1));
    echo html_writer::link($toggleurl, 
        html_writer::tag('i', '', array('class' => 'fa fa-eye')) . ' ' . 
        ($showhidden ? get_string('hide_hidden_clauses', 'local_financecalc') 
                   : get_string('show_hidden_clauses', 'local_financecalc')), 
        array('class' => 'financecalc-action-btn'));
}
echo html_writer::end_div();

echo html_writer::end_div();

// Get and display the clause spending data.
require_once($CFG->dirroot . '/local/financecalc/classes/data_manager.php');

try {
    if ($clause_id > 0) {
        // Show detailed view for specific clause
        $clause_details = \local_financecalc\data_manager::get_clause_details($clause_id);
        display_clause_details($clause_details, $OUTPUT);
    } else {
        // Show summary view for all clauses
        $clause_data = \local_financecalc\data_manager::get_clause_spending_data($yearfilter);
        $summary = \local_financecalc\data_manager::get_clause_summary($yearfilter);
        display_clause_summary($clause_data, $summary, $yearfilter, $showhidden, $OUTPUT);
    }
    
} catch (Exception $e) {
    echo $OUTPUT->notification($e->getMessage(), 'error');
}

echo $OUTPUT->footer();

/**
 * Display clause summary table
 */
function display_clause_summary($clause_data, $summary, $yearfilter, $showhidden, $OUTPUT) {
    global $DB;
    
    // Filter out hidden clauses if not showing hidden
    if (!$showhidden) {
        $clause_data = array_filter($clause_data, function($clause) {
            return $clause->deleted == 0;
        });
    }
    
    // Summary statistics
    echo html_writer::start_div('row mb-4');
    echo html_writer::start_div('col-md-3');
    echo html_writer::div(
        html_writer::tag('h4', count($clause_data), array('class' => 'text-primary')) .
        html_writer::tag('p', get_string('total_clauses', 'local_financecalc'), array('class' => 'text-muted')),
        'card text-center p-3'
    );
    echo html_writer::end_div();
    
    echo html_writer::start_div('col-md-3');
    echo html_writer::div(
        html_writer::tag('h4', number_format($summary->total_budget, 2) . ' OMR', array('class' => 'text-success')) .
        html_writer::tag('p', get_string('total_budget', 'local_financecalc'), array('class' => 'text-muted')),
        'card text-center p-3'
    );
    echo html_writer::end_div();
    
    echo html_writer::start_div('col-md-3');
    echo html_writer::div(
        html_writer::tag('h4', number_format($summary->total_spent, 2) . ' OMR', array('class' => 'text-warning')) .
        html_writer::tag('p', get_string('total_spent', 'local_financecalc'), array('class' => 'text-muted')),
        'card text-center p-3'
    );
    echo html_writer::end_div();
    
    echo html_writer::start_div('col-md-3');
    $remaining_class = $summary->total_remaining >= 0 ? 'text-success' : 'text-danger';
    echo html_writer::div(
        html_writer::tag('h4', number_format($summary->total_remaining, 2) . ' OMR', array('class' => $remaining_class)) .
        html_writer::tag('p', get_string('remaining_budget', 'local_financecalc'), array('class' => 'text-muted')),
        'card text-center p-3'
    );
    echo html_writer::end_div();
    echo html_writer::end_div();
    
    
    // Clause table
    if (empty($clause_data)) {
        echo $OUTPUT->notification(get_string('no_clauses_found', 'local_financecalc'), 'info');
        return;
    }
    
    $table = new html_table();
    $table->head = array(
        get_string('clause_name', 'local_financecalc'),
        get_string('year', 'local_financecalc'),
        get_string('budget_amount', 'local_financecalc'),
        get_string('spent_amount', 'local_financecalc'),
        get_string('remaining_budget', 'local_financecalc'),
        get_string('spending_percentage', 'local_financecalc'),
        get_string('request_count', 'local_financecalc'),
        get_string('actions', 'local_financecalc')
    );
    
    $current_language = current_language();
    $name_field = $current_language === 'ar' ? 'clause_name_ar' : 'clause_name_en';
    
    foreach ($clause_data as $clause) {
        $remaining_class = $clause->remaining_budget >= 0 ? 'text-success' : 'text-danger';
        $percentage_class = $clause->spending_percentage > 80 ? 'text-warning' : 
                           ($clause->spending_percentage > 100 ? 'text-danger' : 'text-success');
        
        $detail_url = new moodle_url('/local/financecalc/pages/clause_report.php', 
            array('clause_id' => $clause->id, 'year' => $yearfilter));
        
        $table->data[] = array(
            $clause->$name_field,
            $clause->clause_year,
            number_format($clause->budget_amount, 2) . ' OMR',
            number_format($clause->total_spent, 2) . ' OMR',
            html_writer::span(number_format($clause->remaining_budget, 2) . ' OMR', $remaining_class),
            html_writer::span($clause->spending_percentage . '%', $percentage_class),
            $clause->request_count,
            html_writer::link($detail_url, get_string('view_details', 'local_financecalc'), 
                array('class' => 'btn btn-sm btn-primary'))
        );
    }
    
    echo html_writer::table($table);
}

/**
 * Display detailed view for a specific clause
 */
function display_clause_details($clause_details, $OUTPUT) {
    global $DB;
    
    $clause = $clause_details->clause;
    $current_language = current_language();
    $name_field = $current_language === 'ar' ? 'clause_name_ar' : 'clause_name_en';
    
    // Back button
    $back_url = new moodle_url('/local/financecalc/pages/clause_report.php');
    echo html_writer::start_div('financecalc-breadcrumb');
    echo html_writer::link($back_url, 
        html_writer::tag('i', '', array('class' => 'fa fa-arrow-left')) . ' ' . get_string('back_to_clause_list', 'local_financecalc'));
    echo html_writer::end_div();
    
    // Clause header
    echo html_writer::tag('h2', $clause->$name_field);
    echo html_writer::tag('p', get_string('clause_year', 'local_financecalc') . ': ' . $clause->clause_year, 
        array('class' => 'text-muted'));
    
    // Summary cards
    echo html_writer::start_div('row mb-4');
    echo html_writer::start_div('col-md-3');
    echo html_writer::div(
        html_writer::tag('h4', number_format($clause->amount, 2) . ' OMR', array('class' => 'text-success')) .
        html_writer::tag('p', get_string('budget_amount', 'local_financecalc'), array('class' => 'text-muted')),
        'card text-center p-3'
    );
    echo html_writer::end_div();
    
    echo html_writer::start_div('col-md-3');
    echo html_writer::div(
        html_writer::tag('h4', number_format($clause_details->approved_spent, 2) . ' OMR', array('class' => 'text-warning')) .
        html_writer::tag('p', get_string('approved_spent', 'local_financecalc'), array('class' => 'text-muted')),
        'card text-center p-3'
    );
    echo html_writer::end_div();
    
    echo html_writer::start_div('col-md-3');
    echo html_writer::div(
        html_writer::tag('h4', number_format($clause_details->remaining_budget, 2) . ' OMR', 
            array('class' => $clause_details->remaining_budget >= 0 ? 'text-success' : 'text-danger')) .
        html_writer::tag('p', get_string('remaining_budget', 'local_financecalc'), array('class' => 'text-muted')),
        'card text-center p-3'
    );
    echo html_writer::end_div();
    
    echo html_writer::start_div('col-md-3');
    $percentage_class = $clause_details->spending_percentage > 80 ? 'text-warning' : 
                       ($clause_details->spending_percentage > 100 ? 'text-danger' : 'text-success');
    echo html_writer::div(
        html_writer::tag('h4', $clause_details->spending_percentage . '%', array('class' => $percentage_class)) .
        html_writer::tag('p', get_string('spending_percentage', 'local_financecalc'), array('class' => 'text-muted')),
        'card text-center p-3'
    );
    echo html_writer::end_div();
    echo html_writer::end_div();
    
    // Progress bar
    $percentage = min($clause_details->spending_percentage, 100);
    $progress_class = $percentage > 80 ? 'bg-warning' : ($percentage > 100 ? 'bg-danger' : 'bg-success');
    echo html_writer::start_div('progress mb-4');
    echo html_writer::div(
        $clause_details->spending_percentage . '%',
        'progress-bar ' . $progress_class,
        array('style' => 'width: ' . $percentage . '%', 'role' => 'progressbar', 'aria-valuenow' => $percentage, 'aria-valuemin' => '0', 'aria-valuemax' => '100')
    );
    echo html_writer::end_div();
    
    // Spending records table
    if (empty($clause_details->spending_records)) {
        echo $OUTPUT->notification(get_string('no_spending_records', 'local_financecalc'), 'info');
        return;
    }
    
    echo html_writer::tag('h3', get_string('spending_records', 'local_financecalc'));
    
    $table = new html_table();
    $table->head = array(
        get_string('request_id', 'local_financecalc'),
        get_string('course', 'local_financecalc'),
        get_string('requester', 'local_financecalc'),
        get_string('amount', 'local_financecalc'),
        get_string('status', 'local_financecalc'),
        get_string('request_date', 'local_financecalc'),
        get_string('notes', 'local_financecalc')
    );
    
    $status_field = $current_language === 'ar' ? 'display_name_ar' : 'display_name_en';
    
    foreach ($clause_details->spending_records as $record) {
        $status_class = ($record->$status_field === 'Approved' || $record->$status_field === 'موافق عليه') ? 
                       'badge badge-success' : 'badge badge-warning';
        
        $table->data[] = array(
            $record->id,
            $record->course_name,
            $record->firstname . ' ' . $record->lastname,
            number_format($record->price_requested, 2) . ' OMR',
            html_writer::span($record->$status_field, $status_class),
            userdate($record->date_time_requested),
            html_writer::div($record->notes, 'small text-muted')
        );
    }
    
    echo html_writer::table($table);
}
