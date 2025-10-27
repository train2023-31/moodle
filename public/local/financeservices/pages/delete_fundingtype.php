<?php
// =============================================================
//  Finance Services - Soft delete Funding Type (set deleted=1)
// =============================================================

require_once(__DIR__ . '/../../../config.php');

// Add event class import
use local_financeservices\event\fundingtype_hidden;

$id = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$context = context_system::instance();
require_login();

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url(new moodle_url('/local/financeservices/pages/delete_fundingtype.php', ['id' => $id]));
$PAGE->set_title(get_string('hidefundingtype', 'local_financeservices'));
$PAGE->set_heading(get_string('financeservices', 'local_financeservices'));

// Tabs
$tab = 'manage';
$tabs = [
    new tabobject('add', new moodle_url('/local/financeservices/index.php', ['tab' => 'add']), get_string('add', 'local_financeservices')),
    new tabobject('list', new moodle_url('/local/financeservices/index.php', ['tab' => 'list']), get_string('list', 'local_financeservices')),
    new tabobject('manage', new moodle_url('/local/financeservices/index.php', ['tab' => 'manage']), get_string('manageservices', 'local_financeservices')),
];

echo $OUTPUT->header();
echo $OUTPUT->tabtree($tabs, $tab);
echo $OUTPUT->heading(get_string('hidefundingtype', 'local_financeservices'));

if ($confirm) {
    // Get funding type data before hiding
    $fundingtype = $DB->get_record('local_financeservices_funding_type', array('id' => $id), '*', MUST_EXIST);
    
    // Soft delete by setting the 'deleted' flag instead of removing the record
    $DB->set_field('local_financeservices_funding_type', 'deleted', 1, ['id' => $id]);

    // Trigger hidden event
    $event = fundingtype_hidden::create(array(
        'objectid' => $id,
        'context' => $context,
        'other' => array(
            'funding_type_en' => $fundingtype->funding_type_en,
            'funding_type_ar' => $fundingtype->funding_type_ar
        )
    ));
    $event->trigger();

    redirect(new moodle_url('/local/financeservices/pages/manage_fundingtypes.php', ['action' => 'manage']), 
        get_string('fundingtypehidden', 'local_financeservices'));
} else {
    $yesurl = new moodle_url('/local/financeservices/pages/delete_fundingtype.php', ['id' => $id, 'confirm' => 1]);
    $nourl = new moodle_url('/local/financeservices/pages/manage_fundingtypes.php', ['action' => 'manage']);
    echo $OUTPUT->confirm(get_string('confirmhide', 'local_financeservices'), $yesurl, $nourl);
}

echo $OUTPUT->footer();
