# Finance Calculator Plugin for Moodle

A Moodle local plugin that provides comprehensive financial reporting by aggregating data from Finance Services and Participant plugins.

## Documentation Structure

This plugin has multiple README files, each serving a specific purpose:

- **`README.md`** (this file) - Quick overview and getting started guide
- **`docs/README.md`** - Comprehensive technical documentation
- **`pages/README.md`** - Documentation for the pages directory structure

This structure allows users to quickly understand the plugin (main README) while providing detailed information for developers and advanced users (docs directory).

## Features

- **Financial Overview**: View spending vs budget by year
- **Multi-Source Data**: Combines data from Finance Services and Participant plugins
- **Real-time Calculations**: Live calculations with optional caching
- **Filtering**: Filter by year for detailed analysis
- **Scheduled Updates**: Automatic data refresh via scheduled tasks
- **Role-based Access**: Configurable permissions for viewing and managing

## Data Sources

### Finance Services Plugin
- **Budget**: `local_financeservices_clause.amount` (grouped by `clause_year`)
- **Spending**: `local_financeservices.price_requested` (approved requests only, status_id = 13)

### Participant Plugin
- **Spending**: `local_participant_requests.compensation_amount` or `duration_amount * request_types.cost`
- **Approval**: Only approved requests (`is_approved = 1`)

## Installation

1. Copy the `financecalc` folder to your Moodle `local/` directory
2. Visit Site administration > Notifications to install the plugin
3. Configure capabilities in Site administration > Users > Permissions > Define roles

## Usage

### Accessing the Report
- Navigate to Site administration > Reports > Financial Report
- Or visit `/local/financecalc/pages/report.php` directly

### Permissions
- **View**: `local/financecalc:view` - Required to view financial reports
- **Manage**: `local/financecalc:manage` - Required to refresh cached data

### Filtering
- Use the year filter to view data for specific years
- Select "All Years" to see comprehensive data

### Data Refresh
- Managers can manually refresh data using the "Refresh Data" button
- Automatic refresh occurs via scheduled task (configurable in Site administration > Server > Scheduled tasks)

## File Structure

```
local/financecalc/
├── README.md                     # Quick overview (this file)
├── index.php                     # Main entry point
├── pages/                        # Main page files
│   ├── report.php                # Financial overview report
│   ├── clause_report.php         # Clause spending report
│   └── README.md                 # Pages documentation
├── docs/                         # Detailed documentation
│   ├── README.md                 # Comprehensive documentation
│   ├── API.md                    # API reference
│   ├── INSTALLATION.md           # Installation guide
│   ├── USER_GUIDE.md             # User guide
│   └── CHANGELOG.md              # Version history
├── classes/                      # Core classes
├── forms/                        # Form definitions
├── output/                       # Output renderers
├── tasks/                        # Scheduled tasks
├── lang/                         # Language files
└── db/                          # Database schema
```

## Database Schema

The plugin creates one table:

```sql
local_financecalc_yearly
├── id (primary key)
├── year (unique, not null)
├── spending_omr (decimal 12,2)
├── budget_omr (decimal 12,2)
├── timecreated (timestamp)
├── timemodified (timestamp)
├── created_by (user id)
└── modified_by (user id)
```

## Technical Details

### SQL Queries
The plugin uses complex SQL with CTEs (Common Table Expressions) to:
1. Calculate budget from finance services clauses
2. Sum approved spending from finance services
3. Calculate participant spending (compensation or duration × cost)
4. Combine all sources by year

### Caching Strategy
- Data can be cached for performance
- Cache is refreshed via scheduled task or manual refresh
- Falls back to live calculation if cache is empty

### Dependencies
- Requires `local_financeservices` plugin
- Requires `local_participant` plugin
- Compatible with Moodle 4.1+

## Troubleshooting

### No Data Displayed
1. Check that Finance Services and Participant plugins are installed
2. Verify that approved requests exist in both systems
3. Ensure user has proper capabilities

### Performance Issues
1. Enable caching by running the scheduled task
2. Check database indexes on related tables
3. Consider filtering by specific years for large datasets

### Permission Errors
1. Verify user has `local/financecalc:view` capability
2. Check role assignments in Site administration > Users > Permissions

## Development

### Adding New Data Sources
1. Extend the `data_manager::calculate_financial_data()` method
2. Add new CTEs to the SQL query
3. Update the cache refresh logic

### Customizing Display
1. Modify `output/financial_table.php` for table formatting
2. Update language strings in `lang/en/local_financecalc.php`
3. Customize the report page in `pages/report.php`

## License

This plugin is licensed under the GNU General Public License v3.0.

## Documentation

For detailed documentation, see the `docs/` directory:
- `docs/README.md` - Comprehensive plugin documentation
- `docs/API.md` - API reference for developers
- `docs/INSTALLATION.md` - Detailed installation guide
- `docs/USER_GUIDE.md` - User guide and instructions
- `docs/CHANGELOG.md` - Version history and changes

## Version Information

- **Current Version**: 1.0.1 (2025082801)
- **Moodle Compatibility**: 4.1+
- **Last Updated**: January 2025

## Support

For issues and feature requests, please contact your development team.
