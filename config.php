<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mariadb';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodle_new';
$CFG->dbuser    = 'root';
$CFG->dbpass    = '';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
  'dbcollation' => 'utf8mb4_unicode_ci',
);

$CFG->wwwroot   = 'http://localhost';
$CFG->dataroot  = 'C:\\xampp\\moodledata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(__DIR__ . '/lib/setup.php');


// ========================================
// ORACLE (local_oracleFetch) CONNECTION OVERRIDES
// ========================================
// Configure per-server Oracle credentials and DSN without editing plugin code.
// Replace the values below with your actual Oracle user, password, and DSN.
// Example DSN formats:
//   //db-host:1521/XEPDB1
//   //db-host:1521/ORCLPDB1
$CFG->local_oraclefetch_dbuser = 'moodleuser';
$CFG->local_oraclefetch_dbpass = 'moodle123';
//$CFG->local_oraclefetch_dsn    = '//localhost:1521/XEPDB1'; //AHMED MACHINE
$CFG->local_oraclefetch_dsn    = '//localhost:1521/XE'; //KHADIJA MACHINE

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
