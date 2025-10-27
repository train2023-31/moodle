# Pages Directory

This directory contains the main page files for the Finance Calculator plugin.

> **Note**: This is the pages directory documentation. For comprehensive plugin documentation, see `../docs/README.md`.

## Files

- **report.php** - Main financial overview report page
- **clause_report.php** - Detailed clause spending report page

## Navigation

- The main entry point is `/local/financecalc/index.php` which provides navigation to these pages
- Direct access to reports is available via:
  - `/local/financecalc/pages/report.php` - Financial overview
  - `/local/financecalc/pages/clause_report.php` - Clause spending report

## Permissions

Both pages require the `local/financecalc:view` capability to access.

## Related Documentation

- **Main Documentation**: `../docs/README.md`
- **API Reference**: `../docs/API.md`
- **Installation Guide**: `../docs/INSTALLATION.md`
- **User Guide**: `../docs/USER_GUIDE.md`
