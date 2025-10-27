# ORA-01790: expression must have the same datatype as corresponding expression

### Symptoms
- `php_errors` in AJAX health:
  - `oci_execute(): ORA-01790`
  - File: `local/oracleFetch/classes/oracle_manager.php` (UNION in `get_all_employees_and_persons`)
- Endpoint: `/local/oracleFetch/tools/ajax_health.php`

### Root cause
- UNION columns had mixed datatypes across servers (e.g., NVARCHAR2 vs VARCHAR2)
- Untyped `NULL` for `pf_number` in personal branch caused implicit mismatch

### Resolution implemented
- In `oracle_manager::get_all_employees_and_persons()` we evolved the fix:
  1) First, aligned datatypes across the UNION using explicit casts and a typed `NULL` for `pf_number`.
  2) Final working solution for this environment:
     - Wrap the UNION in a subquery and apply `ORDER BY` outside to avoid parser edge cases.
     - Convert all projected columns to strings using `TO_CHAR(...)` in both branches, and keep a typed `CAST(NULL AS VARCHAR2(50))` for `pf_number` in the personal branch. This guarantees consistent types without complex cast syntax.
     - Keep `UNION` (dedupe). If duplicates are acceptable, `UNION ALL` can be used for performance.

### Validation steps
1) Reload `/local/oracleFetch/tools/ajax_health.php`
2) Expect no `ORA-01790` or `ORA-00907` in `php_errors`
3) Spot-check output structure: keys `identifier`, `pf_number`, `civil_number`, `first_name`, `middle_name`, `last_name`, `tribe`

### Notes
- Robust across environments with different NLS/column types
- Client charset `AL32UTF8`; `TO_CHAR` normalization ensures consistent string output
