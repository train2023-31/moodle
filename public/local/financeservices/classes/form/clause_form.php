<?php
// ==================================================================
//  FORM  ➜  Add / Edit Clause   (now with amount field)
// ==================================================================

namespace local_financeservices\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Form for adding or editing a clause (English & Arabic names + amount).
 */
class clause_form extends \moodleform {

    /**
     * Make sure the action URL defaults to the current page (POST back).
     */
    public function __construct($actionurl = null, $customdata = null, $method = 'post',
                                $target = '', $attributes = null, $editable = true) {
        global $PAGE;

        if (empty($actionurl)) {
            $actionurl = $PAGE->url; // send submission back to the same page
        }
        parent::__construct($actionurl, $customdata, $method, $target, $attributes, $editable);
    }

    /**
     * Define form fields & validation rules.
     */
    public function definition() {
        $mform = $this->_form;

        // ── Clause (English) ────────────────────────────────────────
        $mform->addElement('text', 'clause_name_en',
            get_string('clausenameen', 'local_financeservices'));
        $mform->setType('clause_name_en', PARAM_TEXT);
        $mform->addRule('clause_name_en', get_string('required'), 'required', null, 'client');

        // ── Clause (Arabic) ─────────────────────────────────────────
        $mform->addElement('text', 'clause_name_ar',
            get_string('clausenamear', 'local_financeservices'));
        $mform->setType('clause_name_ar', PARAM_TEXT);
        $mform->addRule('clause_name_ar', get_string('required'), 'required', null, 'client');

        // ── 🆕 Amount (numeric) ─────────────────────────────────────
        $mform->addElement('text', 'amount', get_string('clauseamount', 'local_financeservices'));
        $mform->setType('amount', PARAM_FLOAT);
        $mform->addRule('amount', get_string('required'), 'required', null, 'client');
        $mform->addRule('amount', get_string('notnumeric', 'local_financeservices'), 'numeric', null, 'client');

        // ── 🆕 Clause Year (integer) ────────────────────────────────
        $currentyear = (int)date('Y');
        $mform->addElement('text', 'clause_year', get_string('clauseyear', 'local_financeservices'));
        $mform->setType('clause_year', PARAM_INT);
        $mform->setDefault('clause_year', $currentyear);
        $mform->addRule('clause_year', get_string('required'), 'required', null, 'client');

        // Hidden fields
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'deleted');
        $mform->setType('deleted', PARAM_INT);
        $mform->setDefault('deleted', 0);
        
        // Audit fields (hidden)
        $mform->addElement('hidden', 'created_by');
        $mform->setType('created_by', PARAM_INT);
        
        $mform->addElement('hidden', 'created_date');
        $mform->setType('created_date', PARAM_INT);
        
        $mform->addElement('hidden', 'modified_by');
        $mform->setType('modified_by', PARAM_INT);
        
        $mform->addElement('hidden', 'modified_date');
        $mform->setType('modified_date', PARAM_INT);
        
        $mform->addElement('hidden', 'initial_amount');
        $mform->setType('initial_amount', PARAM_FLOAT);
        
        // ── Buttons ────────────────────────────────────────────────
        $this->add_action_buttons(true, get_string('savechanges', 'local_financeservices'));
    }

    /**
     * Extra server-side validation (belt & braces).
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        // Trim data for validation to prevent whitespace-only entries
        $trimmed_name_en = trim($data['clause_name_en']);
        $trimmed_name_ar = trim($data['clause_name_ar']);

        if ($trimmed_name_en === '') {
            $errors['clause_name_en'] = get_string('required');
        }
        if ($trimmed_name_ar === '') {
            $errors['clause_name_ar'] = get_string('required');
        }
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            $errors['amount'] = get_string('invalidnumber', 'local_financeservices');
        }

        // Validate year is current year or future; disallow past years
        $currentyear = (int)date('Y');
        $year = (int)$data['clause_year'];
        if ($year < $currentyear) {
            $errors['clause_year'] = get_string('clauseyear_past_not_allowed', 'local_financeservices');
        }

        // Enforce uniqueness of clause name per year (English and Arabic separately)
        // Use trimmed values to prevent duplicate entries with extra spaces
        if (empty($errors['clause_year']) && empty($errors['clause_name_en'])) {
            $exists = $DB->record_exists_select(
                'local_financeservices_clause',
                'LOWER(TRIM(clause_name_en)) = LOWER(?) AND clause_year = ? AND deleted = 0' . (!empty($data['id']) ? ' AND id <> ?' : ''),
                !empty($data['id']) ? [$trimmed_name_en, $year, (int)$data['id']] : [$trimmed_name_en, $year]
            );
            if ($exists) {
                $errors['clause_name_en'] = get_string('clauseyear_unique', 'local_financeservices');
            }
        }

        if (empty($errors['clause_year']) && empty($errors['clause_name_ar'])) {
            $existsar = $DB->record_exists_select(
                'local_financeservices_clause',
                'LOWER(TRIM(clause_name_ar)) = LOWER(?) AND clause_year = ? AND deleted = 0' . (!empty($data['id']) ? ' AND id <> ?' : ''),
                !empty($data['id']) ? [$trimmed_name_ar, $year, (int)$data['id']] : [$trimmed_name_ar, $year]
            );
            if ($existsar) {
                $errors['clause_name_ar'] = get_string('clauseyear_unique', 'local_financeservices');
            }
        }

        return $errors;
    }
}
