<?php
/**
 * Manage-devices list page
 *  – global 3-tab bar  +  local 2-tab bar
 */

 require_once(__DIR__ . '/../../../config.php');

global $DB, $OUTPUT, $PAGE;

require_login();
$context = context_system::instance();
require_capability('local/computerservice:manage_devices', $context);

$PAGE->set_url(new moodle_url('/local/computerservice/pages/manage_devices.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('manage_devices', 'local_computerservice'));
$PAGE->set_heading(get_string('manage_devices', 'local_computerservice'));

/* ────────────────────────────────────────────────────────────────
   ① Global 3-tab navigation (mirrors index.php)
   ──────────────────────────────────────────────────────────────── */
$maintabs = [
    new tabobject('form',
        new moodle_url('/local/computerservice/index.php', ['tab' => 'form']),
        get_string('requestdevices', 'local_computerservice')),
    new tabobject('manage',
        new moodle_url('/local/computerservice/index.php', ['tab' => 'manage']),
        get_string('managerequests', 'local_computerservice')),
    new tabobject('devices',
        new moodle_url('/local/computerservice/index.php', ['tab' => 'devices']),
        get_string('managedevices',  'local_computerservice')),
];
$maintab = 'devices';

/* ────────────────────────────────────────────────────────────────
   ② Local 2-tab navigation
   ──────────────────────────────────────────────────────────────── */
$subtabs = [
    new tabobject('add',
        new moodle_url('/local/computerservice/pages/add_device.php'),
        get_string('add_device', 'local_computerservice')),
    new tabobject('manage',
        new moodle_url('/local/computerservice/pages/manage_devices.php'),
        get_string('manage_devices', 'local_computerservice')),
];
$currentsub = 'manage';

/* ────────────────────────────────────────────────────────────────
   Fetch and render device table
   ──────────────────────────────────────────────────────────────── */
$devices = $DB->get_records('local_computerservice_devices', null, 'id ASC');

echo $OUTPUT->header();
echo $OUTPUT->tabtree($maintabs, $maintab);   // main bar
echo $OUTPUT->tabtree($subtabs,  $currentsub); // sub-bar

// Page heading
echo html_writer::tag('h2', get_string('manage_devices', 'local_computerservice'));

$table        = new html_table();
$table->head  = [
    get_string('id',            'local_computerservice'),
    get_string('devicename_en', 'local_computerservice'),
    get_string('devicename_ar', 'local_computerservice'),
    get_string('status',        'local_computerservice'),
    get_string('actions',       'local_computerservice'),
];

// Populate each row.
foreach ($devices as $device) {
    $toggleurl   = new moodle_url('/local/computerservice/pages/toggle_device.php', ['id' => $device->id]);
    $statuslabel = $device->status === 'active'
        ? '✅ ' . get_string('active',   'local_computerservice')
        : '❌ ' . get_string('inactive', 'local_computerservice');

    $toggletext  = $device->status === 'active'
        ? get_string('deactivate', 'local_computerservice')
        : get_string('activate',   'local_computerservice');

    // Add row.
    $table->data[] = [
        $device->id,
        s($device->devicename_en),
        s($device->devicename_ar),
        $statuslabel,
        html_writer::link($toggleurl, $toggletext),
    ];
}

// Render the table.
echo html_writer::table($table);

// Footer
echo $OUTPUT->footer();
