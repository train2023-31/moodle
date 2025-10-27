<?php
// =============================================================
//  Finance Services - Soft delete Clause (set deleted=1)
// =============================================================

require_once(__DIR__ . '/../../../config.php');

// Add event class import
use local_financeservices\event\clause_hidden;

$id = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$context = context_system::instance();
require_login();

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url(new moodle_url('/local/financeservices/pages/delete_clause.php', ['id' => $id]));
$PAGE->set_title(get_string('hideclause', 'local_financeservices'));
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
echo $OUTPUT->heading(get_string('hideclause', 'local_financeservices'));

if ($confirm) {
    // Get clause data before hiding
    $clause = $DB->get_record('local_financeservices_clause', array('id' => $id), '*', MUST_EXIST);
    
    // Soft delete by setting the 'deleted' flag instead of removing the record
    $DB->set_field('local_financeservices_clause', 'deleted', 1, ['id' => $id]);

    // Trigger hidden event
    $event = clause_hidden::create(array(
        'objectid' => $id,
        'context' => $context,
        'other' => array(
            'clause_name_en' => $clause->clause_name_en,
            'clause_name_ar' => $clause->clause_name_ar
        )
    ));
    $event->trigger();

    redirect(
        new moodle_url('/local/financeservices/pages/manage_clauses.php', ['action' => 'manage']),
        get_string('clausehidden', 'local_financeservices'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
} else {
    $yesurl = new moodle_url('/local/financeservices/pages/delete_clause.php', ['id' => $id, 'confirm' => 1]);
    $nourl  = new moodle_url('/local/financeservices/pages/manage_clauses.php', ['action' => 'manage']);

    echo $OUTPUT->confirm(get_string('confirmhide', 'local_financeservices'), $yesurl, $nourl);
}

echo $OUTPUT->footer();
