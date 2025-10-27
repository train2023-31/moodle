<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings for the financecalc plugin.
 *
 * @package    local_financecalc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Finance Calculator';
$string['financecalc'] = 'Finance Calculator';
$string['financecalc:view'] = 'View finance calculations';
$string['financecalc:manage'] = 'Manage finance calculations';

// Navigation and pages.
$string['financialreport'] = 'Annual Plans Budget Report';
$string['financialoverview'] = 'Annual Plans Budget';

// Report columns.
$string['year'] = 'Annual Plan Year';
$string['spending'] = 'Spending (OMR)';
$string['budget'] = 'Budget (OMR)';
$string['balance'] = 'Balance (OMR)';
$string['spending_finance'] = 'Finance Services Spending';
$string['spending_participant'] = 'Participant Spending';

// Filters.
$string['filter_year'] = 'Filter by Year';
$string['filter_all_years'] = 'All Years';
$string['filter_approved_only'] = 'Approved Requests Only';

// Messages.
$string['no_data_available'] = 'No financial data available for the selected criteria.';
$string['data_last_updated'] = 'Data last updated: {$a}';
$string['refresh_data'] = 'Refresh Data';
$string['refresh_data_success'] = 'Financial data has been refreshed successfully.';
$string['refresh_data_live'] = 'Data refreshed (live calculation mode)';
$string['refresh_data_error'] = 'Error refreshing financial data.';

// Scheduled task.
$string['task_refresh_financial_data'] = 'Refresh financial calculation data';
$string['task_refresh_financial_data_desc'] = 'Updates the cached financial data from finance services and participant requests.';

// Errors.
$string['error_no_permission'] = 'You do not have permission to view this page.';
$string['error_invalid_year'] = 'Invalid year specified.';

// Filter.
$string['filter'] = 'Filter';

// Clause spending report
$string['clause_spending_report'] = 'Clause Spending Report';
$string['clause_spending_overview'] = 'Clause Spending Overview';
$string['clause_name'] = 'Clause Name';
$string['budget_amount'] = 'Budget Amount';
$string['spent_amount'] = 'Spent Amount';
$string['remaining_budget'] = 'Remaining Budget';
$string['spending_percentage'] = 'Spending %';
$string['request_count'] = 'Request Count';
$string['actions'] = 'Actions';
$string['view_details'] = 'View Details';
$string['back_to_clause_list'] = 'Back to Clause List';
$string['total_clauses'] = 'Total Clauses';
$string['total_budget'] = 'Total Budget';
$string['total_spent'] = 'Total Spent';
$string['approved_spent'] = 'Approved Spent';
$string['spending_records'] = 'Spending Records';
$string['request_id'] = 'Request ID';
$string['requester'] = 'Requester';
$string['amount'] = 'Amount';
$string['status'] = 'Status';
$string['request_date'] = 'Request Date';
$string['notes'] = 'Notes';
$string['no_clauses_found'] = 'No clauses found for the selected criteria.';
$string['no_spending_records'] = 'No spending records found for this clause.';
$string['show_hidden_clauses'] = 'Show Hidden Clauses';
$string['hide_hidden_clauses'] = 'Hide Hidden Clauses';
$string['clause_year'] = 'Clause Year';
$string['course'] = 'Course';

// About section
$string['about_finance_calculator'] = 'About Finance Calculator';
$string['about_finance_calculator_desc'] = 'The Finance Calculator provides comprehensive financial analysis and reporting tools for tracking spending across different funding sources and clauses. Use the tools above to access detailed financial reports and spending analysis.';

// Navigation and UI
$string['navigation_filters'] = 'Navigation & Filters';
$string['filter_options'] = 'Filter Options';
$string['dashboard'] = 'Dashboard';
$string['comprehensive_financial_analysis'] = 'Annual plans budget analysis and reporting dashboard';
$string['detailed_spending_analysis'] = 'Detailed spending analysis by financial clauses and categories';

// Index page
$string['welcome_to_finance_calculator'] = 'Welcome to Finance Calculator';
$string['choose_report_type'] = 'Choose the type of financial report you want to view';
$string['clause_spending_description'] = 'View detailed spending analysis for all financial clauses and categories';
$string['financial_overview_description'] = 'View annual plans budget data and spending analysis by year';