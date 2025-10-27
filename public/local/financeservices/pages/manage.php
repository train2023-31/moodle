<?php
// =============================================================
//  Finance Services - Main Manage Page (Cards View)
// =============================================================

// Load Moodle environment
require_once(__DIR__ . '/../../../config.php'); //this should be included at all the pages inside the plages folders
require_once($CFG->dirroot . '/local/financeservices/classes/form/funding_type_form.php'); // Just in case needed

echo $OUTPUT->heading(get_string('manageservices', 'local_financeservices'));

// Card Layout
echo html_writer::start_div('card-deck', ['style' => 'display:flex;gap:20px;margin-top:30px;']);

// ────────────── Manage Funding Types Card ──────────────
$fundingtypesurl = new moodle_url('/local/financeservices/pages/manage_fundingtypes.php');
echo html_writer::start_div('card', ['style' => 'flex:1;text-align:center;padding:20px;border:1px solid #ccc;border-radius:10px;']);
echo html_writer::tag('div', '💵', ['style' => 'font-size:40px;margin-bottom:10px;']);
echo html_writer::tag('h3', get_string('managefundingtypes', 'local_financeservices'));
echo html_writer::tag('p', get_string('vieweditfundingtypes', 'local_financeservices'));
echo html_writer::link($fundingtypesurl, get_string('managefundingtypes', 'local_financeservices'), ['class' => 'btn btn-primary', 'style' => 'margin-top:10px;']);
echo html_writer::end_div();

// ────────────── Manage Clauses Card ──────────────
$clausesurl = new moodle_url('/local/financeservices/pages/manage_clauses.php');
echo html_writer::start_div('card', ['style' => 'flex:1;text-align:center;padding:20px;border:1px solid #ccc;border-radius:10px;']);
echo html_writer::tag('div', '📜', ['style' => 'font-size:40px;margin-bottom:10px;']);
echo html_writer::tag('h3', get_string('manageclauses', 'local_financeservices'));
echo html_writer::tag('p', get_string('vieweditclauses', 'local_financeservices'));
echo html_writer::link($clausesurl, get_string('manageclauses', 'local_financeservices'), ['class' => 'btn btn-success', 'style' => 'margin-top:10px;']);
echo html_writer::end_div();

// Finance Calculator card (only show if user has permission)
if (has_capability('local/financecalc:view', context_system::instance())) {
    $calc_url = new moodle_url('/local/financecalc/index.php');
    echo html_writer::start_div('card', ['style' => 'flex:1;text-align:center;padding:20px;border:1px solid #ccc;border-radius:10px;']);
    echo html_writer::tag('div', '📊', ['style' => 'font-size:40px;margin-bottom:10px;']);
    echo html_writer::tag('h3', get_string('pluginname', 'local_financecalc'));
    echo html_writer::tag('p', 'Financial calculations and spending analysis');
    echo html_writer::link($calc_url, get_string('pluginname', 'local_financecalc'),
        ['class' => 'btn btn-info', 'style' => 'margin-top:10px;']);
    echo html_writer::end_div();
}

echo html_writer::end_div();
