<?php
// ============================================================================
//  Room Booking – Manage Rooms (with soft-delete)
//  --------------------------------------------------------------------------
//  • Uses a ‘deleted’ flag instead of physical delete.
//  • Main 3-tab bar  : Register | Manage Bookings | Manage
//  • Local 2-tab bar : Dashboard | Rooms
// ============================================================================

require_once(__DIR__ . '/../../../config.php');

use local_roombooking\form\room_form;

require_login();
$context = context_system::instance();
require_capability('local/roombooking:managerooms', $context);

// ---------------------------------------------------------------------------
// Input parameters
// ---------------------------------------------------------------------------
$action     = optional_param('action',     '',  PARAM_ALPHA); // add | edit | hide | restore
$id         = optional_param('id',          0,  PARAM_INT);
$showhidden = optional_param('showhidden',  0,  PARAM_BOOL);  // 1 = list hidden too
$sesskey    = sesskey();

// ---------------------------------------------------------------------------
// Moodle page bootstrap
// ---------------------------------------------------------------------------
$PAGE->set_url(new moodle_url('/local/roombooking/pages/manage_rooms.php',
    ['action' => $action, 'id' => $id, 'showhidden' => $showhidden]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('managerooms', 'local_roombooking'));
$PAGE->set_heading(get_string('managerooms', 'local_roombooking'));

// ---------------------------------------------------------------------------
// ① GLOBAL 3-tab navigation
// ---------------------------------------------------------------------------
$maintabs = [
    new tabobject('registeringroom',
        new moodle_url('/local/roombooking/index.php', ['tab' => 'registeringroom']),
        get_string('registeringroom', 'local_roombooking')),
    new tabobject('managebookings',
        new moodle_url('/local/roombooking/index.php', ['tab' => 'managebookings']),
        get_string('managebookings', 'local_roombooking')),
    new tabobject('manage',
        new moodle_url('/local/roombooking/index.php', ['tab' => 'manage']),
        get_string('manage', 'local_roombooking')),
];
$maintab = 'manage';

// ---------------------------------------------------------------------------
// ② LOCAL sub-tab navigation
// ---------------------------------------------------------------------------
$subtabs = [
    new tabobject('dashboard',
        new moodle_url('/local/roombooking/pages/classroom_management.php'),
        get_string('managementdashboard', 'local_roombooking')),
    new tabobject('rooms',
        new moodle_url('/local/roombooking/pages/manage_rooms.php'),
        get_string('managerooms', 'local_roombooking')),
];
$currentsub = 'rooms';

// ---------------------------------------------------------------------------
// Output header + nav bars
// ---------------------------------------------------------------------------
echo $OUTPUT->header();
echo $OUTPUT->tabtree($maintabs, $maintab);
echo $OUTPUT->tabtree($subtabs, $currentsub);
echo $OUTPUT->heading(get_string('managerooms', 'local_roombooking'));

// ---------------------------------------------------------------------------
// Helper function – show a blocking notice then bail out
// ---------------------------------------------------------------------------
function rbk_blocking_notice(string $langkey) {
    global $OUTPUT, $PAGE;
    echo $OUTPUT->notification(get_string($langkey, 'local_roombooking'), 'notifyproblem');
    echo $OUTPUT->continue_button($PAGE->url->out(false));
    echo $OUTPUT->footer();
    exit;
}

global $DB;

/* ════════════════════════════════════════════════════════════════════════ */
/*  ACTION ROUTER                                                           */
/* ════════════════════════════════════════════════════════════════════════ */
switch ($action) {

    /* --------------------------------------------------------------------
       HIDE  ➜  Soft-delete (sets deleted = 1)
    -------------------------------------------------------------------- */
    case 'hide':
        require_sesskey();

        // Don’t allow hide if room already hidden
        if (!$DB->record_exists('local_roombooking_rooms', ['id' => $id, 'deleted' => 0])) {
            rbk_blocking_notice('norooms'); // “Room not found or already hidden”
        }

        $DB->set_field('local_roombooking_rooms', 'deleted', 1, ['id' => $id]);

        redirect(
            new moodle_url('/local/roombooking/pages/manage_rooms.php'),
            get_string('roomdeleted', 'local_roombooking'),      // reuse existing success string
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
        break;

    /* --------------------------------------------------------------------
       RESTORE  ➜  Make the room visible again (deleted = 0)
    -------------------------------------------------------------------- */
    case 'restore':
        require_sesskey();

        if (!$DB->record_exists('local_roombooking_rooms', ['id' => $id, 'deleted' => 1])) {
            rbk_blocking_notice('norooms');
        }

        $DB->set_field('local_roombooking_rooms', 'deleted', 0, ['id' => $id]);

        redirect(
            new moodle_url('/local/roombooking/pages/manage_rooms.php', ['showhidden' => 1]),
            get_string('changessaved'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
        break;

    /* --------------------------------------------------------------------
       ADD / EDIT  ➜  Display and process the mform
    -------------------------------------------------------------------- */
    case 'add':
    case 'edit':
        $formurl = new moodle_url('/local/roombooking/pages/manage_rooms.php',
            ['action' => $action, 'id' => $id]);

        $mform = new room_form($formurl);

        // Pre-load data when editing
        if ($action === 'edit' && $id) {
            $room = $DB->get_record('local_roombooking_rooms', ['id' => $id], '*', MUST_EXIST);
            $mform->set_data($room);
        }

        if ($mform->is_cancelled()) {
            redirect(new moodle_url('/local/roombooking/pages/manage_rooms.php'));

        } elseif ($data = $mform->get_data()) {

            if ($data->id) {                 // UPDATE
                $DB->update_record('local_roombooking_rooms', $data);
                $msgkey = 'roomupdated';
            } else {                         // INSERT
                $data->deleted = 0;          // brand-new rooms are visible
                $DB->insert_record('local_roombooking_rooms', $data);
                $msgkey = 'roomadded';
            }

            redirect(
                new moodle_url('/local/roombooking/pages/manage_rooms.php'),
                get_string($msgkey, 'local_roombooking'),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
        }

        /* ---------- Form heading + display ---------- */
        echo $OUTPUT->heading(
            $action === 'edit'
                ? get_string('editroom', 'local_roombooking')
                : get_string('addroom',  'local_roombooking'),
            3
        );
        $mform->display();
        echo $OUTPUT->footer();
        exit; // Stop further output – list view isn’t shown while form is up

    /* --------------------------------------------------------------------
       DEFAULT  ➜  Room list
    -------------------------------------------------------------------- */
    default:
        // Build WHERE clause depending on $showhidden
        $select = $showhidden ? null : ['deleted' => 0];
        $rooms  = $DB->get_records('local_roombooking_rooms', $select, 'name');

        /* ---------- “Add new” button ---------- */
        echo $OUTPUT->single_button(
            new moodle_url('/local/roombooking/pages/manage_rooms.php', ['action' => 'add']),
            get_string('addroom', 'local_roombooking'),
            'get',
            ['class' => 'btn btn-primary mb-3']
        );

        /* ---------- Toggle “show hidden” ---------- */
        $toggleurl = new moodle_url('/local/roombooking/pages/manage_rooms.php',
            ['showhidden' => $showhidden ? 0 : 1]);
        $toggletxt = $showhidden
            ? get_string('showonlyvisible', 'local_roombooking')
            : get_string('showhidden',       'local_roombooking');

        echo html_writer::div(
            html_writer::link($toggleurl, $toggletxt),
            'mb-4'
        );

        /* ---------- Table rendering ---------- */
        if (!$rooms) {
            echo $OUTPUT->notification(get_string('norooms', 'local_roombooking'), 'notifyproblem');
            echo $OUTPUT->footer();
            exit;
        }

        $table = new html_table();
        $table->head = [
            get_string('roomname',    'local_roombooking'),
            get_string('capacity',    'local_roombooking'),
            get_string('roomtype',    'local_roombooking'),
            get_string('location',    'local_roombooking'),
            get_string('description', 'local_roombooking'),
            get_string('actions',     'local_roombooking')
        ];

        foreach ($rooms as $r) {
            // Label for room type
            $typekey   = in_array($r->roomtype, ['fixed', 'dynamic']) ? $r->roomtype : 'invalidroomtype';
            $typelabel = get_string($typekey, 'local_roombooking');

            // Action links
            $editurl = new moodle_url('/local/roombooking/pages/manage_rooms.php',
                ['action' => 'edit',    'id' => $r->id]);
            $hideurl = new moodle_url('/local/roombooking/pages/manage_rooms.php',
                ['action' => 'hide',    'id' => $r->id, 'sesskey' => $sesskey]);
            $resturl = new moodle_url('/local/roombooking/pages/manage_rooms.php',
                ['action' => 'restore', 'id' => $r->id, 'sesskey' => $sesskey]);

            $actions = html_writer::link($editurl, get_string('edit'));

            if (!$r->deleted) {
                // Visible row → offer Hide
                $actions .= ' | '.html_writer::link(
                    $hideurl,
                    get_string('hide'),
                    ['onclick' => "return confirm('".
                        get_string('deletebookingconfirm','local_roombooking')."');"]
                );
            } else {
                // Hidden row → offer Restore
                $actions .= ' | '.html_writer::link($resturl, get_string('restore'));
            }

            // Dim hidden rows for clarity
            $rowclass = $r->deleted ? ['class' => 'dimmed_text'] : [];

            $table->data[] = new html_table_row([
                format_string($r->name),
                $r->capacity,
                $typelabel,
                format_string($r->location),
                format_text($r->description),
                $actions
            ], $rowclass);
        }

        echo html_writer::table($table);
        echo $OUTPUT->footer();
}
