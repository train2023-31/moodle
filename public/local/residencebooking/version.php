<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Residence‑booking plugin version.
 *
 * 2025050201 – First public workflow integration:
 *   • Stop writing the old char `status` column.
 *   • Every new request stores a `status_id` that points to local_status.
 *   • UI now reads the status name from local_status.
 * 
 * 2025051200 – Soft-delete support:
 *   • Added 'deleted' flag to types and purposes tables.
 *   • UI now supports hiding/restoring instead of permanent deletion.
 * 
 * 2025051201 – Multilingual field support:
 *   • Added separate English and Arabic fields for types and purposes.
 *   • Added proper indexes for performance.
 *   • Kept legacy fields for backward compatibility.
 * 
 * 2025051202 – Remove legacy fields:
 *   • Removed old single-language 'type_name' and 'description' fields.
 *   • All code updated to use only the multilingual fields.
 *   • Final step in migration to full multilingual support.
 * 
 * 2025061700 – Add approval_note field & update workflow:
 *   • Added approval_note field for simple workflow manager.
 *   • Updated workflow to use simple hardcoded approach like room booking.
 *   • Updated to use local_status workflow capabilities instead of custom ones.
 */
$plugin->component = 'local_residencebooking';
$plugin->version   = 2025082500;   // ← bumped to trigger upgrade.php
$plugin->requires  = 2022041900;
$plugin->maturity  = MATURITY_ALPHA;
$plugin->release   = '0.6-audit-fields';


