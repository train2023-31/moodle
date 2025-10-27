<?php
/**
 * Residence‑booking – Lookup‑Data Management (Card view style)
 *
 * Used as content within index.php (tab = 'lookups'), so DO NOT call
 * $OUTPUT->header() or $OUTPUT->footer() here.
 */

require_once(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context); // Admin only

$PAGE->set_url(new moodle_url('/local/residencebooking/pages/manage.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('management', 'local_residencebooking'));
$PAGE->set_heading(get_string('management', 'local_residencebooking'));

// ⚠️ Do NOT call $OUTPUT->header() or $OUTPUT->footer() here

?>

<style>
/* Quick‑and‑clean card styling (Bootstrappy) */
.rb-card-grid      {display:grid;grid-template-columns:repeat(auto-fit,minmax(350px,1fr));gap:2rem;}
.rb-card           {border:1px solid #e0e0e0;border-radius:8px;padding:2.5rem 2rem;text-align:center;}
.rb-card img       {width:64px;height:64px;margin-bottom:1rem;}
.rb-card h3        {margin:0 0 .5rem;font-size:1.5rem;}
.rb-card p         {color:#6c757d;margin-bottom:1.5rem;}
.rb-card .btn      {width:100%;}
</style>

<h2><?= get_string('management', 'local_residencebooking') ?></h2>

<div class="rb-card-grid">

    <!-- Residence Types card -->
    <div class="rb-card">
        <?= $OUTPUT->pix_icon('i/home', '', 'core', ['class' => '', 'role' => 'presentation']) ?>
        <h3><?= get_string('managetypes', 'local_residencebooking') ?></h3>
        <p><?= get_string('managetypes_desc', 'local_residencebooking') ?></p>
        <a class="btn btn-primary"
        href="<?= new moodle_url('/local/residencebooking/pages/manage_types.php') ?>">
            <?= get_string('managetypes', 'local_residencebooking') ?>
        </a>
    </div>

    <!-- Residence Purposes card -->
    <div class="rb-card">
        <?= $OUTPUT->pix_icon('i/info', '', 'core', ['class' => '', 'role' => 'presentation']) ?>
        <h3><?= get_string('managepurposes', 'local_residencebooking') ?></h3>
        <p><?= get_string('managepurposes_desc', 'local_residencebooking') ?></p>
        <a class="btn btn-success"
        href="<?= new moodle_url('/local/residencebooking/pages/manage_purposes.php') ?>">
            <?= get_string('managepurposes', 'local_residencebooking') ?>
        </a>
    </div>

</div>
