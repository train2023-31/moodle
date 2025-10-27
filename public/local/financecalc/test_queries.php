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
 * Test script for financecalc plugin queries.
 * 
 * This script tests the SQL queries against your existing database
 * to ensure they work correctly before installing the plugin.
 *
 * @package    local_financecalc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This script should be run from the command line or removed after testing.
// It's for development/testing purposes only.

require_once('../../config.php');

// Only allow access from CLI or admin users.
if (!CLI_SCRIPT && !has_capability('moodle/site:config', context_system::instance())) {
    die('Access denied');
}

echo "Testing Finance Calculator Plugin Queries\n";
echo "========================================\n\n";

// Test 1: Check if required tables exist
echo "1. Checking required tables...\n";
$required_tables = [
    'local_financeservices_clause',
    'local_financeservices', 
    'local_participant_requests',
    'local_annual_plan',
    'local_participant_request_types'
];

foreach ($required_tables as $table) {
    $exists = $DB->get_manager()->table_exists($table);
    echo "   {$table}: " . ($exists ? "✓ EXISTS" : "✗ MISSING") . "\n";
}

echo "\n";

// Test 2: Check budget data
echo "2. Testing budget calculation...\n";
$budgetsql = "SELECT c.clause_year AS year, SUM(c.amount) AS budget_omr
              FROM {local_financeservices_clause} c
              WHERE c.deleted = 0
              GROUP BY c.clause_year
              ORDER BY c.clause_year DESC";

try {
    $budgetdata = $DB->get_records_sql($budgetsql);
    echo "   Found " . count($budgetdata) . " years with budget data:\n";
    foreach ($budgetdata as $row) {
        echo "   - Year {$row->year}: " . number_format($row->budget_omr, 2) . " OMR\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Check finance services spending
echo "3. Testing finance services spending...\n";
$finsql = "SELECT c.clause_year AS year, SUM(f.price_requested) AS spending_fin_omr
           FROM {local_financeservices} f
           JOIN {local_financeservices_clause} c ON c.id = f.clause_id
           WHERE f.status_id = 13
           GROUP BY c.clause_year
           ORDER BY c.clause_year DESC";

try {
    $findata = $DB->get_records_sql($finsql);
    echo "   Found " . count($findata) . " years with finance spending:\n";
    foreach ($findata as $row) {
        echo "   - Year {$row->year}: " . number_format($row->spending_fin_omr, 2) . " OMR\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Check participant spending
echo "4. Testing participant spending...\n";
$partsql = "SELECT ap.year AS year,
            SUM(COALESCE(r.compensation_amount, (r.duration_amount * t.cost))) AS spending_part_omr
            FROM {local_participant_requests} r
            JOIN {local_annual_plan} ap ON ap.id = r.annual_plan_id
            LEFT JOIN {local_participant_request_types} t ON t.id = r.participant_type_id
            WHERE r.is_approved = 1
            GROUP BY ap.year
            ORDER BY ap.year DESC";

try {
    $partdata = $DB->get_records_sql($partsql);
    echo "   Found " . count($partdata) . " years with participant spending:\n";
    foreach ($partdata as $row) {
        echo "   - Year {$row->year}: " . number_format($row->spending_part_omr, 2) . " OMR\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Combined query
echo "5. Testing combined financial data...\n";
$combinedsql = "WITH budget AS (
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
                ),
                years AS (
                    SELECT year FROM budget
                    UNION
                    SELECT year FROM fin_spend
                    UNION
                    SELECT year FROM part_spend
                )
                SELECT y.year,
                       (COALESCE(f.spending_fin_omr, 0) + COALESCE(p.spending_part_omr, 0)) AS spending_omr,
                       COALESCE(b.budget_omr, 0) AS budget_omr
                FROM years y
                LEFT JOIN budget b ON b.year = y.year
                LEFT JOIN fin_spend f ON f.year = y.year
                LEFT JOIN part_spend p ON p.year = y.year
                ORDER BY y.year DESC";

try {
    $combineddata = $DB->get_records_sql($combinedsql);
    echo "   Found " . count($combineddata) . " years with combined financial data:\n";
    foreach ($combineddata as $row) {
        $balance = $row->budget_omr - $row->spending_omr;
        $balanceclass = $balance >= 0 ? "positive" : "negative";
        echo "   - Year {$row->year}: Spending " . number_format($row->spending_omr, 2) . 
             " OMR, Budget " . number_format($row->budget_omr, 2) . 
             " OMR, Balance " . number_format($balance, 2) . " OMR ({$balanceclass})\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";
echo "Test completed. If all queries work, the plugin should function correctly.\n";
echo "Remove this file after testing.\n";
