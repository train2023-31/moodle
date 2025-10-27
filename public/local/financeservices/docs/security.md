# Security Documentation

This document outlines the security measures, best practices, and potential vulnerabilities in the Finance Services plugin.

## 🔐 Security Overview

The Finance Services plugin implements multiple layers of security to protect against common web vulnerabilities and ensure data integrity.

## 🛡️ Authentication & Authorization

### 1. Moodle Authentication Integration

**Session Management**:
```php
// All pages require valid Moodle session
require_login();

// Verify user is logged in
if (!isloggedin()) {
    redirect(new moodle_url('/login/index.php'));
}
```

**User Context Validation**:
```php
// Ensure user has access to the system
$context = context_system::instance();
$PAGE->set_context($context);

// Check basic access capability
require_capability('local/financeservices:view', $context);
```

### 2. Capability-Based Authorization

**Permission Matrix**:
| Action | Required Capability | Context Level |
|--------|-------------------|---------------|
| View requests | `local/financeservices:view` | System |
| Submit requests | `local/financeservices:view` | System |
| Approve Level 1 | `local/financeservices:approve_level1` | System |
| Approve Level 2 | `local/financeservices:approve_level2` | System |
| Approve Level 3 | `local/financeservices:approve_level3` | System |
| Final Approval | `local/financeservices:approve_boss` | System |
| Manage Configuration | `local/financeservices:manage` | System |

**Implementation Example**:
```php
// Check specific approval capability
public static function can_user_approve($status_id) {
    $context = context_system::instance();
    
    switch ($status_id) {
        case 9: // Leader 1 Review
            return has_capability('local/financeservices:approve_level1', $context);
        case 10: // Leader 2 Review
            return has_capability('local/financeservices:approve_level2', $context);
        case 11: // Leader 3 Review
            return has_capability('local/financeservices:approve_level3', $context);
        case 12: // Boss Review
            return has_capability('local/financeservices:approve_boss', $context);
        default:
            return false;
    }
}
```

### 3. Record-Level Security

**Ownership Validation**:
```php
// Users can only view/edit their own requests unless they have manage capability
$request = $DB->get_record('local_financeservices', ['id' => $id], '*', MUST_EXIST);

if ($request->user_id !== $USER->id && 
    !has_capability('local/financeservices:manage', context_system::instance())) {
    throw new moodle_exception('nopermission', 'local_financeservices');
}
```

## 🔒 Input Validation & Sanitization

### 1. Parameter Cleaning

**Standard Moodle Parameter Handling**:
```php
// Required parameters with type validation
$courseid = required_param('courseid', PARAM_INT);
$notes = required_param('notes', PARAM_TEXT);
$price = required_param('price', PARAM_FLOAT);

// Optional parameters with defaults
$clause_id = optional_param('clause_id', null, PARAM_INT);
$tab = optional_param('tab', 'add', PARAM_ALPHA);
```

**Parameter Types Used**:
- `PARAM_INT` - Integer values (IDs, amounts)
- `PARAM_FLOAT` - Decimal values (prices)
- `PARAM_TEXT` - Text content (notes, descriptions)
- `PARAM_ALPHA` - Alphabetic strings (tab names, actions)
- `PARAM_ALPHANUMEXT` - Alphanumeric with some symbols

### 2. Form Validation

**Server-Side Validation**:
```php
public function validation($data, $files) {
    $errors = parent::validation($data, $files);
    
    // Business logic validation
    if (empty($data['price_requested']) || $data['price_requested'] <= 0) {
        $errors['price_requested'] = get_string('price_must_be_positive', 'local_financeservices');
    }
    
    if ($data['price_requested'] > 10000) {
        $errors['price_requested'] = get_string('price_exceeds_limit', 'local_financeservices');
    }
    
    // Date validation
    if (!empty($data['date_type_required'])) {
        $requested_date = $data['date_type_required'];
        $current_time = time();
        
        if ($requested_date < $current_time) {
            $errors['date_type_required'] = get_string('date_cannot_be_past', 'local_financeservices');
        }
    }
    
    return $errors;
}
```

### 3. Database Input Safety

**Parameterized Queries**:
```php
// Safe database queries using parameters
$sql = "SELECT fs.*, c.fullname AS course_name
        FROM {local_financeservices} fs
        JOIN {course} c ON fs.course_id = c.id
        WHERE fs.user_id = :userid 
          AND fs.status_id = :status
        ORDER BY fs.date_time_requested DESC";

$params = [
    'userid' => $USER->id,
    'status' => $status_id
];

$records = $DB->get_records_sql($sql, $params);
```

**Never Use Direct String Concatenation**:
```php
// BAD - SQL Injection vulnerability
$sql = "SELECT * FROM {local_financeservices} WHERE user_id = " . $userid;

// GOOD - Parameterized query
$records = $DB->get_records('local_financeservices', ['user_id' => $userid]);
```

## 🛡️ CSRF Protection

### 1. Session Key Validation

**Form-Based CSRF Protection**:
```php
// In form processing
require_sesskey();

// In form definition
protected function definition() {
    $mform = $this->_form;
    // ... form elements ...
    $mform->addElement('hidden', 'sesskey', sesskey());
}
```

**AJAX CSRF Protection**:
```php
// Server-side validation in AJAX handlers
if (!confirm_sesskey($input['sesskey'])) {
    throw new moodle_exception('invalidsesskey');
}
```

```javascript
// Client-side session key inclusion
const data = {
    action: 'approve',
    id: requestId,
    note: note,
    sesskey: M.cfg.sesskey  // Include session key
};

fetch('/local/financeservices/actions/update_request_status.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify(data),
    credentials: 'same-origin'  // Include cookies
});
```

### 2. Double-Submit Cookie Pattern

**Implementation in AJAX**:
```php
// Additional CSRF token validation
$csrf_token = optional_param('csrf_token', '', PARAM_ALPHANUMEXT);
$expected_token = sess_token();

if (!hash_equals($expected_token, $csrf_token)) {
    throw new moodle_exception('invalid_csrf_token');
}
```

## 🔄 Race Condition Prevention

### 1. Client-Side Protection

**Immediate Button Disabling**:
```javascript
function approveRequest(requestId, note) {
    // Prevent double-clicks
    const button = event.target;
    button.disabled = true;
    button.textContent = 'Processing...';
    
    // Add loading spinner
    button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
    
    // Perform AJAX request
    fetch(/* ... */)
    .finally(() => {
        // Re-enable on completion (if still on page)
        if (button) {
            button.disabled = false;
            button.textContent = 'Approve';
        }
    });
}
```

### 2. Server-Side Protection

**Status Validation Before Update**:
```php
public static function approve_request($request_id, $note = '') {
    global $DB;
    
    // Start transaction for atomicity
    $transaction = $DB->start_delegated_transaction();
    
    try {
        // Get current request with lock
        $request = $DB->get_record('local_financeservices', 
            ['id' => $request_id], '*', MUST_EXIST, IGNORE_MULTIPLE);
        
        // Verify current status hasn't changed
        $current_status = $DB->get_field('local_financeservices', 'status_id', 
            ['id' => $request_id]);
        
        if ($current_status !== $request->status_id) {
            throw new moodle_exception('status_changed_concurrent');
        }
        
        // Check if user can still approve
        if (!self::can_user_approve($request->status_id)) {
            throw new moodle_exception('cannot_approve_status');
        }
        
        // Perform update
        $next_status = self::get_next_status_id($request->status_id);
        $request->status_id = $next_status;
        $request->timemodified = time();
        
        if (!empty($note)) {
            $request->approval_note = $note;
        }
        
        $DB->update_record('local_financeservices', $request);
        
        // Commit transaction
        $transaction->allow_commit();
        
        return true;
        
    } catch (Exception $e) {
        $transaction->rollback($e);
        return false;
    }
}
```

## 🚫 XSS Prevention

### 1. Output Escaping

**Template Output Safety**:
```mustache
{{! Safe output - automatically escaped }}
<td>{{course_name}}</td>
<td>{{notes}}</td>

{{! Raw output only for trusted content }}
<td>{{{safe_html_content}}}</td>
```

**PHP Output Escaping**:
```php
// Always escape user-generated content
echo html_writer::tag('p', s($user_note));

// Use format_text for rich content
echo format_text($description, FORMAT_HTML, ['trusted' => false]);

// URL parameter escaping
$url = new moodle_url('/local/financeservices/index.php', [
    'tab' => s($tab),
    'id' => (int)$id
]);
```

### 2. Content Security Policy

**Header Implementation** (in theme or server config):
```php
// Recommended CSP headers
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");
```

## 🔐 Data Protection

### 1. Sensitive Data Handling

**Password/Token Protection**:
```php
// Never store sensitive data in plain text
// Use Moodle's built-in encryption for sensitive fields
$encrypted_data = encrypt_data($sensitive_info);

// Store only hashed values where applicable
$hash = password_hash($password, PASSWORD_DEFAULT);
```

**PII Data Minimization**:
```php
// Only store necessary personal data
// Anonymize data when possible
$record->user_id = $USER->id;  // Store reference, not personal details
$record->notes = clean_text($notes);  // Clean but preserve content
```

### 2. Data Retention

**Automatic Cleanup**:
```php
// Scheduled task to clean old data
class cleanup_old_requests extends \core\task\scheduled_task {
    public function execute() {
        global $DB;
        
        // Delete requests older than retention period (e.g., 7 years)
        $cutoff = time() - (7 * 365 * 24 * 60 * 60);
        
        $DB->delete_records_select('local_financeservices', 
            'date_time_requested < ? AND status_id IN (13, 14)', 
            [$cutoff]);
    }
}
```

## 🔍 Security Monitoring

### 1. Event Logging

**Comprehensive Activity Logging**:
```php
// Log all significant actions
$event = \local_financeservices\event\request_status_changed::create([
    'context' => context_system::instance(),
    'objectid' => $request_id,
    'other' => [
        'old_status' => $old_status,
        'new_status' => $new_status,
        'action' => $action,
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ]
]);
$event->trigger();
```

### 2. Suspicious Activity Detection

**Failed Access Attempts**:
```php
// Log failed authorization attempts
if (!has_capability('local/financeservices:approve_level1', $context)) {
    // Log unauthorized access attempt
    $event = \core\event\user_login_failed::create([
        'context' => context_system::instance(),
        'other' => [
            'reason' => 'insufficient_privileges',
            'attempted_action' => 'approve_request',
            'request_id' => $request_id
        ]
    ]);
    $event->trigger();
    
    throw new moodle_exception('nopermission');
}
```

## ⚠️ Known Security Considerations

### 1. File Upload Security

**If implementing file uploads**:
```php
// Validate file types
$allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'png'];
$file_extension = pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION);

if (!in_array(strtolower($file_extension), $allowed_types)) {
    throw new moodle_exception('invalid_file_type');
}

// Scan for viruses (if antivirus available)
// Store outside web root
// Validate file size
```

### 2. Email Security

**When sending notifications**:
```php
// Validate email addresses
if (!validate_email($email)) {
    throw new moodle_exception('invalid_email');
}

// Prevent email header injection
$subject = str_replace(["\r", "\n"], '', $subject);
$from = str_replace(["\r", "\n"], '', $from);

// Use Moodle's email API
email_to_user($user, $from, $subject, $message);
```

### 3. API Security

**For future API endpoints**:
```php
// Rate limiting
// API key validation
// Request size limits
// JSON parsing limits
```

## 🔧 Security Testing

### 1. Automated Security Testing

**Unit Tests for Security Functions**:
```php
public function test_permission_checks() {
    $user = $this->getDataGenerator()->create_user();
    $this->setUser($user);
    
    // Test that user cannot approve without proper capability
    $result = simple_workflow_manager::can_user_approve(9);
    $this->assertFalse($result);
    
    // Grant capability and test again
    $context = context_system::instance();
    assign_capability('local/financeservices:approve_level1', CAP_ALLOW, 
        $user->id, $context->id);
    
    $result = simple_workflow_manager::can_user_approve(9);
    $this->assertTrue($result);
}
```

### 2. Manual Security Testing

**Security Testing Checklist**:
- [ ] SQL injection testing on all input fields
- [ ] XSS testing on text areas and search fields
- [ ] CSRF testing on form submissions
- [ ] Authorization bypass testing
- [ ] File upload validation testing
- [ ] Session management testing
- [ ] Input validation boundary testing

### 3. Penetration Testing

**Regular Security Assessments**:
- Automated vulnerability scanning
- Manual penetration testing
- Code review for security issues
- Dependency vulnerability checking

## 📋 Security Deployment Checklist

- [ ] All user inputs validated and sanitized
- [ ] Parameterized queries used throughout
- [ ] Session keys validated on all forms
- [ ] Capabilities checked for all actions
- [ ] Error messages don't reveal sensitive information
- [ ] Security headers configured
- [ ] HTTPS enforced for production
- [ ] Regular security updates applied
- [ ] Monitoring and logging in place
- [ ] Backup and recovery procedures tested

This security framework ensures the Finance Services plugin maintains high security standards while providing necessary functionality. 