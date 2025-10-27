<?php
// =============================================================
//  Finance Services – Add Clause (Global + Local Tabs)
// =============================================================

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/financeservices/classes/form/clause_form.php');

// Add event class import
use local_financeservices\event\clause_created;

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url(new moodle_url('/local/financeservices/pages/add_clause.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('addclause', 'local_financeservices'));
$PAGE->set_heading(get_string('financeservices', 'local_financeservices'));

global $OUTPUT, $PAGE, $DB;

/* ────────────────────────────────────────────────────────────────
   ① Global tab navigation (Add | List | Manage)
   ──────────────────────────────────────────────────────────────── */
$maintabs = [
    new tabobject('add',    new moodle_url('/local/financeservices/index.php', ['tab' => 'add']),    get_string('add',            'local_financeservices')),
    new tabobject('list',   new moodle_url('/local/financeservices/index.php', ['tab' => 'list']),   get_string('list',           'local_financeservices')),
    new tabobject('manage', new moodle_url('/local/financeservices/index.php', ['tab' => 'manage']), get_string('manageservices', 'local_financeservices')),
];

/* ────────────────────────────────────────────────────────────────
   ② Local tab navigation (Add Clause | Manage Clauses)
   ──────────────────────────────────────────────────────────────── */
$subtabs = [
    new tabobject('addclause', new moodle_url('/local/financeservices/pages/add_clause.php'),        get_string('addclause', 'local_financeservices')),
    new tabobject('manageclauses', new moodle_url('/local/financeservices/pages/manage_clauses.php'), get_string('manageclauses', 'local_financeservices')),
];

/* ────────────────────────────────────────────────────────────────
   Form logic and save
   ──────────────────────────────────────────────────────────────── */
$mform = new \local_financeservices\form\clause_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/financeservices/pages/manage_clauses.php'));

} elseif ($data = $mform->get_data()) {
    global $USER;
    $currenttime = time();
    
    $record = (object) [
        'clause_name_en' => trim($data->clause_name_en), // Trim whitespace to prevent duplicates
        'clause_name_ar' => trim($data->clause_name_ar), // Trim whitespace to prevent duplicates
        'amount'         => $data->amount, // 🆕 Save the monetary amount
        'deleted'        => 0,             // Clause is active by default
        'created_by'     => $USER->id,     // 🆕 Track who created it
        'created_date'   => $currenttime,  // 🆕 Track when it was created
        'clause_year'    => (int)$data->clause_year,
        'initial_amount' => $data->amount, // 🆕 Store the original amount
    ];

    $id = $DB->insert_record('local_financeservices_clause', $record);

    // Trigger created event
    $event = clause_created::create(array(
        'objectid' => $id,
        'context' => $context,
        'other' => array(
            'clause_name_en' => $record->clause_name_en,
            'clause_name_ar' => $record->clause_name_ar,
            'amount' => $record->amount,
            'initial_amount' => $record->initial_amount,
            'created_by' => $record->created_by,
            'clause_year' => $record->clause_year
        )
    ));
    $event->trigger();

    redirect(
        new moodle_url('/local/financeservices/pages/manage_clauses.php', ['action' => 'manage']),
        get_string('changessaved', 'local_financeservices'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

/* ────────────────────────────────────────────────────────────────
   Render page
   ──────────────────────────────────────────────────────────────── */
echo $OUTPUT->header();
echo $OUTPUT->tabtree($maintabs, 'manage');
echo $OUTPUT->tabtree($subtabs,  'addclause');
echo $OUTPUT->heading(get_string('addclause', 'local_financeservices'), 3);

$mform->display();
echo $OUTPUT->footer();
