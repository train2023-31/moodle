<?php
// =============================================================
//  Finance Services - Edit Funding Type
// =============================================================

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/financeservices/classes/form/funding_type_form.php');

// Add event class import
use local_financeservices\event\fundingtype_updated;

$id = required_param('id', PARAM_INT);
$context = context_system::instance();
require_login();

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url(new moodle_url('/local/financeservices/pages/edit_fundingtype.php', ['id' => $id]));
$PAGE->set_title(get_string('editfundingtype', 'local_financeservices'));
$PAGE->set_heading(get_string('financeservices', 'local_financeservices'));

// Tabs setup
$tab = 'manage';
$tabs = [
    new tabobject('add', new moodle_url('/local/financeservices/index.php', ['tab' => 'add']), get_string('add', 'local_financeservices')),
    new tabobject('list', new moodle_url('/local/financeservices/index.php', ['tab' => 'list']), get_string('list', 'local_financeservices')),
    new tabobject('manage', new moodle_url('/local/financeservices/index.php', ['tab' => 'manage']), get_string('manageservices', 'local_financeservices')),
];

$record = $DB->get_record('local_financeservices_funding_type', ['id' => $id], '*', MUST_EXIST);

$mform = new \local_financeservices\form\funding_type_form();

$mform->set_data([
    'id' => $record->id,
    'funding_type_en' => $record->funding_type_en,
    'funding_type_ar' => $record->funding_type_ar,
    'deleted' => $record->deleted
]);

echo $OUTPUT->header();
echo $OUTPUT->tabtree($tabs, $tab);
echo $OUTPUT->heading(get_string('editfundingtype', 'local_financeservices'));

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/financeservices/pages/manage_fundingtypes.php'));
} else if ($data = $mform->get_data()) {
    $record->funding_type_en = $data->funding_type_en;
    $record->funding_type_ar = $data->funding_type_ar;
    // Keep the deleted status as is, don't change it through the edit form
    $DB->update_record('local_financeservices_funding_type', $record);

    // Trigger updated event
    $event = fundingtype_updated::create(array(
        'objectid' => $record->id,
        'context' => $context,
        'other' => array(
            'funding_type_en' => $record->funding_type_en,
            'funding_type_ar' => $record->funding_type_ar
        )
    ));
    $event->trigger();

    redirect(new moodle_url('/local/financeservices/pages/manage_fundingtypes.php'), 
        get_string('changessaved', 'local_financeservices'));
}

$mform->display();

echo $OUTPUT->footer();
