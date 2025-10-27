# Finance Calculator Plugin Documentation

# Finance Calculator Plugin - Comprehensive Documentation

## Overview

The **Finance Calculator** (`local_financecalc`) is a Moodle local plugin that provides comprehensive financial reporting and analysis by aggregating budget and spending data from multiple sources within your Moodle installation.

> **Note**: This is the comprehensive documentation. For a quick overview, see the main `README.md` file in the plugin root.

## Features

- **📊 Financial Overview**: Aggregate financial data from multiple plugins
- **💰 Budget Tracking**: Monitor budget vs. actual spending
- **📈 Yearly Reports**: View financial data by year
- **🔄 Hybrid Calculation**: Live calculation with optional caching for performance
- **🌍 Multi-language**: Support for English and Arabic
- **⚡ Scheduled Updates**: Automatic data refresh via cron jobs
- **🔒 Role-based Access**: Granular permissions for viewing and managing

## Data Sources

The plugin aggregates data from two primary sources:

### 1. Finance Services (`local_financeservices`)
- **Budget**: `local_financeservices_clause.amount` (summed by `clause_year`)
- **Spending**: `local_financeservices.price_requested` (approved requests only, status_id = 13)
- **Year Anchor**: `local_financeservices_clause.clause_year`

### 2. Participant Requests (`local_participant`)
- **Spending**: `COALESCE(compensation_amount, duration_amount * cost)` (approved requests only)
- **Year Anchor**: `local_annual_plan.year` via `annual_plan_id`
- **Status**: `local_participant_requests.is_approved = 1`

## Installation

### Prerequisites
- Moodle 4.1+ (requires 2022112800)
- PHP 7.4+
- MySQL/MariaDB
- Access to `local_financeservices` and `local_participant` plugins

### Installation Steps

1. **Download/Clone the Plugin**
   ```bash
   # Navigate to your Moodle local directory
   cd /path/to/moodle/local/
   
   # Clone or copy the plugin files
   git clone [repository-url] financecalc
   # OR copy files manually to local/financecalc/
   ```

2. **Install via Moodle Admin**
   - Go to **Site administration > Notifications**
   - Follow the installation prompts
   - The plugin will create necessary database tables

3. **Verify Installation**
   - Check **Site administration > Plugins > Local plugins**
   - Verify `Finance Calculator` appears in the list
   - Navigate to **Site administration > Reports > Financial Overview**

### Manual Installation (if needed)

If automatic installation fails:

1. **Create Database Tables**
   ```sql
   -- The plugin will create this table automatically
   -- Manual creation if needed:
   CREATE TABLE IF NOT EXISTS mdl_local_financecalc_yearly (
       id INT(10) NOT NULL AUTO_INCREMENT,
       year INT(4) NOT NULL,
       spending_omr DECIMAL(12,2) NOT NULL DEFAULT 0,
       budget_omr DECIMAL(12,2) NOT NULL DEFAULT 0,
       timecreated INT(10) NOT NULL,
       timemodified INT(10) NOT NULL,
       created_by INT(10) NOT NULL,
       modified_by INT(10) NOT NULL,
       PRIMARY KEY (id),
       UNIQUE KEY year (year),
       INDEX yearidx (year),
       INDEX timecreatedidx (timecreated)
   );
   ```

2. **Update Moodle Version**
   - Go to **Site administration > Notifications**
   - Complete the version upgrade process

## Usage

### Accessing the Financial Overview

1. **Navigate to the Report**
   - **Site administration > Reports > Financial Overview**
   - OR direct URL: `/local/financecalc/pages/report.php`

2. **Understanding the Interface**
   - **Annual Plan Year**: The fiscal year
   - **Spending (OMR)**: Total approved spending from both sources
   - **Budget (OMR)**: Total budget allocated for the year
   - **Balance (OMR)**: Budget minus spending (green = positive, red = negative)

### Using Filters

1. **Year Filter**
   - Select a specific year from the dropdown
   - Choose "All Years" to view all available data
   - Click "Filter" to apply

2. **Refresh Data**
   - Click "Refresh Data" to recalculate from source tables
   - Useful after adding new financial data
   - Requires "Manage finance calculations" permission

### Permissions

#### Viewing Reports
- **Capability**: `local/financecalc:view`
- **Default Roles**: Manager, Course Creator
- **Access**: View financial overview and reports

#### Managing Data
- **Capability**: `local/financecalc:manage`
- **Default Roles**: Manager only
- **Access**: Refresh data, manage cache, configure settings

## Technical Details

### Database Schema

#### Cache Table: `local_financecalc_yearly`
```sql
CREATE TABLE mdl_local_financecalc_yearly (
    id INT(10) NOT NULL AUTO_INCREMENT,
    year INT(4) NOT NULL,
    spending_omr DECIMAL(12,2) NOT NULL DEFAULT 0,
    budget_omr DECIMAL(12,2) NOT NULL DEFAULT 0,
    timecreated INT(10) NOT NULL,
    timemodified INT(10) NOT NULL,
    created_by INT(10) NOT NULL,
    modified_by INT(10) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY year (year),
    INDEX yearidx (year),
    INDEX timecreatedidx (timecreated)
);
```

### Core SQL Queries

#### All Years Query (CTE-based)
```sql
WITH budget AS (
    SELECT c.clause_year AS year, SUM(c.amount) AS budget_omr
    FROM {local_financeservices_clause} c
    WHERE c.deleted = 0
    GROUP BY c.clause_year
),
fin_spend AS (
    SELECT c.clause_year AS year, SUM(f.price_requested) AS spending_fin_omr
    FROM {local_financeservices} f
    JOIN {local_financeservices_clause} c ON c.id = f.clause_id
    WHERE f.status_id = 13
    GROUP BY c.clause_year
),
part_spend AS (
    SELECT ap.year AS year,
           SUM(COALESCE(r.compensation_amount, (r.duration_amount * t.cost))) AS spending_part_omr
    FROM {local_participant_requests} r
    JOIN {local_annual_plan} ap ON ap.id = r.annual_plan_id
    LEFT JOIN {local_participant_request_types} t ON t.id = r.participant_type_id
    WHERE r.is_approved = 1
    GROUP BY ap.year
)
SELECT y.year,
       (COALESCE(f.spending_fin_omr, 0) + COALESCE(p.spending_part_omr, 0)) AS spending_omr,
       COALESCE(b.budget_omr, 0) AS budget_omr
FROM years y
LEFT JOIN budget b ON b.year = y.year
LEFT JOIN fin_spend f ON f.year = y.year
LEFT JOIN part_spend p ON p.year = y.year
ORDER BY y.year DESC;
```

#### Single Year Query (Subquery-based)
```sql
SELECT 
    :year AS year,
    COALESCE((
        SELECT SUM(f.price_requested)
        FROM {local_financeservices} f
        JOIN {local_financeservices_clause} c ON c.id = f.clause_id
        WHERE f.status_id = 13 AND c.clause_year = :year
    ), 0) + COALESCE((
        SELECT SUM(COALESCE(r.compensation_amount, (r.duration_amount * t.cost)))
        FROM {local_participant_requests} r
        JOIN {local_annual_plan} ap ON ap.id = r.annual_plan_id
        LEFT JOIN {local_participant_request_types} t ON t.id = r.participant_type_id
        WHERE r.is_approved = 1 AND ap.year = :year
    ), 0) AS spending_omr,
    COALESCE((
        SELECT SUM(c.amount)
        FROM {local_financeservices_clause} c
        WHERE c.deleted = 0 AND c.clause_year = :year
    ), 0) AS budget_omr;
```

### File Structure

```
local/financecalc/
├── classes/
│   └── data_manager.php          # Core data calculation logic
├── forms/
│   └── filter_form.php           # Year filter form
├── output/
│   └── financial_table.php       # Table rendering
├── tasks/
│   └── refresh_financial_data.php # Scheduled task
├── lang/
│   ├── en/
│   │   └── local_financecalc.php # English strings
│   └── ar/
│       └── local_financecalc.php # Arabic strings
├── db/
│   ├── install.xml               # Database schema
│   └── access.php                # Capabilities
├── docs/                         # Documentation directory
│   ├── README.md                 # This comprehensive documentation
│   ├── API.md                    # API reference for developers
│   ├── INSTALLATION.md           # Detailed installation guide
│   ├── USER_GUIDE.md             # User guide and instructions
│   └── CHANGELOG.md              # Version history and changes
├── pages/                        # Main page files
│   ├── report.php                # Main report page
│   ├── clause_report.php         # Clause spending report page
│   └── README.md                 # Pages directory documentation
├── classes.php                   # Autoloader
├── lib.php                       # Navigation hooks
├── settings.php                  # Admin settings
├── version.php                   # Plugin metadata
├── index.php                     # Main entry point
├── test_queries.php              # Testing script
└── README.md                     # Quick overview (main README)
```

### Caching Strategy

The plugin uses a hybrid approach:

1. **Live Calculation**: Default mode when cache table doesn't exist
2. **Cached Mode**: When cache table exists, data is pre-calculated
3. **Auto-refresh**: Scheduled task updates cache nightly
4. **Manual Refresh**: Admin can force refresh via UI
5. **Fallback**: If cache fails, falls back to live calculation

### Performance Considerations

- **Large Datasets**: Use caching for better performance
- **Frequent Updates**: Consider disabling cache for real-time data
- **Memory Usage**: CTE queries may use more memory for large datasets
- **Indexing**: Ensure proper indexes on year fields in source tables

## Configuration

### Admin Settings

Navigate to **Site administration > Plugins > Local plugins > Finance Calculator**

#### Available Settings
- **Enable Caching**: Toggle between live and cached modes
- **Refresh Interval**: How often to run the scheduled task
- **Default Year**: Default year filter for reports

### Scheduled Tasks

#### Automatic Data Refresh
- **Task**: `Refresh financial calculation data`
- **Frequency**: Daily (configurable)
- **Purpose**: Updates cached data from source tables
- **Dependencies**: Requires `local_financeservices` and `local_participant` plugins

#### Manual Configuration
```php
// In Site administration > Server > Scheduled tasks
// Find "Refresh financial calculation data"
// Set to "Daily" or "Weekly" as needed
```

## Troubleshooting

### Common Issues

#### 1. "No financial data available"
**Cause**: No data in source tables or incorrect permissions
**Solution**:
- Verify data exists in `local_financeservices` and `local_participant`
- Check user permissions (`local/financecalc:view`)
- Ensure source plugins are installed and active

#### 2. "Error refreshing financial data"
**Cause**: Database issues or missing tables
**Solution**:
- Check database permissions
- Verify cache table exists: `SHOW TABLES LIKE 'local_financecalc_yearly'`
- Check Moodle error logs for specific errors

#### 3. "Class not found" errors
**Cause**: Autoloader issues
**Solution**:
- Verify `classes.php` file exists and is correct
- Clear Moodle cache: **Site administration > Development > Purge all caches**
- Check file permissions

#### 4. SQL Parameter Errors
**Cause**: Query parameter mismatch
**Solution**:
- Check source table schemas match expected structure
- Verify year fields exist and are properly indexed
- Test queries manually in database

#### 5. Scheduled Task Dependencies Missing
**Cause**: Required plugins not installed
**Solution**:
- Ensure `local_financeservices` plugin is installed
- Ensure `local_participant` plugin is installed
- Check scheduled task logs for dependency warnings

### Debug Mode

Enable debugging to see detailed error messages:

1. **Site administration > Development > Debugging**
2. **Set Debug messages to "DEVELOPER"**
3. **Check error logs for specific issues**

### Manual Testing

Use the test script to verify data sources:

```bash
# Run the test script
php local/financecalc/test_queries.php
```

This will:
- Check table existence
- Test individual queries
- Verify data aggregation
- Show sample results

## Development

### Adding New Data Sources

1. **Modify `data_manager.php`**
   - Add new CTE to the main query
   - Update single-year query
   - Add to `get_all_financial_years()`

2. **Update Language Strings**
   - Add new column headers
   - Update filter options
   - Add error messages

3. **Test Thoroughly**
   - Use test script
   - Verify with different year filters
   - Check performance impact

### Customizing the Interface

#### Adding New Filters
1. Modify `filter_form.php`
2. Update `data_manager.php` to handle new parameters
3. Add language strings
4. Update table rendering

#### Customizing Display
1. Modify `financial_table.php`
2. Add new columns or formatting
3. Update CSS for styling

### API Usage

#### Programmatic Access
```php
// Get financial data for specific year
$data = \local_financecalc\data_manager::get_financial_data(2025);

// Get all years data
$alldata = \local_financecalc\data_manager::get_financial_data(0);

// Refresh cache
$success = \local_financecalc\data_manager::refresh_cached_data();

// Get detailed breakdown
$breakdown = \local_financecalc\data_manager::get_detailed_breakdown(2025);
```

## Support

### Getting Help

1. **Check this documentation** for common solutions
2. **Review error logs** in Moodle admin
3. **Test with debug mode** enabled
4. **Verify data sources** are working correctly

### Reporting Issues

When reporting issues, include:
- Moodle version
- Plugin version
- Error messages
- Steps to reproduce
- Sample data (if applicable)

### Contributing

1. **Fork the repository**
2. **Create a feature branch**
3. **Make your changes**
4. **Test thoroughly**
5. **Submit a pull request**

## License

This plugin is licensed under the GNU General Public License v3.0.

## Version History

### Version 2025082800
- Initial release
- Basic financial aggregation
- English and Arabic support
- Hybrid caching system
- Scheduled tasks
- Admin interface
- Dependency checking

---

**Last Updated**: January 2025  
**Compatible with**: Moodle 4.1+  
**Author**: Your Name  
**License**: GNU GPL v3
