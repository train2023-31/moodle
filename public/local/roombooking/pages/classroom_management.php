<?php
// ============================================================================
//  Room Booking – Classroom Management Dashboard (Cards View)
//  --------------------------------------------------------------------------
//  • Adds main + sub navigation bars consistent with other manage pages.
//  • The file still works in 2 modes:
//      ① Stand-alone (accessed directly)  → shows header + main tabs + sub tabs
//      ② Included from index.php          → shows sub tabs only
// ============================================================================

defined('MOODLE_INTERNAL') || define('MOODLE_INTERNAL', false); // for CLI safety
$being_included = (defined('INCLUDED_FROM_INDEX'));             // set by index.php

if (!$being_included) {
    /* --------------------------------------------------------------
       Stand-alone bootstrap
    -------------------------------------------------------------- */
    require_once(__DIR__ . '/../../../config.php');
    require_login();

    $context = context_system::instance();
    require_capability('local/roombooking:managebookings', $context);

    $PAGE->set_url(new moodle_url('/local/roombooking/pages/classroom_management.php'));
    $PAGE->set_context($context);
    $PAGE->set_title(get_string('managementdashboard', 'local_roombooking'));
    $PAGE->set_heading(get_string('managementdashboard', 'local_roombooking'));

    /* --------------------------------------------------------------
       GLOBAL 3-tab navigation (Register | Manage Bookings | Manage)
    -------------------------------------------------------------- */
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

    echo $OUTPUT->header();
    echo $OUTPUT->tabtree($maintabs, $maintab);
}

/* --------------------------------------------------------------------------
   LOCAL 2-tab navigation (Dashboard | Rooms) – always displayed
-------------------------------------------------------------------------- */
// $subtabs = [
//     new tabobject('dashboard',
//         new moodle_url('/local/roombooking/pages/classroom_management.php'),
//         get_string('managementdashboard', 'local_roombooking')),
//     new tabobject('rooms',
//         new moodle_url('/local/roombooking/pages/manage_rooms.php'),
//         get_string('managerooms', 'local_roombooking')),
// ];
// $currentsub = 'dashboard';

// echo $OUTPUT->tabtree($subtabs, $currentsub);

/* --------------------------------------------------------------------------
   Dashboard heading + cards
-------------------------------------------------------------------------- */
echo $OUTPUT->heading(get_string('managementdashboard', 'local_roombooking'));

// Card Layout
echo html_writer::start_div('card-deck', [
    'style' => 'display:flex;gap:20px;margin-top:30px;flex-wrap:wrap;'
]);

// ────────────── Manage Rooms Card ──────────────
$roomsurl = new moodle_url('/local/roombooking/pages/manage_rooms.php');
echo html_writer::start_div('card', [
    'style' => 'flex:1 1 260px;text-align:center;padding:20px;border:1px solid #ccc;border-radius:10px;'
]);
echo html_writer::tag('div', '🚪', ['style' => 'font-size:40px;margin-bottom:10px;']);
echo html_writer::tag('h3', get_string('managerooms', 'local_roombooking'));
echo html_writer::tag('p', get_string('vieweditrooms', 'local_roombooking'));
echo html_writer::link($roomsurl, get_string('managerooms', 'local_roombooking'),
    ['class' => 'btn btn-success', 'style' => 'margin-top:10px;']);
echo html_writer::end_div();

// ────────────── Booking Reports Card (kept as-is for now) ──────────────
// $reportsurl = new moodle_url('/local/roombooking/pages/reports.php');
// echo html_writer::start_div('card', [
//     'style' => 'flex:1 1 260px;text-align:center;padding:20px;border:1px solid #ccc;border-radius:10px;'
// ]);
// echo html_writer::tag('div', '📊', ['style' => 'font-size:40px;margin-bottom:10px;']);
// echo html_writer::tag('h3', get_string('reports', 'local_roombooking'));
// echo html_writer::tag('p', get_string('generatereports', 'local_roombooking'));
// echo html_writer::link($reportsurl, get_string('reports', 'local_roombooking'),
//     ['class' => 'btn btn-info', 'style' => 'margin-top:10px;']);
// echo html_writer::end_div();

// echo html_writer::end_div(); // end card deck

/* --------------------------------------------------------------------------
   Footer (only when stand-alone)
-------------------------------------------------------------------------- */
if (!$being_included) {
    echo $OUTPUT->footer();
}
