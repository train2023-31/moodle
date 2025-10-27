# Annual Report Block Plugin

## Overview

The Annual Report Block is a custom Moodle block plugin that displays comprehensive annual statistics for a department's training activities and financial expenditures. The plugin provides a dashboard-style view of course statistics, beneficiary counts, and budget information.

## Features

- **Internal Courses Statistics**: Display count and beneficiaries for courses designed for internal staff
- **External Courses Statistics**: Display count and beneficiaries for courses provided to external entities  
- **Financial Reporting**: Show approved, spent, and remaining amounts for budget clauses 802 and 811
- **Bilingual Support**: Full support for English and Arabic languages with automatic language switching
- **Real-time Data**: Pulls live data from the database for current year statistics
- **Responsive Design**: Modern, clean UI with responsive styling

## Plugin Information

- **Plugin Name**: Annual Report Block
- **Component**: `block_annual_report`
- **Version**: 2025071302
- **Requires**: Moodle 4.0+ (2022041900)
- **License**: GNU GPL v3 or later

## Database Dependencies

This plugin requires the following custom database tables to be present:
- `local_annual_plan_course` - Stores annual training course data
- `local_annual_plan_course_level` - Defines course levels/types (internal/external)
- `local_financeservices` - Contains financial service requests and expenditures
- `local_financeservices_clause` - Stores approved budget amounts for different clauses

## Installation

1. Copy the plugin folder to `{moodle_root}/blocks/annual_report/`
2. Login as administrator and visit Admin → Notifications
3. Follow the installation process
4. Clear caches if necessary

## Usage

1. Navigate to any page where you can add blocks
2. Turn editing on
3. Select "Add a block" → "Department Annual Report"
4. The block will automatically display current year statistics

## Supported Languages

- English (en)
- Arabic (ar)

The plugin automatically detects the user's language preference and displays appropriate content.

## File Structure

```
blocks/annual_report/
├── docs/                          # Plugin documentation
├── db/                           # Database definitions
├── lang/                         # Language files
│   ├── en/                      # English strings
│   └── ar/                      # Arabic strings
├── block_annual_report.php      # Main block class
├── version.php                  # Plugin version information
└── styles.css                   # Block styling
```

## Development

See [DEVELOPMENT.md](DEVELOPMENT.md) for detailed development guidelines and coding standards.

## File Documentation

See [FILE_STRUCTURE.md](FILE_STRUCTURE.md) for detailed explanation of each file's purpose and functionality.

## Recent Updates

### Bug Fixes
- **Fixed**: Annual report now only displays approved finance requests (status_id = 13)
- **Fixed**: Rejected and pending finance requests are properly filtered out
- **Improved**: Data integrity in financial reporting section

## Known Issues

See [Error_AHMED.txt](../Error_AHMED.txt) for resolved database schema issues and troubleshooting information.

## Support

For issues or questions regarding this plugin, please contact the development team. 