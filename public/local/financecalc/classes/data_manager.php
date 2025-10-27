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
 * Data manager for the financecalc plugin.
 *
 * @package    local_financecalc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_financecalc;

defined('MOODLE_INTERNAL') || die();

/**
 * Data manager class for financial calculations.
 *
 * @package    local_financecalc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_manager {

    /**
     * Get financial data for reporting.
     *
     * @param int $yearfilter Optional year filter (0 for all years)
     * @return array Array of financial data objects
     */
    public static function get_financial_data($yearfilter = 0) {
        global $DB;

        // Try to get from cache first.
        $cacheddata = self::get_cached_data($yearfilter);
        if (!empty($cacheddata)) {
            return $cacheddata;
        }

        // If no cached data, calculate live.
        return self::calculate_financial_data($yearfilter);
    }

    /**
     * Get cached financial data.
     *
     * @param int $yearfilter Optional year filter
     * @return array Array of financial data objects
     */
    private static function get_cached_data($yearfilter = 0) {
        global $DB;

        // Check if the cache table exists
        if (!$DB->get_manager()->table_exists('local_financecalc_yearly')) {
            return array(); // Return empty array if table doesn't exist
        }

        $params = array();
        $where = '';
        
        if ($yearfilter > 0) {
            $where = 'WHERE year = :year';
            $params['year'] = $yearfilter;
        }

        $sql = "SELECT year, spending_omr, budget_omr, timemodified
                FROM {local_financecalc_yearly}
                {$where}
                ORDER BY year DESC";

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Calculate financial data live from source tables.
     *
     * @param int $yearfilter Optional year filter
     * @return array Array of financial data objects
     */
    private static function calculate_financial_data($yearfilter = 0) {
        global $DB;

        if ($yearfilter > 0) {
            // Simple query for specific year
            $sql = "SELECT 
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
                        ), 0) AS budget_omr";
            
            return $DB->get_records_sql($sql, array('year' => $yearfilter));
        } else {
            // Complex query for all years
            $sql = "WITH budget AS (
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

            return $DB->get_records_sql($sql);
        }
    }

    /**
     * Refresh cached financial data.
     *
     * @return bool Success status
     */
    public static function refresh_cached_data() {
        global $DB, $USER;

        try {
            // First, try to create the cache table if it doesn't exist
            if (!$DB->get_manager()->table_exists('local_financecalc_yearly')) {
                // Try to create the table manually
                $sql = "CREATE TABLE IF NOT EXISTS {local_financecalc_yearly} (
                    id INT(10) NOT NULL AUTO_INCREMENT,
                    year INT(4) NOT NULL,
                    spending_omr DECIMAL(12,2) NOT NULL DEFAULT 0,
                    budget_omr DECIMAL(12,2) NOT NULL DEFAULT 0,
                    timecreated INT(10) NOT NULL,
                    timemodified INT(10) NOT NULL,
                    created_by INT(10) NOT NULL,
                    modified_by INT(10) NOT NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY year (year)
                )";
                
                try {
                    $DB->execute($sql);
                } catch (Exception $e) {
                    // If we can't create the table, just return true (live mode)
                    return true;
                }
            }

            // Get all financial data at once using the "all years" query
            $alldata = self::calculate_financial_data(0); // 0 = all years
            
            if (empty($alldata)) {
                return true; // No data to cache, but that's not an error
            }

            $transaction = $DB->start_delegated_transaction();

            // Clear existing cached data.
            $DB->delete_records('local_financecalc_yearly');
            
            // Cache all the data we got
            foreach ($alldata as $record) {
                $cacherecord = new \stdClass();
                $cacherecord->year = $record->year;
                $cacherecord->spending_omr = $record->spending_omr;
                $cacherecord->budget_omr = $record->budget_omr;
                $cacherecord->timecreated = time();
                $cacherecord->timemodified = time();
                $cacherecord->created_by = $USER->id;
                $cacherecord->modified_by = $USER->id;
                
                $DB->insert_record('local_financecalc_yearly', $cacherecord);
            }
            
            $transaction->allow_commit();
            return true;
            
        } catch (Exception $e) {
            if (isset($transaction)) {
                $transaction->rollback($e);
            }
            // Log the error for debugging
            debugging('Finance calc refresh error: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }

    /**
     * Get all years that have financial data.
     *
     * @return array Array of years
     */
    private static function get_all_financial_years() {
        global $DB;

        $sql = "SELECT DISTINCT year FROM (
                    SELECT c.clause_year AS year
                    FROM {local_financeservices_clause} c
                    WHERE c.deleted = 0
                    UNION
                    SELECT ap.year
                    FROM {local_participant_requests} r
                    JOIN {local_annual_plan} ap ON ap.id = r.annual_plan_id
                    WHERE r.is_approved = 1
                ) years
                ORDER BY year DESC";

        $years = $DB->get_records_sql($sql);
        return array_keys($years);
    }

    /**
     * Get the last updated time for cached data.
     *
     * @return int|null Timestamp or null if no data
     */
    public static function get_last_updated_time() {
        global $DB;

        // Check if the cache table exists
        if (!$DB->get_manager()->table_exists('local_financecalc_yearly')) {
            return null; // Return null if table doesn't exist
        }

        $sql = "SELECT MAX(timemodified) as last_updated
                FROM {local_financecalc_yearly}";
        
        $record = $DB->get_record_sql($sql);
        return $record ? $record->last_updated : null;
    }

    /**
     * Get detailed breakdown by source for a specific year.
     *
     * @param int $year The year to get breakdown for
     * @return object Object with finance and participant spending
     */
    public static function get_detailed_breakdown($year) {
        global $DB;

        // Finance services spending.
        $finsql = "SELECT SUM(f.price_requested) AS spending_fin_omr
                   FROM {local_financeservices} f
                   JOIN {local_financeservices_clause} c ON c.id = f.clause_id
                   WHERE f.status_id = 13 AND c.clause_year = :year";
        
        $finspending = $DB->get_field_sql($finsql, array('year' => $year)) ?: 0;

        // Participant spending.
        $partsql = "SELECT SUM(COALESCE(r.compensation_amount, (r.duration_amount * t.cost))) AS spending_part_omr
                    FROM {local_participant_requests} r
                    JOIN {local_annual_plan} ap ON ap.id = r.annual_plan_id
                    LEFT JOIN {local_participant_request_types} t ON t.id = r.participant_type_id
                    WHERE r.is_approved = 1 AND ap.year = :year";
        
        $partspending = $DB->get_field_sql($partsql, array('year' => $year)) ?: 0;

        $breakdown = new \stdClass();
        $breakdown->finance_spending = $finspending;
        $breakdown->participant_spending = $partspending;
        $breakdown->total_spending = $finspending + $partspending;

        return $breakdown;
    }

    /**
     * Get clause-specific spending data for a specific year.
     *
     * @param int $year The year to get clause data for (0 for all years)
     * @return array Array of clause spending objects
     */
    public static function get_clause_spending_data($year = 0) {
        global $DB;

        $params = array();
        $yearfilter = '';
        
        if ($year > 0) {
            $yearfilter = 'WHERE c.clause_year = :year';
            $params['year'] = $year;
        }

        $sql = "SELECT 
                    c.id,
                    c.clause_name_en,
                    c.clause_name_ar,
                    c.amount AS budget_amount,
                    c.initial_amount,
                    c.clause_year,
                    c.deleted,
                    COALESCE(spending_data.total_spent, 0) AS total_spent,
                    COALESCE(spending_data.request_count, 0) AS request_count,
                    (c.amount - COALESCE(spending_data.total_spent, 0)) AS remaining_budget,
                    CASE 
                        WHEN c.amount > 0 THEN 
                            ROUND((COALESCE(spending_data.total_spent, 0) / c.amount) * 100, 2)
                        ELSE 0 
                    END AS spending_percentage
                FROM {local_financeservices_clause} c
                LEFT JOIN (
                    SELECT 
                        f.clause_id,
                        SUM(f.price_requested) AS total_spent,
                        COUNT(*) AS request_count
                    FROM {local_financeservices} f
                    WHERE f.status_id = 13
                    GROUP BY f.clause_id
                ) spending_data ON spending_data.clause_id = c.id
                {$yearfilter}
                ORDER BY c.clause_year DESC, c.clause_name_en ASC";

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Get spending data for a specific clause.
     *
     * @param int $clause_id The clause ID
     * @return object Object with clause spending details
     */
    public static function get_clause_details($clause_id) {
        global $DB;

        // Get clause basic info
        $clause = $DB->get_record('local_financeservices_clause', array('id' => $clause_id), '*', MUST_EXIST);

        // Get spending data
        $spendingsql = "SELECT 
                            f.id,
                            f.price_requested,
                            f.notes,
                            f.date_time_requested,
                            f.date_time_processed,
                            c.fullname AS course_name,
                            u.firstname,
                            u.lastname,
                            ls.display_name_en,
                            ls.display_name_ar
                        FROM {local_financeservices} f
                        JOIN {course} c ON f.course_id = c.id
                        JOIN {user} u ON f.user_id = u.id
                        JOIN {local_status} ls ON f.status_id = ls.id
                        WHERE f.clause_id = :clause_id
                        ORDER BY f.date_time_requested DESC";

        $spending_records = $DB->get_records_sql($spendingsql, array('clause_id' => $clause_id));

        // Calculate totals
        $total_spent = 0;
        $approved_spent = 0;
        $pending_spent = 0;
        $approved_count = 0;
        $pending_count = 0;

        foreach ($spending_records as $record) {
            if ($record->display_name_en === 'Approved' || $record->display_name_en === 'موافق عليه') {
                $approved_spent += $record->price_requested;
                $approved_count++;
            } else {
                $pending_spent += $record->price_requested;
                $pending_count++;
            }
            $total_spent += $record->price_requested;
        }

        $clause_details = new \stdClass();
        $clause_details->clause = $clause;
        $clause_details->spending_records = $spending_records;
        $clause_details->total_spent = $total_spent;
        $clause_details->approved_spent = $approved_spent;
        $clause_details->pending_spent = $pending_spent;
        $clause_details->approved_count = $approved_count;
        $clause_details->pending_count = $pending_count;
        $clause_details->remaining_budget = $clause->amount - $approved_spent;
        $clause_details->spending_percentage = $clause->amount > 0 ? 
            round(($approved_spent / $clause->amount) * 100, 2) : 0;

        return $clause_details;
    }

    /**
     * Get summary statistics for all clauses.
     *
     * @param int $year Optional year filter
     * @return object Summary statistics
     */
    public static function get_clause_summary($year = 0) {
        global $DB;

        $params = array();
        $yearfilter = '';
        
        if ($year > 0) {
            $yearfilter = 'WHERE c.clause_year = :year';
            $params['year'] = $year;
        }

        $sql = "SELECT 
                    COUNT(*) AS total_clauses,
                    SUM(c.amount) AS total_budget,
                    SUM(COALESCE(spending_data.total_spent, 0)) AS total_spent,
                    SUM(c.amount - COALESCE(spending_data.total_spent, 0)) AS total_remaining,
                    AVG(CASE 
                        WHEN c.amount > 0 THEN 
                            (COALESCE(spending_data.total_spent, 0) / c.amount) * 100
                        ELSE 0 
                    END) AS avg_spending_percentage
                FROM {local_financeservices_clause} c
                LEFT JOIN (
                    SELECT 
                        f.clause_id,
                        SUM(f.price_requested) AS total_spent
                    FROM {local_financeservices} f
                    WHERE f.status_id = 13
                    GROUP BY f.clause_id
                ) spending_data ON spending_data.clause_id = c.id
                {$yearfilter}";

        return $DB->get_record_sql($sql, $params);
    }
}
