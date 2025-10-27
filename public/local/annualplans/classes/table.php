<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

class annual_plan_table extends table_sql {
    protected $filterparams; // Explicit declaration of the property

    /**
     * Constructor
     *
     * @param string $uniqueid A unique identifier for the table.
     */
    public function __construct($uniqueid, $filterparams = null) {
        parent::__construct($uniqueid);
        $this->define_baseurl(new moodle_url('/local/annualplans/index.php')); // Adjust URL as needed
        $this->filterparams = $filterparams;

        // Define the headers and columns
        $fields = array(
            'annualplantitle', // New column
            'courseid', 
            'coursename', 
            'category', 
            'coursedate', 
            'numberofbeneficiaries', 
            'status'
        );
        $headers = array(
            get_string('annualplantitle', 'local_annualplans'), // New column header
            get_string('courseid', 'local_annualplans'),
            get_string('coursename', 'local_annualplans'),
            get_string('category', 'local_annualplans'),
            get_string('coursedate', 'local_annualplans'),
            get_string('numberofbeneficiaries', 'local_annualplans'),
            get_string('status', 'local_annualplans')
        );

        $this->define_columns($fields);
        $this->define_headers($headers);
        $this->sortable(true, 'coursedate', SORT_DESC); // example: sortable by coursedate
        $this->no_sorting('status');

        $this->set_attribute('id', 'annualplans_table');
        $this->set_attribute('class', 'generaltable generalbox');
        $this->setup(); // Required for the table to be displayed
    }

    /**
     * Format the coursedate column.
     *
     * @param stdClass $row The row from the database.
     * @return string Formatted date.
     */
    public function col_coursedate($row) {
        // Assuming $row->coursedate is a timestamp.
        return userdate($row->coursedate, get_string('strftimedatetime', 'langconfig'));
    }
    
    public function col_annualplantitle($row) {
        return format_string($row->annualplantitle); // Assuming title needs to be formatted as a string
    }

    public function query_db_aprroved($pagesize, $useinitialsbar = true) {
        global $DB;


        $offset = $this->get_page_start();
        $endset = $pagesize;

        //if we compared shortname when use course table it would be more efficient
        $sort = $this->get_sql_sort();
        $sql = "SELECT cap.*, ap.title AS annualplantitle, l.name AS levelname, c.*
        FROM {local_annual_plan_course} cap
        JOIN {local_annual_plan} ap ON cap.annualplanid = ap.id
        LEFT JOIN {local_annual_plan_course_level} l ON cap.courselevelid = l.id
        JOIN {course} c ON c.fullname = cap.coursename";

        $params = []; // Array to hold SQL parameters
        
        $whereclauses = ['cap.disabled = 0'];
        if ($this->filterparams) {
            if (!empty($this->filterparams->annualplanid)) {
                $whereclauses[] = "cap.annualplanid = ?";
                $params[] = $this->filterparams->annualplanid;
            }
            if (!empty($this->filterparams->category)) {
                $whereclauses[] = "cap.category = ?";
                $params[] = $this->filterparams->category;
            }
            if (!empty($this->filterparams->level)) {
                $whereclauses[] = "l.name LIKE ?";
                $params[] = '%' . $this->filterparams->level . '%';
            }
            
            if (!empty($this->filterparams->coursename)) {
                $whereclauses[] = "cap.coursename LIKE ?";
                $params[] = '%' . $this->filterparams->coursename . '%';
            }
            if (!empty($this->filterparams->courseid)) {
                $whereclauses[] = "cap.courseid LIKE ?";
                $params[] =  '%' . $this->filterparams->courseid . '%';
            }
            if (!empty($this->filterparams->status)) {
                $whereclauses[] = "cap.status LIKE ?";
                $params[] = '%' . $this->filterparams->status . '%';
            }
            if (!empty($this->filterparams->place)) {
                $whereclauses[] = "cap.place LIKE ?";
                $params[] = '%' . $this->filterparams->place . '%';
            }
            
            if (!empty($this->filterparams->approve)) {
                $whereclauses[] = "cap.approve = ?";
                $params[] = $this->filterparams->approve;
            }
            if (!empty($this->filterparams->notapprove)) {
                $whereclauses[] = "cap.approve = 0";
                $params[] = $this->filterparams->notapprove;
            }
            // Add date range filters for the course table only if dates are provided
            if (!empty($this->filterparams->startdateinput) && $this->filterparams->startdateinput > 0) {
                $whereclauses[] = "c.startdate >= ?";
                $params[] = $this->filterparams->startdateinput;
            }
            if (!empty($this->filterparams->enddateinput) && $this->filterparams->enddateinput > 0) {
                $whereclauses[] = "c.enddate <= ?";
                $params[] = $this->filterparams->enddateinput;
            }
        }

        if (!empty($whereclauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereclauses);
        }

        if (!empty($sort)) {
            $sql .= " ORDER BY " . $sort;
        }

        $this->rawdata = $DB->get_records_sql($sql, $params, $offset, $endset);
        if ($useinitialsbar) {
            $this->initialbars(true);
        }
    }

    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;


        $offset = $this->get_page_start();
        $endset = $pagesize;

        //if we compared shortname when use course table it would be more efficient
        $sort = $this->get_sql_sort();
        $sql = "SELECT cap.*, ap.title AS annualplantitle, l.name AS levelname
        FROM {local_annual_plan_course} cap
        JOIN {local_annual_plan} ap ON cap.annualplanid = ap.id
        LEFT JOIN {local_annual_plan_course_level} l ON cap.courselevelid = l.id";
        

        $params = []; // Array to hold SQL parameters
        
        $whereclauses = ["cap.disabled = 0"];
        if ($this->filterparams) {
            if (!empty($this->filterparams->annualplanid)) {
                $whereclauses[] = "cap.annualplanid = ?";
                $params[] = $this->filterparams->annualplanid;
            }
            if (!empty($this->filterparams->category)) {
                $whereclauses[] = "cap.category = ?";
                $params[] = $this->filterparams->category;
            }
            if (!empty($this->filterparams->level)) {
                $whereclauses[] = "l.name LIKE ?";
                $params[] = '%' . $this->filterparams->level . '%';
            }
            
            if (!empty($this->filterparams->coursename)) {
                $whereclauses[] = "cap.coursename LIKE ?";
                $params[] = '%' . $this->filterparams->coursename . '%';
            }
            if (!empty($this->filterparams->courseid)) {
                $whereclauses[] = "cap.courseid LIKE ?";
                $params[] =  '%' . $this->filterparams->courseid . '%';
            }
            if (!empty($this->filterparams->status)) {
                $whereclauses[] = "cap.status LIKE ?";
                $params[] = '%' . $this->filterparams->status . '%';
            }
            if (!empty($this->filterparams->place)) {
                $whereclauses[] = "cap.place LIKE ?";
                $params[] = '%' . $this->filterparams->place . '%';
            }
            
            
            if (!empty($this->filterparams->approve)) {
                $whereclauses[] = "cap.approve = ?";
                $params[] = $this->filterparams->approve;
            }
            if (!empty($this->filterparams->notapprove)) {
                $whereclauses[] = "cap.approve = 0";
                $params[] = $this->filterparams->notapprove;
            }         
        }

        if (!empty($whereclauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereclauses);
        }

        if (!empty($sort)) {
            $sql .= " ORDER BY " . $sort;
        }

        $this->rawdata = $DB->get_records_sql($sql, $params, $offset, $endset);
        if ($useinitialsbar) {
            $this->initialbars(true);
        }
    }
}
