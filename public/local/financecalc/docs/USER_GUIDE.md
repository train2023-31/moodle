# User Guide - Finance Calculator Plugin

## Introduction

Welcome to the **Finance Calculator** plugin for Moodle! This guide will help you understand and use the financial reporting features to track budgets and spending across your organization.

> **Note**: This is the detailed user guide. For a quick overview, see the main `README.md` file in the plugin root.

## What is the Finance Calculator?

The Finance Calculator is a powerful tool that:

- **📊 Aggregates financial data** from multiple sources in your Moodle system
- **💰 Tracks budgets vs. actual spending** by year
- **📈 Provides clear financial overviews** with easy-to-understand reports
- **🔄 Updates automatically** to keep your data current
- **🌍 Supports multiple languages** including English and Arabic

## Getting Started

### Accessing the Financial Overview

1. **Log into Moodle** with an account that has financial reporting permissions
2. **Navigate to Site administration** (usually in the top navigation)
3. **Click on "Reports"** in the left sidebar
4. **Select "Financial Overview"** from the reports list

You should now see the Financial Overview page with your financial data displayed in a table format.

> **Alternative Access**: You can also access the reports directly via:
> - Financial Overview: `/local/financecalc/pages/report.php`
> - Clause Spending Report: `/local/financecalc/pages/clause_report.php`

### Understanding the Interface

The Financial Overview page contains several key elements:

#### 📋 Main Data Table
- **Annual Plan Year**: The fiscal year for the data
- **Spending (OMR)**: Total approved spending from all sources
- **Budget (OMR)**: Total budget allocated for that year
- **Balance (OMR)**: Remaining budget (Budget - Spending)

#### 🎛️ Control Panel
- **Refresh Data Button**: Updates the financial data from source systems (managers only)
- **Year Filter**: Select a specific year or view all years
- **Filter Button**: Apply the selected year filter

#### 📊 Visual Indicators
- **Green Balance**: Positive balance (spending under budget)
- **Red Balance**: Negative balance (spending over budget)
- **Last Updated**: Shows when the data was last refreshed (if available)

## Using the Financial Overview

### Viewing All Years

1. **Ensure "All Years" is selected** in the year filter dropdown
2. **Click "Filter"** to apply
3. **Review the data** - you'll see all available years with their financial information

### Filtering by Specific Year

1. **Select a specific year** from the dropdown (e.g., "2025")
2. **Click "Filter"** to apply
3. **View the detailed data** for that specific year

### Refreshing Data

**When to refresh:**
- After adding new financial data to source systems
- When you want to ensure you have the latest information
- If you notice data seems outdated

**How to refresh:**
1. **Click the "Refresh Data" button** (only visible to managers)
2. **Wait for the confirmation message**
3. **Review the updated data**

**Note:** You need "Manage finance calculations" permission to refresh data.

## Understanding Your Financial Data

### Data Sources

The Finance Calculator pulls data from two main sources:

#### 1. Finance Services
- **Budget**: Money allocated for finance services
- **Spending**: Approved finance service requests
- **Status**: Only approved requests (status 13) are included

#### 2. Participant Requests
- **Spending**: Approved participant compensation and costs
- **Calculation**: Uses either direct compensation or duration × cost rate
- **Status**: Only approved requests are included

### Reading the Numbers

#### Example Data Row:
```
Year: 2025
Spending: 53.00 OMR
Budget: 1,500.00 OMR
Balance: 1,447.00 OMR (green)
```

**What this means:**
- In 2025, you had a budget of 1,500 OMR
- You spent 53 OMR on approved requests
- You have 1,447 OMR remaining (positive balance)
- The green color indicates you're under budget

#### Negative Balance Example:
```
Year: 2024
Spending: 2,000.00 OMR
Budget: 1,500.00 OMR
Balance: -500.00 OMR (red)
```

**What this means:**
- In 2024, you had a budget of 1,500 OMR
- You spent 2,000 OMR on approved requests
- You exceeded your budget by 500 OMR
- The red color indicates you're over budget

## Common Scenarios

### Scenario 1: Monthly Budget Review

**Goal:** Review your current year's financial status

**Steps:**
1. **Filter to current year** (e.g., 2025)
2. **Review the balance** - is it positive or negative?
3. **Consider the spending** - is it reasonable for this time of year?
4. **Plan accordingly** - adjust future spending if needed

### Scenario 2: Year-over-Year Comparison

**Goal:** Compare financial performance across years

**Steps:**
1. **View all years** to see the complete picture
2. **Compare spending patterns** across different years
3. **Identify trends** - are budgets increasing or decreasing?
4. **Analyze efficiency** - how well are you staying within budget?

### Scenario 3: Budget Planning

**Goal:** Use historical data to plan next year's budget

**Steps:**
1. **Review past years' spending** to understand patterns
2. **Consider budget vs. actual** - were budgets realistic?
3. **Factor in growth** - plan for increased needs
4. **Set appropriate targets** based on historical performance

## Troubleshooting

### "No financial data available"

**Possible causes:**
- No data exists in the source systems
- You don't have permission to view the data
- The source plugins aren't properly configured

**Solutions:**
1. **Check your permissions** - ensure you have "View finance calculations" capability
2. **Verify source data** - confirm that finance services and participant data exist
3. **Contact your administrator** if the issue persists

### "Error refreshing financial data"

**Possible causes:**
- Database connection issues
- Missing source tables
- Permission problems
- Missing dependency plugins

**Solutions:**
1. **Try again later** - temporary database issues may resolve
2. **Check with your administrator** - they can investigate the technical issues
3. **Use the data as-is** - the existing data may still be useful
4. **Verify dependencies** - ensure required plugins are installed

### Data seems incorrect

**Possible causes:**
- Source data has been updated
- Filter settings are incorrect
- Cache needs refreshing
- Dependencies are missing

**Solutions:**
1. **Refresh the data** using the refresh button (if you have permission)
2. **Check your filters** - ensure you're looking at the right year
3. **Verify source data** - check the original finance and participant systems
4. **Contact your administrator** if discrepancies persist

### "Refresh Data" button not visible

**Possible causes:**
- You don't have manager permissions
- The capability isn't assigned to your role

**Solutions:**
1. **Contact your administrator** to request the "Manage finance calculations" capability
2. **Check your role** - ensure you have the appropriate permissions
3. **Ask a manager** to refresh the data for you

## Best Practices

### Regular Monitoring
- **Check weekly** during active periods
- **Review monthly** for overall trends
- **Analyze quarterly** for strategic planning

### Data Quality
- **Ensure source data is accurate** - the Finance Calculator reflects what's in your source systems
- **Keep approvals current** - only approved requests are included in spending calculations
- **Maintain consistent year assignments** - ensure all data is properly assigned to the correct fiscal year

### Reporting
- **Export data** when needed for external reporting
- **Document significant changes** in spending patterns
- **Share insights** with relevant stakeholders

## Permissions and Access

### Viewing Reports
- **Required capability:** `local/financecalc:view`
- **Default roles:** Manager, Course Creator
- **What you can do:** View financial overview and reports

### Managing Data
- **Required capability:** `local/financecalc:manage`
- **Default roles:** Manager only
- **What you can do:** Refresh data, manage cache, configure settings

### Requesting Access
If you need access to financial reports:

1. **Contact your Moodle administrator**
2. **Explain your role and need** for financial data
3. **Request appropriate permissions** based on your responsibilities

## Language Support

The Finance Calculator supports multiple languages:

### English
- Default language
- All features available
- Standard financial terminology

### Arabic (العربية)
- Full Arabic translation
- Right-to-left (RTL) layout support
- Arabic financial terms

### Switching Languages
1. **Go to your user profile**
2. **Select "Preferences"**
3. **Choose your preferred language**
4. **Save changes**

The interface will automatically update to your selected language.

## Getting Help

### Self-Service Resources
- **This user guide** - comprehensive documentation
- **On-screen help** - tooltips and contextual information
- **Moodle help system** - integrated help resources

### Contacting Support
If you need additional help:

1. **Check this guide first** - many questions are answered here
2. **Contact your Moodle administrator** - they can help with technical issues
3. **Review error messages** - they often contain helpful information
4. **Document the issue** - include screenshots and specific steps

### Reporting Issues
When reporting problems, include:
- **What you were trying to do**
- **What happened instead**
- **Any error messages**
- **Your browser and operating system**
- **Screenshots if helpful**

## Glossary

### Financial Terms
- **Budget (OMR)**: Money allocated for a specific period
- **Spending (OMR)**: Money actually spent on approved requests
- **Balance (OMR)**: Remaining budget (Budget - Spending)
- **OMR**: Omani Rial, the currency used in the system

### Technical Terms
- **Fiscal Year**: The financial year for budgeting and reporting
- **Approved Requests**: Requests that have been approved and are included in spending calculations
- **Cache**: Temporary storage of calculated data for faster access
- **Refresh**: Update data from source systems
- **Live Calculation**: Real-time calculation from source data
- **Hybrid Mode**: System that can use both cached and live data

### System Terms
- **Finance Services**: System for managing financial service requests
- **Participant Requests**: System for managing participant compensation and costs
- **Source Data**: Original data from finance and participant systems
- **Dependencies**: Required plugins that must be installed for the system to work

---

**Last Updated**: January 2025  
**Version**: 1.0  
**Compatible with**: Moodle 4.1+
