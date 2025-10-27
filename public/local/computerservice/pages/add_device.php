<?php
/**
 * Add-device page
 *  – shows the global 3-tab navigation first
 *  – then a local 2-tab bar (Add / Manage)
 */

// NOTE: require path goes three levels up now (pages → plugin → local → root)
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/computerservice/classes/form/device_form.php');

global $DB, $OUTPUT, $PAGE;

// ─ Setup / permissions ──────────────────────────────────────────────
require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url(new moodle_url('/local/computerservice/pages/add_device.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('add_device', 'local_computerservice'));
$PAGE->set_heading(get_string('add_device', 'local_computerservice'));

/* ────────────────────────────────────────────────────────────────
   ① Global 3-tab navigation (same as index.php)
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
$maintab = 'devices';   // highlight "Manage Devices"

/* ────────────────────────────────────────────────────────────────
   ② Local 2-tab navigation (within Manage Devices)
   ──────────────────────────────────────────────────────────────── */
$subtabs = [
    new tabobject('add',
        new moodle_url('/local/computerservice/pages/add_device.php'),
        get_string('add_device', 'local_computerservice')),
    new tabobject('manage',
        new moodle_url('/local/computerservice/pages/manage_devices.php'),
        get_string('manage_devices', 'local_computerservice')),
];
$currentsub = 'add';

/* ────────────────────────────────────────────────────────────────
   Form handling
   ──────────────────────────────────────────────────────────────── */
$mform = new device_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/computerservice/pages/manage_devices.php'));

} elseif ($data = $mform->get_data()) {
    // Build record – we no longer touch the legacy column.
    $record              = new stdClass();
    $record->devicename_en = trim($data->devicename_en);
    $record->devicename_ar = trim($data->devicename_ar);
    $record->status        = 'active';

    $DB->insert_record('local_computerservice_devices', $record);

    redirect(
        new moodle_url('/local/computerservice/pages/manage_devices.php'),
        get_string('device_added', 'local_computerservice'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

/* ────────────────────────────────────────────────────────────────
   Output page
   ──────────────────────────────────────────────────────────────── */
echo $OUTPUT->header();
echo $OUTPUT->tabtree($maintabs, $maintab);   // main bar
echo $OUTPUT->tabtree($subtabs,  $currentsub); // sub-bar
$mform->display();
echo $OUTPUT->footer();
