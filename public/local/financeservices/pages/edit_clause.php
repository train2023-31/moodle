<?php
// =============================================================
//  Finance Services - Edit Clause (with amount field)
// =============================================================

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/financeservices/classes/form/clause_form.php');

// Add event class imports
use local_financeservices\event\clause_created;
use local_financeservices\event\clause_updated;

$id = required_param('id', PARAM_INT);
$context = context_system::instance();
require_login();

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url(new moodle_url('/local/financeservices/pages/edit_clause.php', ['id' => $id]));
$PAGE->set_title(get_string('editclause', 'local_financeservices'));
$PAGE->set_heading(get_string('financeservices', 'local_financeservices'));

global $OUTPUT, $DB;

// Tabs
$tab  = 'manage';
$tabs = [
    new tabobject('add',    new moodle_url('/local/financeservices/index.php', ['tab' => 'add']),    get_string('add', 'local_financeservices')),
    new tabobject('list',   new moodle_url('/local/financeservices/index.php', ['tab' => 'list']),   get_string('list', 'local_financeservices')),
    new tabobject('manage', new moodle_url('/local/financeservices/index.php', ['tab' => 'manage']), get_string('manageservices', 'local_financeservices')),
];

// Get the clause
$record = $DB->get_record('local_financeservices_clause', ['id' => $id], '*', MUST_EXIST);

$mform = new \local_financeservices\form\clause_form();

// Pre-fill form with existing values including the amount and audit fields
$mform->set_data([
    'id'             => $record->id,
    'clause_name_en' => $record->clause_name_en,
    'clause_name_ar' => $record->clause_name_ar,
    'amount'         => $record->amount, // 🆕 include current amount
    'clause_year'    => $record->clause_year ?? (int)date('Y'),
    'deleted'        => $record->deleted,
    'created_by'     => $record->created_by ?? 0,
    'created_date'   => $record->created_date ?? 0,
    'modified_by'    => $record->modified_by ?? 0,
    'modified_date'  => $record->modified_date ?? 0,
    'initial_amount' => $record->initial_amount ?? $record->amount,
]);

echo $OUTPUT->header();
echo $OUTPUT->tabtree($tabs, $tab);
echo $OUTPUT->heading(get_string('manageclauses', 'local_financeservices'));

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/financeservices/pages/manage_clauses.php'));
} else if ($data = $mform->get_data()) {
    if (empty($data->id)) {
        // Creating new clause
        global $USER;
        $currenttime = time();
        
        $data->deleted = 0;
        $data->created_by = $USER->id;
        $data->created_date = $currenttime;
        $data->initial_amount = $data->amount; // Set initial_amount to initial amount
        
        // Trim whitespace before saving to prevent duplicates
        $data->clause_name_en = trim($data->clause_name_en);
        $data->clause_name_ar = trim($data->clause_name_ar);
        
        $id = $DB->insert_record('local_financeservices_clause', $data);

        // Trigger created event
        $event = clause_created::create(array(
            'objectid' => $id,
            'context' => $context,
            'other' => array(
                'clause_name_en' => $data->clause_name_en,
                'clause_name_ar' => $data->clause_name_ar,
                'amount' => $data->amount,
                'initial_amount' => $data->initial_amount,
                'created_by' => $data->created_by,
                'clause_year' => (int)$data->clause_year
            )
        ));
        $event->trigger();
    } else {
        // Updating existing clause
        global $USER;
        $currenttime = time();
        
        // Set modification audit fields
        $data->modified_by = $USER->id;
        $data->modified_date = $currenttime;
        
        // Keep initial_amount unchanged (it should remain the original amount)
        $data->initial_amount = $record->initial_amount ?? $record->amount;
        
        // Prevent changing clause_year to a past year
        if (!empty($data->clause_year) && (int)$data->clause_year < (int)date('Y')) {
            // Force it back to original value if attempted
            $data->clause_year = $record->clause_year;
        }

        // Trim whitespace before saving to prevent duplicates
        $data->clause_name_en = trim($data->clause_name_en);
        $data->clause_name_ar = trim($data->clause_name_ar);

        $DB->update_record('local_financeservices_clause', $data);

        // Trigger updated event
        $event = clause_updated::create(array(
            'objectid' => $data->id,
            'context' => $context,
            'other' => array(
                'clause_name_en' => $data->clause_name_en,
                'clause_name_ar' => $data->clause_name_ar,
                'amount' => $data->amount,
                'initial_amount' => $data->initial_amount,
                'modified_by' => $data->modified_by,
                'clause_year' => (int)($data->clause_year ?? $record->clause_year)
            )
        ));
        $event->trigger();
    }

    redirect(
        new moodle_url('/local/financeservices/pages/manage_clauses.php', array('action' => 'manage')),
        get_string('changessaved', 'local_financeservices'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$mform->display();
echo $OUTPUT->footer();
