# ORA-12541: TNS:no listener

### Symptoms
- `oracle.connection.ok: false` on health page
- Error: `ORA-12541: TNS:no listener`
- PHP: `oci_connect(): ORA-12541`
- Manager methods return empty arrays/false

### Affected
- `local/oracleFetch/tools/health.php`
- `local/oracleFetch/tools/ajax_health.php`
- All AJAX endpoints using `oracle_manager`

### Root cause
- Oracle listener not reachable:
  - Wrong DSN (host/port/service)
  - Listener down on DB host
  - Firewall/DNS/network issues between servers

### Resolution implemented
- Per-server overrides in `oracle_manager::get_connection()`:
  - Read from `config.php`: `$CFG->local_oraclefetch_dbuser`, `$CFG->local_oraclefetch_dbpass`, `$CFG->local_oraclefetch_dsn`
  - Fallback to env vars: `ORACLE_DBUSER`, `ORACLE_DBPASS`, `ORACLE_DSN`
  - Final fallback to hardcoded defaults
- Example `config.php`:
```
$CFG->local_oraclefetch_dbuser = 'YOUR_USER';
$CFG->local_oraclefetch_dbpass = 'YOUR_PASS';
$CFG->local_oraclefetch_dsn    = '//DB_HOST:1521/SERVICE_NAME';
```

### Validation steps
1) Reload `/local/oracleFetch/tools/health.php?html=1`
2) Expect: `oracle.connection.ok: true`, `select_dual.ok: true`
3) If failing, verify network:
   - PowerShell: `Test-NetConnection DB_HOST -Port 1521`
   - On DB host: `lsnrctl status` shows service and listening endpoint

### Notes
- `NLS_LANG=AMERICAN_AMERICA.AL32UTF8` and client charset `AL32UTF8` are used
- Keep secrets out of source control; prefer `config.php` or environment variables
