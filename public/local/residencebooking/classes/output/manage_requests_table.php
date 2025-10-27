<?php
/**
 * Build the data context for the “Manage Accommodation Requests” Mustache
 * template.  It:
 *
 *   • Joins local_status for translated status labels
 *   • Calculates per-row permissions (approve / reject buttons)
 *   • Passes a rejection-note value **whenever it is non-empty _and_
 *     the request is not in the final “Approved” state**.
 *
 * @package   local_residencebooking
 */

class manage_requests_table {

    /**
     * Fetch & format rows.
     *
     * @return array ['requests' => [], 'pagination' => []]
     */
    public function get_requests_data(
        int $page,
        int $perpage,
        int $status_filter      = 0,
        string $guest_name      = '',
        string $service_number  = '',
        string $start_date      = '',
        string $end_date        = '',
        string $course_id       = '',
        int $residence_type     = 0,
        int $purpose            = 0
    ): array {
        global $DB;

        /* ──────────────────────────────────────────────
         * 1. SQL bits
         * ──────────────────────────────────────────── */
        $labelcol  = current_language() === 'ar' ? 'display_name_ar' : 'display_name_en';
        $fnameType = current_language() === 'ar' ? 'type_name_ar'    : 'type_name_en';
        $fnamePurp = current_language() === 'ar' ? 'description_ar' : 'description_en';

        $fields = "
            rr.id,
            rr.courseid,
            rr.status_id,
            rr.guest_name,
            rr.service_number,
            rt.$fnameType  AS residence_type,
            rr.start_date,
            rr.end_date,
            rp.$fnamePurp  AS purpose,
            rr.rejection_note,
            c.fullname     AS course_name,
            ls.$labelcol   AS status_label
        ";

        $from = "
            {local_residencebooking_request} rr
            JOIN {local_residencebooking_types}    rt ON rt.id = rr.residence_type
            JOIN {local_residencebooking_purpose}  rp ON rp.id = rr.purpose
            JOIN {course}                          c  ON c.id  = rr.courseid
            JOIN {local_status}                    ls ON ls.id = rr.status_id
        ";

        $where  = 'WHERE 1=1';
        $params = [];

        if ($course_id && $course_id !== '')      { $where .= ' AND rr.courseid       = :cid'; $params['cid'] = $course_id; }
        if ($status_filter && $status_filter > 0)  { $where .= ' AND rr.status_id      = :sid'; $params['sid'] = $status_filter; }
        if ($guest_name && trim($guest_name) !== '')     { $where .= ' AND rr.guest_name LIKE :g';     $params['g']  = "%{$guest_name}%"; }
        if ($service_number && trim($service_number) !== '') { $where .= ' AND rr.service_number LIKE :s'; $params['s']  = "%{$service_number}%"; }
        if ($start_date && trim($start_date) !== '')     { $where .= ' AND rr.start_date >=  :sd';     $params['sd'] = strtotime($start_date); }
        if ($end_date && trim($end_date) !== '')       { $where .= ' AND rr.end_date   <=  :ed';     $params['ed'] = strtotime($end_date); }
        if ($residence_type && $residence_type > 0) { $where .= ' AND rr.residence_type = :rt';   $params['rt'] = $residence_type; }
        if ($purpose && $purpose > 0)        { $where .= ' AND rr.purpose         = :p';   $params['p']  = $purpose; }

        /* ──────────────────────────────────────────────
         * 2. Pagination
         * ──────────────────────────────────────────── */
        $total = $DB->count_records_sql("SELECT COUNT(1) FROM $from $where", $params);

        $records = $DB->get_records_sql(
            "SELECT $fields FROM $from $where ORDER BY rr.id DESC",
            $params,
            $page * $perpage,
            $perpage
        );

        /* ──────────────────────────────────────────────
         * 3. Format rows
         * ──────────────────────────────────────────── */
        $approvedId = \local_residencebooking\simple_workflow_manager::STATUS_APPROVED;
        $rejectedId = \local_residencebooking\simple_workflow_manager::STATUS_REJECTED;

        $rows = [];
        foreach ($records as $r) {

            $statusid = (int)$r->status_id;

            // Show note if non-empty AND not fully approved
            $note = '';
            if ($statusid !== $approvedId) {
                $note = trim($r->rejection_note ?? '');
            }

            $rows[] = [
                'id'             => $r->id,
                'course_name'    => $r->course_name,
                'guest_name'     => $r->guest_name,
                'service_number' => $r->service_number,
                'residence_type' => $r->residence_type,
                'start_date'     => userdate($r->start_date),
                'end_date'       => userdate($r->end_date),
                'purpose'        => $r->purpose,
                'status_label'   => format_string($r->status_label),
                'rejection_note' => $note,

                'can_approve'    => \local_residencebooking\simple_workflow_manager::can_user_approve($statusid),
                'can_reject'     => \local_residencebooking\simple_workflow_manager::can_user_reject($statusid),
                'status_final'   => ($statusid === $approvedId) || ($statusid === $rejectedId),

                'approve_url'    => (new \moodle_url('/local/residencebooking/status.php'))->out(),
                'reject_url'     => (new \moodle_url('/local/residencebooking/status.php'))->out(),
                'sesskey'        => sesskey(),
            ];
        }

        /* ──────────────────────────────────────────────
         * 4. Pagination URLs (preserve filter parameters)
         * ──────────────────────────────────────────── */
        $pagination = [];
        
        // Build base URL with all current filter parameters
        $base_params = ['tab' => 'manage'];
        if ($status_filter) $base_params['status'] = $status_filter;
        if ($course_id) $base_params['course_id'] = $course_id;
        if ($guest_name) $base_params['guest_name'] = $guest_name;
        if ($service_number) $base_params['service_number'] = $service_number;
        if ($start_date) $base_params['start_date'] = $start_date;
        if ($end_date) $base_params['end_date'] = $end_date;
        if ($residence_type) $base_params['residence_type'] = $residence_type;
        if ($purpose) $base_params['purpose'] = $purpose;
        
        if ($page > 0) {
            $prev_params = $base_params;
            $prev_params['page'] = $page - 1;
            $pagination['previous'] = new \moodle_url('/local/residencebooking/index.php', $prev_params);
        }
        if (($page + 1) * $perpage < $total) {
            $next_params = $base_params;
            $next_params['page'] = $page + 1;
            $pagination['next'] = new \moodle_url('/local/residencebooking/index.php', $next_params);
        }

        return ['requests' => $rows, 'pagination' => $pagination];
    }

    /* ─────────── lookup helpers for the filter dropdowns (unchanged) ─────────── */

    public function get_all_courses_for_filter() {
        global $DB;
        return $DB->get_records('course', null, 'fullname', 'id, fullname');
    }

    public function get_all_residence_types_for_filter() {
        global $DB;
        $f = current_language() === 'ar' ? 'type_name_ar' : 'type_name_en';
        return $DB->get_records_select(
            'local_residencebooking_types', 'deleted = 0', null, $f, "id, $f AS name");
    }

    public function get_all_purposes_for_filter() {
        global $DB;
        $f = current_language() === 'ar' ? 'description_ar' : 'description_en';
        return $DB->get_records_select(
            'local_residencebooking_purpose', 'deleted = 0', null, $f, "id, $f AS name");
    }
}
