<?php
// =============================================================
//  Finance Services ➜ Manage Clauses (Dashboard + Table)
// =============================================================

require_once(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url(new moodle_url('/local/financeservices/pages/manage_clauses.php'));
$PAGE->set_title(get_string('manageclauses', 'local_financeservices'));
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
   ② Local tab navigation (Add Clause | Manage Clauses)
   ──────────────────────────────────────────────────────────────── */
$subtabs = [
    new tabobject('addclause', new moodle_url('/local/financeservices/pages/add_clause.php'),        get_string('addclause', 'local_financeservices')),
    new tabobject('manageclauses', new moodle_url('/local/financeservices/pages/manage_clauses.php'), get_string('manageclauses', 'local_financeservices')),
];

$action = optional_param('action', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);
$showhidden = optional_param('showhidden', 0, PARAM_BOOL);
$sesskey = sesskey();

echo $OUTPUT->header();
echo $OUTPUT->tabtree($maintabs, 'manage');
echo $OUTPUT->tabtree($subtabs,  'manageclauses');
echo $OUTPUT->heading(get_string('manageclauses', 'local_financeservices'));

// Handle hide/restore actions
if ($action === 'hide' && confirm_sesskey()) {
    // Get clause data before hiding
    $clause = $DB->get_record('local_financeservices_clause', array('id' => $id), '*', MUST_EXIST);
    
    // Hide the clause
    $DB->set_field('local_financeservices_clause', 'deleted', 1, array('id' => $id));

    // Trigger hidden event
    $event = \local_financeservices\event\clause_hidden::create(array(
        'objectid' => $id,
        'context' => $context,
        'other' => array(
            'clause_name_en' => $clause->clause_name_en,
            'clause_name_ar' => $clause->clause_name_ar
        )
    ));
    $event->trigger();

    redirect(new moodle_url('/local/financeservices/pages/manage_clauses.php', array('action' => 'manage')));

} else if ($action === 'restore' && confirm_sesskey()) {
    // Get clause data before restoring
    $clause = $DB->get_record('local_financeservices_clause', array('id' => $id), '*', MUST_EXIST);
    
    // Restore the clause
    $DB->set_field('local_financeservices_clause', 'deleted', 0, array('id' => $id));

    // Trigger restored event
    $event = \local_financeservices\event\clause_restored::create(array(
        'objectid' => $id,
        'context' => $context,
        'other' => array(
            'clause_name_en' => $clause->clause_name_en,
            'clause_name_ar' => $clause->clause_name_ar
        )
    ));
    $event->trigger();

    redirect(new moodle_url('/local/financeservices/pages/manage_clauses.php', array('action' => 'manage', 'showhidden' => 1)));
}

/* ────────────────────────────────────────────────────────────────
   1. Dashboard cards view
   ──────────────────────────────────────────────────────────────── */
if (empty($action)) {
    echo html_writer::start_div('card-deck', ['style' => 'display:flex;gap:20px;margin-top:30px;']);

    // Manage Clauses card
    $manageurl = new moodle_url('/local/financeservices/pages/manage_clauses.php', ['action' => 'manage']);
    echo html_writer::start_div('card', ['style' => 'flex:1;text-align:center;padding:20px;border:1px solid #ccc;border-radius:10px;']);
    echo html_writer::tag('div', '🛠', ['style' => 'font-size:40px;margin-bottom:10px;']);
    echo html_writer::tag('h3', get_string('manageclauses', 'local_financeservices'));
    echo html_writer::tag('p', get_string('vieweditclauses', 'local_financeservices'));
    echo html_writer::link($manageurl, get_string('manageclauses', 'local_financeservices'),
        ['class' => 'btn btn-primary', 'style' => 'margin-top:10px;']);
    echo html_writer::end_div();

    // Add Clause card (disabled for past year creation)
    $addurl = new moodle_url('/local/financeservices/pages/add_clause.php');
    echo html_writer::start_div('card', ['style' => 'flex:1;text-align:center;padding:20px;border:1px solid #ccc;border-radius:10px;']);
    echo html_writer::tag('div', '➕', ['style' => 'font-size:40px;margin-bottom:10px;']);
    echo html_writer::tag('h3', get_string('addclause', 'local_financeservices'));
    echo html_writer::tag('p', get_string('addnewclause', 'local_financeservices'));
    echo html_writer::link($addurl, get_string('addclause', 'local_financeservices'),
        ['class' => 'btn btn-success', 'style' => 'margin-top:10px;']);
    echo html_writer::end_div();

    echo html_writer::end_div();
}

/* ────────────────────────────────────────────────────────────────
   2. Clause table view (updated to show 'amount')
   ──────────────────────────────────────────────────────────────── */
elseif ($action === 'manage') {
    // Get clauses, filter by deleted status if not showing hidden
    // Sort by Created on from new to old (created_date DESC)
    $select = $showhidden ? '' : 'deleted = 0';
    $clauses = $DB->get_records_select('local_financeservices_clause', $select, null, 'created_date DESC');

    echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'margin-top:30px;']);
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('clausenameen', 'local_financeservices'));
    echo html_writer::tag('th', get_string('clausenamear', 'local_financeservices'));
    echo html_writer::tag('th', get_string('currentamount', 'local_financeservices')); // Current amount
    echo html_writer::tag('th', get_string('initialamount', 'local_financeservices'));  // Initial amount
   // echo html_writer::tag('th', get_string('spendingdata', 'local_financeservices')); // Spending data
    echo html_writer::tag('th', get_string('createdby', 'local_financeservices'));
    echo html_writer::tag('th', get_string('createddate', 'local_financeservices'));
    echo html_writer::tag('th', get_string('clauseyear', 'local_financeservices'));
    echo html_writer::tag('th', get_string('modifiedby', 'local_financeservices'));
    echo html_writer::tag('th', get_string('modifieddate', 'local_financeservices'));
    echo html_writer::tag('th', get_string('actions', 'local_financeservices'));
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');

    echo html_writer::start_tag('tbody');
    foreach ($clauses as $c) {

        
        // TODO: Implement spending data display once ready if needed
        // Get spending data for this clause
        // $spending_sql = "SELECT 
        //                     COALESCE(SUM(f.price_requested), 0) AS total_spent,
        //                     COUNT(*) AS request_count
        //                  FROM {local_financeservices} f
        //                  WHERE f.clause_id = :clause_id AND f.status_id = 13";
        // $spending_data = $DB->get_record_sql($spending_sql, array('clause_id' => $c->id));
        
        // $remaining_budget = $c->amount - $spending_data->total_spent;
        // $spending_percentage = $c->amount > 0 ? round(($spending_data->total_spent / $c->amount) * 100, 2) : 0;
        
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', s($c->clause_name_en));
        echo html_writer::tag('td', s($c->clause_name_ar));
        echo html_writer::tag('td', format_float($c->amount, 2)); // Current amount
        echo html_writer::tag('td', format_float($c->initial_amount ?? $c->amount, 2)); // Initial amount
        
        // Spending data column - currently empty as per TODO comment
       // echo html_writer::tag('td', '-'); // Empty spending data column
        
        // Created by
        $created_by_name = '';
        if (!empty($c->created_by)) {
            $created_user = $DB->get_record('user', ['id' => $c->created_by], 'firstname, lastname');
            if ($created_user) {
                $created_by_name = fullname($created_user);
            }
        }
        echo html_writer::tag('td', $created_by_name);
        
        // Created date
        $created_date = !empty($c->created_date) ? userdate($c->created_date) : '';
        echo html_writer::tag('td', $created_date);
        // Clause Year
        echo html_writer::tag('td', (string)($c->clause_year ?? ''));
        
        // Modified by
        $modified_by_name = '';
        $modified_by_attr = [];
        if (!empty($c->modified_by)) {
            $modified_user = $DB->get_record('user', ['id' => $c->modified_by], 'firstname, lastname');
            if ($modified_user) {
                $modified_by_name = fullname($modified_user);
            }
        }
        if ($modified_by_name === '') {
            $modified_by_name = get_string('notmodified', 'local_financeservices');
            $modified_by_attr = ['class' => 'dimmed_text'];
        }
        echo html_writer::tag('td', $modified_by_name, $modified_by_attr);
        
        // Modified date
        $modified_date = !empty($c->modified_date) ? userdate($c->modified_date) : '';
        $modified_date_attr = [];
        if ($modified_date === '') {
            $modified_date = get_string('notmodified', 'local_financeservices');
            $modified_date_attr = ['class' => 'dimmed_text'];
        }
        echo html_writer::tag('td', $modified_date, $modified_date_attr);
        
        echo html_writer::start_tag('td');

        $editurl = new moodle_url('/local/financeservices/pages/edit_clause.php', ['id' => $c->id]);

        $isPastYear = !empty($c->clause_year) && (int)$c->clause_year < (int)date('Y');
        if (!$c->deleted) {
            $hideurl = new moodle_url('/local/financeservices/pages/manage_clauses.php',
                ['action' => 'hide', 'id' => $c->id, 'sesskey' => $sesskey]);
            // Disable edit/hide links for past-year clauses
            if ($isPastYear) {
                echo html_writer::span(get_string('edit', 'local_financeservices'), 'dimmed_text') . ' | ';
                echo html_writer::span(get_string('hide', 'local_financeservices'), 'dimmed_text');
            } else {
                echo html_writer::link($editurl, get_string('edit', 'local_financeservices')) . ' | ';
                echo html_writer::link($hideurl, get_string('hide', 'local_financeservices'));
            }
        } else {
            $restoreurl = new moodle_url('/local/financeservices/pages/manage_clauses.php',
                ['action' => 'restore', 'id' => $c->id, 'sesskey' => $sesskey]);
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
            new moodle_url('/local/financeservices/pages/manage_clauses.php',
                ['action' => 'manage', 'showhidden' => $showhidden ? 0 : 1]),
            $showhidden ? get_string('hidehiddenrecords', 'local_financeservices')
                        : get_string('showhiddenrecords', 'local_financeservices')
        ),
        'mt-3'
    );
}

echo $OUTPUT->footer();
