<?php

defined('MOODLE_INTERNAL') || die();

class block_annual_report extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_annual_report');
    }

    public function get_content() {
        global $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        // Check if required tables exist
        if (!$DB->get_manager()->table_exists('local_annual_plan_course') || 
            !$DB->get_manager()->table_exists('local_annual_plan_course_level')) {
            $this->content->text = '<div style="color: red; padding: 10px; border: 1px solid red;">' .
                '<strong>Error:</strong> Required database tables are missing. Please contact your administrator.' .
                '</div>';
            return $this->content;
        }

        // Get current year
        $current_year = date('Y');

        // Check for active annual plans for the current year
        $active_plans = $DB->get_records('local_annual_plan', ['year' => $current_year, 'disabled' => 0]);
        if (empty($active_plans)) {
            $this->content->text = '<div style="color: orange; padding: 15px; border: 2px solid orange; margin: 20px 0; font-size: 1.2em;">'
                . get_string('noactiveannualplan', 'block_annual_report')
                . '</div>';
            return $this->content;
        }
        
        // Get all active plan IDs for the current year
        $active_plan_ids = array_keys($active_plans);
        $plan_placeholders = implode(',', array_fill(0, count($active_plan_ids), '?'));

        // Get internal and external level IDs dynamically from database
        $internal_levels = [];
        $external_levels = [];
        
        if ($DB->get_manager()->field_exists('local_annual_plan_course_level', 'is_internal')) {
            $internal_levels = $DB->get_records('local_annual_plan_course_level', ['is_internal' => 1], '', 'id');
            $external_levels = $DB->get_records('local_annual_plan_course_level', ['is_internal' => 0], '', 'id');
        } else {
            // Fallback: assume first half are internal, second half are external
            $all_levels = $DB->get_records('local_annual_plan_course_level', [], '', 'id');
            if (!empty($all_levels)) {
                $level_ids = array_keys($all_levels);
                $split_point = ceil(count($level_ids) / 2);
                $internal_levels = array_slice($all_levels, 0, $split_point, true);
                $external_levels = array_slice($all_levels, $split_point, null, true);
            }
        }

        $internal_level_ids = array_keys($internal_levels);
        $external_level_ids = array_keys($external_levels);

        // Check if we have any course levels
        if (empty($internal_level_ids) && empty($external_level_ids)) {
            $this->content->text = '<div style="color: orange; padding: 10px; border: 1px solid orange;">' .
                '<strong>Warning:</strong> No course levels found. Please configure course levels first.' .
                '</div>';
            return $this->content;
        }

        // Fallback to prevent SQL errors
        if (empty($internal_level_ids)) {
            $internal_level_ids = [0]; // Use 0 to prevent SQL errors
        }
        if (empty($external_level_ids)) {
            $external_level_ids = [0]; // Use 0 to prevent SQL errors
        } 

        // Get teacher role IDs (if table exists)
        $teacher_role_ids = [];
        if ($DB->get_manager()->table_exists('role')) {
            $teacher_roles = $DB->get_records_list('role', 'shortname', ['editingteacher', 'teacher']);
            $teacher_role_ids = array_map(function($r) { return $r->id; }, $teacher_roles);
        }
        
        if (empty($teacher_role_ids)) {
            $teacher_role_ids = [0]; // Fallback
        }
        $teacher_placeholders = implode(',', array_fill(0, count($teacher_role_ids), '?'));

        //HTML Code
        $this->content->text .= "<div class='wrapper'>";

        /////////Internal Courses Section/////////

        $internal_placeholders = implode(',', array_fill(0, count($internal_level_ids), '?'));
        $params = array_merge($internal_level_ids, $active_plan_ids);

        $total_internal_courses_this_year_sql = 
        "SELECT COUNT(ac.courselevelid) AS total_internal_courses_this_year 
        FROM {local_annual_plan_course} ac
        JOIN {local_annual_plan} ap ON ac.annualplanid = ap.id
        WHERE ac.courselevelid IN ($internal_placeholders)
        AND ac.approve = 1
        AND ap.id IN ($plan_placeholders)
        AND ap.disabled = 0";
        $iCourseResult = $DB->get_record_sql($total_internal_courses_this_year_sql, $params);
        $total_internal_courses_this_year = $iCourseResult ? $iCourseResult->total_internal_courses_this_year : 0;

        $total_internal_trainees_sql = 
            "SELECT COALESCE(SUM(ac.numberofbeneficiaries), 0) AS total_internal_trainees
            FROM {local_annual_plan_course} ac
            JOIN {local_annual_plan} ap ON ac.annualplanid = ap.id
            WHERE ac.courselevelid IN ($internal_placeholders)
              AND ac.approve = 1
              AND ac.numberofbeneficiaries > 0
              AND ap.id IN ($plan_placeholders)
              AND ap.disabled = 0";
        $iTraineesParams = array_merge($internal_level_ids, $active_plan_ids);
        $iTrainesssResult = $DB->get_record_sql($total_internal_trainees_sql, $iTraineesParams);
        $total_internal_trainees = $iTrainesssResult ? $iTrainesssResult->total_internal_trainees : 0;

        $internal_levels_sql = 
        "SELECT 
            cl.id,
            CASE 
                WHEN ? = 'ar' THEN COALESCE(cl.description_ar, cl.description_en, cl.name)
                ELSE COALESCE(cl.description_en, cl.description_ar, cl.name)
            END AS i_level_name, 
            COUNT(ac.id) AS total_internal_courses_for_this_level
            FROM {local_annual_plan_course_level} cl
            JOIN {local_annual_plan_course} ac ON ac.courselevelid = cl.id
            JOIN {local_annual_plan} ap ON ac.annualplanid = ap.id
            WHERE cl.id IN ($internal_placeholders)
              AND ac.approve = 1
              AND ap.id IN ($plan_placeholders)
              AND ap.disabled = 0
            GROUP BY cl.id, cl.name, cl.description_en, cl.description_ar ";
        $iLevelsParams = array_merge([current_language()], $internal_level_ids, $active_plan_ids);
        $internal_Levels = $DB->get_records_sql($internal_levels_sql, $iLevelsParams);

        $internal_trainees_in_each_level_sql = 
        "SELECT ac.courselevelid AS i_level, 
                SUM(ac.numberofbeneficiaries) AS total_internal_trainees_for_this_level
            FROM {local_annual_plan_course} ac
            JOIN {local_annual_plan} ap ON ac.annualplanid = ap.id
            WHERE ac.courselevelid IN ($internal_placeholders)
              AND ac.approve = 1
              AND ac.numberofbeneficiaries > 0
              AND ap.id IN ($plan_placeholders)
              AND ap.disabled = 0
            GROUP BY ac.courselevelid;";
        $itraineesParams = array_merge($internal_level_ids, $active_plan_ids);
        $internal_trainees_records = $DB->get_records_sql($internal_trainees_in_each_level_sql , $itraineesParams);

        // Get plan titles for display
        // Build a "level -> titles string" map in one query
$dbfamily = $DB->get_dbfamily(); // 'mysql' | 'postgres' | 'mssql' | etc.

// Aggregate function per DB
if ($dbfamily === 'postgres') {
    $agg = "string_agg(DISTINCT ap.title, ', ')";
} else {
    // MySQL/MariaDB & most others
    $agg = "GROUP_CONCAT(DISTINCT ap.title ORDER BY ap.title SEPARATOR ', ')";
}
        $internal_plan_type_sql = 
        "SELECT cl.id AS level_id, $agg AS plantitles
        FROM {local_annual_plan_course_level} cl
        JOIN {local_annual_plan_course} ac ON ac.courselevelid = cl.id
        JOIN {local_annual_plan} ap ON ap.id = ac.annualplanid
        WHERE cl.id IN ($internal_placeholders)
        AND ac.approve = 1
        AND ap.id IN ($plan_placeholders)
        AND ap.disabled = 0
        GROUP BY cl.id";
        $int_rows = $DB->get_records_sql($internal_plan_type_sql, array_merge($internal_level_ids, $active_plan_ids));
        $iplanParams = array_merge($internal_level_ids, $active_plan_ids);
        $iplanTitles = $DB->get_records_sql($internal_plan_type_sql, $iplanParams);

        // Build: level_id => [title, title, ...]
$internal_plan_titles_map = [];
foreach ($int_rows as $rec) {
    // $rec has ->title and ->id (level id)
    $internal_plan_titles_map[$rec->id][] = $rec->title;
}



        $this->content->text .= "<div>
            <h3 class='title'>" . get_string('internalcourses', 'block_annual_report') . "</h3>
            <div class='count-row'>
                <div class='box'>
                    <span>" . get_string('count', 'block_annual_report') . " : <strong>$total_internal_courses_this_year</strong></span>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <span>" . get_string('beneficiaries', 'block_annual_report') . " : <strong>$total_internal_trainees</strong></span>
                </div>
            </div>
            <table class='stat-table'>
                <thead>
                    <tr style='font-size: 18px;'>
                        <th>" . get_string('coursetype', 'block_annual_report') . "</th>
                        <th>" . get_string('planType', 'block_annual_report') . "</th>
                        <th>" . get_string('count', 'block_annual_report') . "</th>
                        <th>" . get_string('beneficiaries', 'block_annual_report') . "</th>
                    </tr>
                </thead>
                <tbody>";

        //loop for internal beneficiaries 
        $internal_beneficiaries_map = [];
        foreach ($internal_trainees_records as $record) {
            $internal_beneficiaries_map[$record->i_level] = $record->total_internal_trainees_for_this_level;
        }

        // Loop for Internal Courses level
        $total_courses_all_levels = 0;
        $total_beneficiaries_all_levels = 0;

        if (empty($internal_Levels)) {
            // Show message when no internal courses found
            $this->content->text .= '<tr>';
            $this->content->text .= '<td colspan="4" style="text-align: center; color: #666; font-style: italic;">No approved internal courses found for ' . date('Y') . '</td>';
            $this->content->text .= '</tr>';
        } else {
            foreach ($internal_Levels as $lvl) {
                $level_id = $lvl->id;
                $level_name = format_string($lvl->i_level_name);
                $course_count = $lvl->total_internal_courses_for_this_level;
                $itrainee_count = isset($internal_beneficiaries_map[$level_id]) ? $internal_beneficiaries_map[$level_id] : 0;

                $total_courses_all_levels += $course_count;
                $total_beneficiaries_all_levels += $itrainee_count;

                $level_id = (int)$lvl->id;
                $internal_plan_type = ($internal_plan_titles_map[$level_id])?? '' !== '' ? $internal_plan_titles_map[$level_id] : get_string('na', 'block_annual_report'); // fallback "N/A"

                $this->content->text .= '<tr>';
                $this->content->text .= "<td>$level_name</td>";
                $this->content->text .= "<td>$internal_plan_type</td>";
                $this->content->text .= "<td>$course_count</td>";
                $this->content->text .= "<td>$itrainee_count</td>";
                $this->content->text .= '</tr>';
            }
        }
        $this->content->text .= '</tbody></table></div>';
        $this->content->text .= "<hr class='line'>";

        //End Of Internal Courses

        ///////External Section///////

        $external_placeholders = implode(',', array_fill(0, count($external_level_ids), '?'));

        // Query for Total external Courses Created This Year
        $total_external_courses_this_year_sql = 
        "SELECT COUNT(ac.courselevelid) AS total_external_courses_this_year
        FROM {local_annual_plan_course} ac
        JOIN {local_annual_plan} ap ON ac.annualplanid = ap.id
        WHERE ac.courselevelid IN ($external_placeholders)
        AND ac.approve = 1
        AND ap.id IN ($plan_placeholders)
        AND ap.disabled = 0";

        $eCourseParams = array_merge($external_level_ids, $active_plan_ids);
        $eCourseResult = $DB->get_record_sql($total_external_courses_this_year_sql, $eCourseParams);
        $total_external_courses_this_year = $eCourseResult ? $eCourseResult->total_external_courses_this_year : 0;

        // External Courses Query
        $external_categories_sql = 
        "SELECT 
            cl.id,
            CASE 
                WHEN ? = 'ar' THEN COALESCE(cl.description_ar, cl.description_en, cl.name)
                ELSE COALESCE(cl.description_en, cl.description_ar, cl.name)
            END AS e_level_name, 
            COUNT(ac.id) AS total_external_courses_for_this_level
            FROM {local_annual_plan_course_level} cl
            JOIN {local_annual_plan_course} ac ON ac.courselevelid = cl.id
            JOIN {local_annual_plan} ap ON ac.annualplanid = ap.id
            WHERE cl.id IN ($external_placeholders)
              AND ac.approve = 1
              AND ap.id IN ($plan_placeholders)
              AND ap.disabled = 0
            GROUP BY cl.id, cl.name, cl.description_en, cl.description_ar ";

        $eLevelParams = array_merge([current_language()], $external_level_ids, $active_plan_ids);
        $external_levels = $DB->get_records_sql($external_categories_sql, $eLevelParams);

        // Query to Get Total external Trainees
        $total_external_trainees_sql = "
            SELECT COALESCE(SUM(ac.numberofbeneficiaries), 0) AS total_external_trainees
            FROM {local_annual_plan_course} ac
            JOIN {local_annual_plan} ap ON ac.annualplanid = ap.id
            WHERE ac.courselevelid IN ($external_placeholders)
              AND ac.approve = 1
              AND ac.numberofbeneficiaries > 0
              AND ap.id IN ($plan_placeholders)
              AND ap.disabled = 0";

        $eTraineesParams = array_merge($external_level_ids, $active_plan_ids);
        $eTraineesResult = $DB->get_record_sql($total_external_trainees_sql, $eTraineesParams);
        $total_external_trainees = $eTraineesResult ? $eTraineesResult->total_external_trainees : 0;

        // Query for external trainees in each level
        $external_trainees_in_each_level_sql = 
        "SELECT ac.courselevelid AS e_level, 
                SUM(ac.numberofbeneficiaries) AS total_external_trainees_for_this_level
            FROM {local_annual_plan_course} ac
            JOIN {local_annual_plan} ap ON ac.annualplanid = ap.id
            WHERE ac.courselevelid IN ($external_placeholders)
              AND ac.approve = 1
              AND ac.numberofbeneficiaries > 0
              AND ap.id IN ($plan_placeholders)
              AND ap.disabled = 0
            GROUP BY ac.courselevelid;";

        $etraineesParams = array_merge($external_level_ids, $active_plan_ids);
        $external_trainees_records = $DB->get_records_sql($external_trainees_in_each_level_sql, $etraineesParams);

        // Get plan titles for external courses display
        $external_plan_type_sql = 
        "SELECT ap.title, cl.id
  FROM {local_annual_plan} ap
  JOIN {local_annual_plan_course} ac ON ap.id = ac.annualplanid
  JOIN {local_annual_plan_course_level} cl ON ac.courselevelid = cl.id
  WHERE cl.id IN ($external_placeholders)
    AND ac.approve = 1
    AND ap.id IN ($plan_placeholders)
    AND ap.disabled = 0
  GROUP BY ap.title, cl.id";
        $eplanParams = array_merge($external_level_ids, $active_plan_ids);
        $eplanTitles = $DB->get_records_sql($external_plan_type_sql, $eplanParams);
// Build: level_id => [title, title, ...]
$external_plan_titles_map = [];
foreach ($eplanTitles as $rec) {
    $external_plan_titles_map[$rec->id][] = $rec->title;
}

        // Create external beneficiaries map
        $external_beneficiaries_map = [];
        foreach ($external_trainees_records as $record) {
            $external_beneficiaries_map[$record->e_level] = $record->total_external_trainees_for_this_level;
        }

        $this->content->text .= "<div>
            <h3 class='title'>" . get_string('externalcourses', 'block_annual_report') . "</h3>
            <div class='count-row'>
                <div class='box'>
                    <span>" . get_string('count', 'block_annual_report') . " : <strong>$total_external_courses_this_year</strong></span>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <span>" . get_string('beneficiaries', 'block_annual_report') . " : <strong>$total_external_trainees</strong></span>
                </div>
            </div>
            <table class='stat-table'>
                <thead>
                    <tr style='font-size: 18px;'>
                        <th>" . get_string('coursetype', 'block_annual_report') . "</th>
                        <th>" . (get_string('planType', 'block_annual_report') !== '[[planType]]' ? get_string('planType', 'block_annual_report') : (current_language() === 'ar' ? 'نوع الخطة' : 'Plan Type')) . "</th>
                        <th>" . get_string('count', 'block_annual_report') . "</th>
                        <th>" . get_string('beneficiaries', 'block_annual_report') . "</th>
                    </tr>
                </thead>
                <tbody>";

        // Loop for External Courses

        $external_plan_type = isset($external_plan_titles_map[$level_id])
        ? implode(', ', array_unique($external_plan_titles_map[$level_id]))
        : get_string('na', 'block_annual_report');

        if (empty($external_levels)) {
            // Show message when no external courses found
            $this->content->text .= '<tr>';
            $this->content->text .= '<td colspan="4" style="text-align: center; color: #666; font-style: italic;">No approved external courses found for ' . date('Y') . '</td>';
            $this->content->text .= '</tr>';
        } else {
            foreach ($external_levels as $lvl) {
                $external_trainee_count = isset($external_beneficiaries_map[$lvl->id]) ? $external_beneficiaries_map[$lvl->id] : 0;
                $this->content->text .= '<tr>';
                $this->content->text .= '<td>' . format_string($lvl->e_level_name) . '</td>';
                $this->content->text .= '<td>' . $external_plan_type . '</td>';
                $this->content->text .= '<td>' . $lvl->total_external_courses_for_this_level . '</td>';
                $this->content->text .= '<td>' . $external_trainee_count . '</td>';
                $this->content->text .= '</tr>';
            }
        }

        $this->content->text .= '</tbody></table></div>';
        $this->content->text .= "<hr class='line'>";

        //Finance Details
        if ($DB->get_manager()->table_exists('local_financeservices_clause')) {
            // Always show clauses for the current year (dynamic list)
            $report_year = (int)date('Y');
            $year_start = strtotime($report_year . '-01-01 00:00:00');
            $year_end   = strtotime($report_year . '-12-31 23:59:59');

            $clauses = $DB->get_records_select(
                'local_financeservices_clause',
                'clause_year = :yr AND deleted = 0',
                ['yr' => $report_year],
                'clause_name_en ASC',
                'id, clause_name_en, clause_name_ar, amount'
            );

            if (!empty($clauses)) {
                $this->content->text .="<div>
                <h3 class='title'> ". get_string('financial', 'block_annual_report') . "  - " . $report_year . "</h3>
                <table class='stat-table'>
                       <tr>
                           <th>". get_string('clauseType', 'block_annual_report') . " </th>
                           <th>". get_string('approvedAmount', 'block_annual_report') . "</th>
                           <th>". get_string('spentAmount', 'block_annual_report') . "</th>
                           <th>". get_string('remainingAmount', 'block_annual_report') . "</th>
                       </tr>";

                $amountsql = "
                    SELECT COALESCE(SUM(price_requested), 0) AS total_spent
                      FROM {local_financeservices}
                     WHERE clause_id = :clauseid
                       AND status_id = 13
                       AND date_time_requested BETWEEN :ys AND :ye";

                $isArabic = (current_language() === 'ar');
                foreach ($clauses as $cl) {
                    $data = $DB->get_record_sql($amountsql, ['clauseid' => $cl->id, 'ys' => $year_start, 'ye' => $year_end]);
                    $spent = $data ? (float)$data->total_spent : 0.0;
                    $approved = (float)$cl->amount;
                    $remaining = $approved - $spent;

                    $name = $isArabic ? ($cl->clause_name_ar ?? $cl->clause_name_en) : ($cl->clause_name_en ?? $cl->clause_name_ar);
                    $this->content->text .= "<tr>";
                    $this->content->text .= "<td>" . format_string($name) . "</td>";
                    $this->content->text .= "<td>" . number_format($approved, 2) . " OMR</td>";
                    $this->content->text .= "<td>" . number_format($spent, 2) . " OMR</td>";
                    $this->content->text .= "<td>" . number_format($remaining, 2) . " OMR</td>";
                    $this->content->text .= "</tr>";
                }

                $this->content->text .= "</table></div>";
            } else {
                $this->content->text .= "<div style='color: orange; padding: 10px; margin: 10px 0;'>";
                $this->content->text .= "<strong>Finance Information:</strong> No finance clauses found for year {$report_year}.";
                $this->content->text .= "</div>";
            }
        } else {
            $this->content->text .= "<div style='color: orange; padding: 10px; margin: 10px 0;'>";
            $this->content->text .= "<strong>Finance Information:</strong> Finance services module not installed.";
            $this->content->text .= "</div>";
        }

        $this->content->text .= "</div>"; // Close wrapper

        return $this->content;
    }
}
