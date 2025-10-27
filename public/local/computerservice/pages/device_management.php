<?php
require_once(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();

$PAGE->set_url(new moodle_url('/local/computerservice/pages/device_management.php'));
$PAGE->set_context($context);

// Use language string for both page title and heading.
$PAGE->set_title(get_string('device_management', 'local_computerservice'));
$PAGE->set_heading(get_string('device_management', 'local_computerservice'));

// NOTE: This file is included by index.php, so we DO NOT output header/footer here.

/**
 * Outputs the “Manage Devices” tab with two cards:
 *   • Add Device
 *   • Manage Devices
 * Each card links to the correct sub-page.
 */
function display_device_management() {
    echo '<div class="container">';
    echo '<div class="row mt-4">';

    // ───── Manage Devices Card ─────
    echo '<div class="col-md-6">';
    echo '<div class="card shadow-sm">';
    echo '<div class="card-body text-center">';
    echo '<h4><i class="fa fa-cogs text-primary"></i> ' . get_string('manage_devices', 'local_computerservice') . '</h4>';
    echo '<p class="text-muted">' . get_string('manage_devices_desc', 'local_computerservice') . '</p>';
    echo '<a href="' . new moodle_url('/local/computerservice/pages/manage_devices.php') . '" class="btn btn-primary btn-lg">';
    echo '<i class="fa fa-wrench"></i> ' . get_string('manage_devices', 'local_computerservice') . '</a>';
    echo '</div></div></div>';

    // ───── Add Device Card ─────
    echo '<div class="col-md-6">';
    echo '<div class="card shadow-sm">';
    echo '<div class="card-body text-center">';
    echo '<h4><i class="fa fa-plus text-success"></i> ' . get_string('add_device', 'local_computerservice') . '</h4>';
    echo '<p class="text-muted">' . get_string('add_device_desc', 'local_computerservice') . '</p>';
    echo '<a href="' . new moodle_url('/local/computerservice/pages/add_device.php') . '" class="btn btn-success btn-lg">';
    echo '<i class="fa fa-plus-circle"></i> ' . get_string('add_device', 'local_computerservice') . '</a>';
    echo '</div></div></div>';

    echo '</div>'; // End Row
    echo '</div>'; // End Container
}

display_device_management();
