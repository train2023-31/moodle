<?php
// =============================================================
//  Finance Services – Add Funding Type (Global + Local Tabs)
// =============================================================

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/financeservices/classes/form/funding_type_form.php');

// Add event class import
use local_financeservices\event\fundingtype_created;

global $OUTPUT, $PAGE, $DB;

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url(new moodle_url('/local/financeservices/pages/add_fundingtype.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('addfundingtype', 'local_financeservices'));
$PAGE->set_heading(get_string('financeservices', 'local_financeservices'));

/* ────────────────────────────────────────────────────────────────
   ① Global tabs: Add | List | Manage
   ──────────────────────────────────────────────────────────────── */
$maintabs = [
    new tabobject('add',    new moodle_url('/local/financeservices/index.php', ['tab' => 'add']),    get_string('add',            'local_financeservices')),
    new tabobject('list',   new moodle_url('/local/financeservices/index.php', ['tab' => 'list']),   get_string('list',           'local_financeservices')),
    new tabobject('manage', new moodle_url('/local/financeservices/index.php', ['tab' => 'manage']), get_string('manageservices', 'local_financeservices')),
];

/* ────────────────────────────────────────────────────────────────
   ② Local tabs: Add Funding Type | Manage Funding Types
   ──────────────────────────────────────────────────────────────── */
$subtabs = [
    new tabobject('addfundingtype',
        new moodle_url('/local/financeservices/pages/add_fundingtype.php'),
        get_string('addfundingtype', 'local_financeservices')),
    new tabobject('managefundingtypes',
        new moodle_url('/local/financeservices/pages/manage_fundingtypes.php'),
        get_string('managefundingtypes', 'local_financeservices')),
];

/* ────────────────────────────────────────────────────────────────
   Form setup and submission
   ──────────────────────────────────────────────────────────────── */
$mform = new \local_financeservices\form\funding_type_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/financeservices/pages/manage_fundingtypes.php'));

} elseif ($data = $mform->get_data()) {
    $record = (object) [
        'funding_type_en' => $data->funding_type_en,
        'funding_type_ar' => $data->funding_type_ar,
        'deleted' => 0, // New records are always active (not deleted)
    ];
    $id = $DB->insert_record('local_financeservices_funding_type', $record);

    // Trigger created event
    $event = fundingtype_created::create(array(
        'objectid' => $id,
        'context' => $context,
        'other' => array(
            'funding_type_en' => $record->funding_type_en,
            'funding_type_ar' => $record->funding_type_ar
        )
    ));
    $event->trigger();

    redirect(
        new moodle_url('/local/financeservices/pages/manage_fundingtypes.php', ['action' => 'manage']),
        get_string('changessaved', 'local_financeservices'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

/* ────────────────────────────────────────────────────────────────
   Render output
   ──────────────────────────────────────────────────────────────── */
echo $OUTPUT->header();
echo $OUTPUT->tabtree($maintabs, 'manage');           // highlight main
echo $OUTPUT->tabtree($subtabs,  'addfundingtype');   // highlight local
echo $OUTPUT->heading(get_string('addfundingtype', 'local_financeservices'), 3);

$mform->display();
echo $OUTPUT->footer();
