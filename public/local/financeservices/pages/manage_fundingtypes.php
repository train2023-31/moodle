<?php
// =============================================================
//  Finance Services ➜ Manage Funding Types (Dashboard + Table)
//  - Global tabs: Add | List | Manage
//  - Local tabs : Add Funding Type | Manage Funding Types
// =============================================================

require_once(__DIR__ . '/../../../config.php');

// Add event class imports
use local_financeservices\event\fundingtype_hidden;
use local_financeservices\event\fundingtype_restored;

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url(new moodle_url('/local/financeservices/pages/manage_fundingtypes.php'));
$PAGE->set_title(get_string('managefundingtypes', 'local_financeservices'));
$PAGE->set_heading(get_string('financeservices', 'local_financeservices'));

global $OUTPUT, $DB;

/* ────────────────────────────────────────────────────────────────
   ① Global tab navigation (Add | List | Manage)
   ──────────────────────────────────────────────────────────────── */
$maintabs = [
    new tabobject('add',    new moodle_url('/local/financeservices/index.php', ['tab' => 'add']),    get_string('add',            'local_financeservices')),
    new tabobject('list',   new moodle_url('/local/financeservices/index.php', ['tab' => 'list']),   get_string('list',           'local_financeservices')),
    new tabobject('manage', new moodle_url('/local/financeservices/index.php', ['tab' => 'manage']), get_string('manageservices', 'local_financeservices')),
];

/* ────────────────────────────────────────────────────────────────
   ② Local tab navigation (Add Funding Type | Manage Funding Types)
   ──────────────────────────────────────────────────────────────── */
$subtabs = [
    new tabobject('addfundingtype', new moodle_url('/local/financeservices/pages/add_fundingtype.php'),     get_string('addfundingtype', 'local_financeservices')),
    new tabobject('managefundingtypes', new moodle_url('/local/financeservices/pages/manage_fundingtypes.php'), get_string('managefundingtypes', 'local_financeservices')),
];

$action = optional_param('action', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);
$showhidden = optional_param('showhidden', 0, PARAM_BOOL);
$sesskey = sesskey();

echo $OUTPUT->header();
echo $OUTPUT->tabtree($maintabs, 'manage');
echo $OUTPUT->tabtree($subtabs,  'managefundingtypes');

// Handle hide/restore actions
if ($action === 'hide' && confirm_sesskey()) {
    // Get funding type data before hiding
    $fundingtype = $DB->get_record('local_financeservices_funding_type', array('id' => $id), '*', MUST_EXIST);
    
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

    redirect(new moodle_url('/local/financeservices/pages/manage_fundingtypes.php', ['action' => 'manage']));

} else if ($action === 'restore' && confirm_sesskey()) {
    // Get funding type data before restoring
    $fundingtype = $DB->get_record('local_financeservices_funding_type', array('id' => $id), '*', MUST_EXIST);
    
    $DB->set_field('local_financeservices_funding_type', 'deleted', 0, ['id' => $id]);

    // Trigger restored event
    $event = fundingtype_restored::create(array(
        'objectid' => $id,
        'context' => $context,
        'other' => array(
            'funding_type_en' => $fundingtype->funding_type_en,
            'funding_type_ar' => $fundingtype->funding_type_ar
        )
    ));
    $event->trigger();

    redirect(new moodle_url('/local/financeservices/pages/manage_fundingtypes.php', ['action' => 'manage', 'showhidden' => 1]));
}

/* ────────────────────────────────────────────────────────────────
   1. Dashboard cards view (default)
   ──────────────────────────────────────────────────────────────── */
if (empty($action)) {
    echo html_writer::start_div('card-deck', ['style' => 'display:flex;gap:20px;margin-top:30px;']);

    // Manage card
    $manageurl = new moodle_url('/local/financeservices/pages/manage_fundingtypes.php', ['action' => 'manage']);
    echo html_writer::start_div('card', ['style'=>'flex:1;text-align:center;padding:20px;border:1px solid #ccc;border-radius:10px']);
    echo html_writer::tag('div','🛠',['style'=>'font-size:40px;margin-bottom:10px']);
    echo html_writer::tag('h3', get_string('managefundingtypes','local_financeservices'));
    echo html_writer::tag('p',  get_string('vieweditfundingtypes','local_financeservices'));
    echo html_writer::link($manageurl, get_string('managefundingtypes','local_financeservices'), ['class'=>'btn btn-primary','style'=>'margin-top:10px']);
    echo html_writer::end_div();

    // Add card
    $addurl = new moodle_url('/local/financeservices/pages/add_fundingtype.php');
    echo html_writer::start_div('card', ['style'=>'flex:1;text-align:center;padding:20px;border:1px solid #ccc;border-radius:10px']);
    echo html_writer::tag('div','➕',['style'=>'font-size:40px;margin-bottom:10px']);
    echo html_writer::tag('h3', get_string('addfundingtype','local_financeservices'));
    echo html_writer::tag('p',  get_string('addnewfundingtype','local_financeservices'));
    echo html_writer::link($addurl, get_string('addfundingtype','local_financeservices'), ['class'=>'btn btn-success','style'=>'margin-top:10px']);
    echo html_writer::end_div();

    echo html_writer::end_div(); // end of card deck
}

/* ────────────────────────────────────────────────────────────────
   2. Manage table view (action=manage)
   ──────────────────────────────────────────────────────────────── */
elseif ($action === 'manage') {
    // Get funding types, filter by deleted status if not showing hidden
    $select = $showhidden ? '' : 'deleted = 0';
    $fundingtypes = $DB->get_records_select('local_financeservices_funding_type', $select);

    echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'margin-top:30px;']);
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('fundingtypeen', 'local_financeservices'));
    echo html_writer::tag('th', get_string('fundingtypear', 'local_financeservices'));
    echo html_writer::tag('th', get_string('actions',        'local_financeservices'));
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');

    echo html_writer::start_tag('tbody');
    foreach ($fundingtypes as $type) {
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', s($type->funding_type_en));
        echo html_writer::tag('td', s($type->funding_type_ar));
        echo html_writer::start_tag('td');

        $editurl = new moodle_url('/local/financeservices/pages/edit_fundingtype.php', ['id' => $type->id]);
        
        if (!$type->deleted) {
            $hideurl = new moodle_url('/local/financeservices/pages/manage_fundingtypes.php', 
                ['action' => 'hide', 'id' => $type->id, 'sesskey' => $sesskey]);
            echo html_writer::link($editurl, get_string('edit', 'local_financeservices')) . ' | ';
            echo html_writer::link($hideurl, get_string('hide', 'local_financeservices'));
        } else {
            $restoreurl = new moodle_url('/local/financeservices/pages/manage_fundingtypes.php', 
                ['action' => 'restore', 'id' => $type->id, 'sesskey' => $sesskey]);
            echo html_writer::link($editurl, get_string('edit', 'local_financeservices')) . ' | ';
            echo html_writer::link($restoreurl, get_string('restore', 'local_financeservices'));
        }

        echo html_writer::end_tag('td');
        echo html_writer::end_tag('tr');
    }
    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
    
    // Toggle hidden records link
    echo html_writer::div(
        html_writer::link(
            new moodle_url('/local/financeservices/pages/manage_fundingtypes.php', 
                ['action' => 'manage', 'showhidden' => $showhidden ? 0 : 1]),
            $showhidden ? get_string('hidehiddenrecords', 'local_financeservices') 
                        : get_string('showhiddenrecords', 'local_financeservices')
        ),
        'mt-3'
    );
}

echo $OUTPUT->footer();
