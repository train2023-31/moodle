# Security Features Documentation

This document outlines the security features, mechanisms, and best practices implemented in the Computer Service plugin.

## 🛡️ Security Overview

The Computer Service plugin implements multiple layers of security to protect against common threats while maintaining usability and integration with Moodle's security framework.

### Security Principles
- **Defense in Depth**: Multiple security layers
- **Least Privilege**: Minimal required permissions
- **Fail Secure**: Secure defaults and error handling
- **Audit Trail**: Comprehensive logging of actions
- **Data Protection**: Sensitive information safeguards

### Threat Model
The plugin protects against:
- **Unauthorized Access**: Users accessing data/functions without permission
- **Data Manipulation**: Tampering with requests or device data
- **Cross-Site Scripting (XSS)**: Malicious script injection
- **Cross-Site Request Forgery (CSRF)**: Unauthorized actions via forged requests
- **SQL Injection**: Database manipulation through malicious input
- **Session Hijacking**: Stealing or manipulating user sessions
- **Privilege Escalation**: Users gaining unauthorized capabilities

## 🔐 Authentication and Authorization

### Authentication Layer

#### Moodle Integration
```php
// All pages require login
require_login();

// Context-aware authentication
$context = context_system::instance();
$PAGE->set_context($context);
```

The plugin leverages Moodle's robust authentication system:
- **Single Sign-On**: Uses existing Moodle sessions
- **Session Management**: Automatic session handling
- **Login Requirements**: All functionality requires authentication
- **Guest Access**: No guest access allowed

#### Session Security
```php
// Session validation for AJAX requests
if (!confirm_sesskey($sesskey)) {
    die(json_encode(['success' => false, 'error' => 'Invalid session key']));
}
```

### Authorization Framework

#### Capability-Based Access Control
The plugin implements granular permissions through Moodle's capability system:

```php
// Capability definitions in db/access.php
'local/computerservice:submitrequest' => array(
    'captype' => 'write',
    'contextlevel' => CONTEXT_SYSTEM,
    'archetypes' => array(
        'user' => CAP_ALLOW,
        'teacher' => CAP_ALLOW,
    ),
),

'local/computerservice:managerequests' => array(
    'captype' => 'write',
    'contextlevel' => CONTEXT_SYSTEM,
    'archetypes' => array(
        'manager' => CAP_ALLOW,
    ),
),
```

#### Permission Enforcement
```php
// Example capability check
require_capability('local/computerservice:managerequests', $context);

// User-specific capability check
if (has_capability('local/computerservice:approve_leader1', $context, $userid)) {
    // User can approve at this level
}
```

#### Workflow-Specific Authorization
```php
public static function can_user_manage_status($userid, $status_id) {
    $context = context_system::instance();
    
    // Check global management capability first
    if (has_capability('local/computerservice:managerequests', $context, $userid)) {
        return true;
    }
    
    // Check workflow-specific capability
    $specific_capability = self::get_capability_for_status($status_id);
    if ($specific_capability && has_capability($specific_capability, $context, $userid)) {
        return true;
    }
    
    return false;
}
```

## 🔒 Input Validation and Sanitization

### Parameter Validation

#### Moodle Parameter Functions
All user inputs are validated using Moodle's parameter validation:

```php
// Required parameters with type checking
$requestid = required_param('requestid', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);
$courseid = required_param('courseid', PARAM_INT);

// Optional parameters with defaults
$comments = optional_param('comments', '', PARAM_TEXT);
$approval_note = optional_param('approval_note', '', PARAM_TEXT);

// Raw parameters for specific validation
$sesskey = required_param('sesskey', PARAM_RAW);
```

#### Parameter Types
- `PARAM_INT`: Integer values only
- `PARAM_ALPHA`: Alphabetic characters only
- `PARAM_TEXT`: Clean text, HTML stripped
- `PARAM_RAW`: No cleaning (requires manual validation)
- `PARAM_EMAIL`: Valid email format
- `PARAM_URL`: Valid URL format

### Data Sanitization

#### Output Sanitization
```php
// HTML output sanitization
echo format_text($user_input, FORMAT_HTML);

// Plain text output
echo s($user_text); // Equivalent to htmlspecialchars()

// URL sanitization
$url = new moodle_url('/local/computerservice/index.php', ['id' => $id]);
```

#### Database Input Sanitization
```php
// All database operations use prepared statements
$record = (object)[
    'userid' => clean_param($userid, PARAM_INT),
    'comments' => clean_param($comments, PARAM_TEXT),
    'timecreated' => time(),
];

$DB->insert_record('local_computerservice_requests', $record);
```

## 🚫 CSRF Protection

### Session Key Implementation

#### Form Protection
```php
// In forms (classes/form/request_form.php)
public function definition() {
    $mform = $this->_form;
    
    // Automatic CSRF protection
    $mform->addElement('hidden', 'sesskey', sesskey());
    $mform->setType('sesskey', PARAM_RAW);
}
```

#### AJAX Protection
```php
// In AJAX handlers (actions/update_request_status.php)
$sesskey = required_param('sesskey', PARAM_RAW);

if (!confirm_sesskey($sesskey)) {
    die(json_encode([
        'success' => false, 
        'error' => 'Invalid session key'
    ]));
}
```

#### JavaScript Session Key
```javascript
// In templates and JavaScript
$.ajax({
    url: M.cfg.wwwroot + '/local/computerservice/actions/update_request_status.php',
    type: 'POST',
    data: {
        requestid: requestId,
        action: 'approve',
        sesskey: M.cfg.sesskey  // Moodle provides session key
    },
    success: function(response) {
        // Handle response
    }
});
```

### CSRF Best Practices
- **All State Changes**: Require session key for any data modification
- **AJAX Requests**: Include session key in all AJAX calls
- **Form Submissions**: Automatic session key inclusion
- **Token Validation**: Server-side validation of all tokens

## 🔍 SQL Injection Prevention

### Database Access Layer

#### Moodle Database API
All database operations use Moodle's secure database API:

```php
// Safe parameterized queries
$users = $DB->get_records_sql('
    SELECT u.id, u.firstname, u.lastname 
    FROM {user} u 
    WHERE u.id = ?
', [$userid]);

// Safe record operations
$record = $DB->get_record('local_computerservice_requests', ['id' => $requestid], '*', MUST_EXIST);

// Safe updates with conditions
$DB->update_record('local_computerservice_requests', $update_record);
```

#### Query Building
```php
// Safe dynamic query building
$conditions = [];
$params = [];

if ($courseid) {
    $conditions[] = 'courseid = ?';
    $params[] = $courseid;
}

if ($status_id) {
    $conditions[] = 'status_id = ?';
    $params[] = $status_id;
}

$where = '';
if (!empty($conditions)) {
    $where = 'WHERE ' . implode(' AND ', $conditions);
}

$sql = "SELECT * FROM {local_computerservice_requests} $where";
$records = $DB->get_records_sql($sql, $params);
```

### Unsafe Practices Avoided
- **String Concatenation**: Never concatenate user input into SQL
- **Direct Queries**: Always use Moodle's database API
- **Unparameterized Queries**: All parameters properly escaped
- **Dynamic Table Names**: Table names hardcoded or validated

## 🌐 XSS Protection

### Output Encoding

#### Template Security
```mustache
{{!-- Safe text output --}}
<td>{{username}}</td>
<td>{{coursename}}</td>

{{!-- HTML content (pre-sanitized) --}}
<td>{{{formatted_content}}}</td>

{{!-- URL attributes --}}
<a href="{{url}}">{{link_text}}</a>
```

#### PHP Output Security
```php
// Safe text output
echo s($user_text);

// Safe HTML output (after cleaning)
echo format_text($user_html, FORMAT_HTML);

// Safe attribute output
echo 'data-id="' . s($record_id) . '"';
```

### Content Security Policy

#### Recommended CSP Headers
```apache
# In .htaccess or server configuration
Header set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'"
```

### Input Sanitization for XSS
```php
// Form validation with cleaning
public function validation($data, $files) {
    $errors = parent::validation($data, $files);
    
    // Clean and validate text inputs
    $data['comments'] = clean_param($data['comments'], PARAM_TEXT);
    
    // Validate length
    if (strlen($data['comments']) > 1000) {
        $errors['comments'] = 'Comments too long';
    }
    
    return $errors;
}
```

## 🔄 Race Condition Prevention

### Atomic Operations

#### Database-Level Protection
```php
public static function approve_request($requestid, $userid, $sesskey, $approval_note = '') {
    global $DB;
    
    // Start transaction
    $transaction = $DB->start_delegated_transaction();
    
    try {
        // Get current record with lock
        $request = $DB->get_record('local_computerservice_requests', 
            ['id' => $requestid], '*', MUST_EXIST);
        
        // Validate current state
        if ($request->status_id >= self::STATUS_APPROVED) {
            throw new moodle_exception('Request already processed');
        }
        
        // Update with timestamp check
        $update = [
            'id' => $requestid,
            'status_id' => self::get_next_status($request->status_id),
            'timemodified' => time(),
        ];
        
        if (!empty($approval_note)) {
            $update['approval_note'] = $approval_note;
        }
        
        $DB->update_record('local_computerservice_requests', (object)$update);
        
        // Commit transaction
        $transaction->allow_commit();
        
        return ['success' => true, 'new_status' => $update['status_id']];
        
    } catch (Exception $e) {
        $transaction->rollback($e);
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
```

#### Timestamp-Based Validation
```php
// Check for concurrent modifications
if ($request->timemodified > $expected_timestamp) {
    throw new moodle_exception('Record modified by another user');
}
```

### Concurrency Best Practices
- **Database Transactions**: Use for multi-step operations
- **Optimistic Locking**: Check timestamps before updates
- **Atomic Updates**: Single query updates when possible
- **State Validation**: Verify current state before transitions

## 📝 Audit and Logging

### Action Logging

#### Database Audit Trail
```php
// All status changes are logged
$record = (object)[
    'userid' => $USER->id,
    'requestid' => $requestid,
    'old_status' => $old_status,
    'new_status' => $new_status,
    'action_type' => $action, // 'approve' or 'reject'
    'notes' => $notes,
    'timestamp' => time(),
    'ip_address' => getremoteaddr(),
];

$DB->insert_record('local_computerservice_audit', $record);
```

#### Moodle Event System
```php
// Trigger Moodle events for integration
$event = \local_computerservice\event\request_approved::create([
    'context' => context_system::instance(),
    'userid' => $USER->id,
    'objectid' => $requestid,
    'other' => [
        'old_status' => $old_status,
        'new_status' => $new_status,
    ]
]);
$event->trigger();
```

### Security Monitoring

#### Failed Access Attempts
```php
// Log failed capability checks
if (!has_capability('local/computerservice:managerequests', $context)) {
    debugging("User {$USER->id} attempted unauthorized access to manage requests", DEBUG_DEVELOPER);
    
    // Log to security log if available
    if (function_exists('security_log')) {
        security_log("Unauthorized access attempt by user {$USER->id}");
    }
    
    throw new required_capability_exception($context, 'local/computerservice:managerequests', 'nopermissions', '');
}
```

#### Suspicious Activity Detection
```php
// Rate limiting example
$recent_actions = $DB->count_records_select(
    'local_computerservice_audit',
    'userid = ? AND timestamp > ?',
    [$USER->id, time() - 300] // 5 minutes
);

if ($recent_actions > 10) {
    // Too many actions in short time
    throw new moodle_exception('Rate limit exceeded');
}
```

## 🔧 Security Configuration

### Recommended Security Settings

#### Moodle Configuration
```php
// In config.php
$CFG->forcelogin = true;           // Require login for all pages
$CFG->forceloginforprofiles = true; // Require login for user profiles
$CFG->opentogoogle = false;        // Prevent Google indexing
$CFG->enablerssfeeds = false;      // Disable RSS if not needed
$CFG->maxbytes = 2097152;          // Limit file uploads (2MB)
```

#### Plugin-Specific Settings
```php
// In plugin configuration
define('LOCAL_COMPUTERSERVICE_MAX_REQUESTS_PER_USER_PER_DAY', 10);
define('LOCAL_COMPUTERSERVICE_MAX_DEVICES_PER_REQUEST', 50);
define('LOCAL_COMPUTERSERVICE_REQUIRE_JUSTIFICATION', true);
```

### Capability Assignment Guidelines

#### Principle of Least Privilege
```php
// Give users only necessary permissions
$role_assignments = [
    'teacher' => [
        'local/computerservice:submitrequest'
    ],
    'manager' => [
        'local/computerservice:submitrequest',
        'local/computerservice:managerequests',
        'local/computerservice:approve_leader1'
    ],
    'admin' => [
        // All capabilities
    ]
];
```

#### Regular Permission Audits
- Review capability assignments quarterly
- Remove unnecessary permissions
- Monitor for privilege escalation
- Document permission changes

## 🚨 Incident Response

### Security Incident Types

#### Data Breach Response
1. **Immediate Actions**:
   - Identify affected data
   - Contain the breach
   - Assess impact scope
   - Notify relevant authorities

2. **Investigation**:
   - Review audit logs
   - Identify attack vectors
   - Document timeline
   - Preserve evidence

3. **Recovery**:
   - Apply security patches
   - Reset compromised accounts
   - Update security measures
   - Monitor for continued threats

#### Privilege Escalation Response
1. **Detection**: Monitor for unusual capability usage
2. **Containment**: Temporarily revoke suspected accounts
3. **Investigation**: Review permission changes and access logs
4. **Remediation**: Fix permission issues and update processes

### Security Monitoring Checklist

#### Daily Monitoring
- [ ] Review error logs for security-related errors
- [ ] Check for failed login attempts
- [ ] Monitor unusual data access patterns
- [ ] Verify system integrity

#### Weekly Monitoring
- [ ] Review audit logs for suspicious activity
- [ ] Check for new user accounts and permission changes
- [ ] Verify backup integrity
- [ ] Test security controls

#### Monthly Monitoring
- [ ] Conduct security vulnerability scan
- [ ] Review and update security documentation
- [ ] Test incident response procedures
- [ ] Update security training materials

## 📚 Security Best Practices

### Development Security
- **Secure Coding**: Follow Moodle coding standards
- **Code Review**: Peer review all security-related code
- **Testing**: Include security testing in QA process
- **Documentation**: Document security decisions and rationale

### Deployment Security
- **Environment Hardening**: Secure server configuration
- **Regular Updates**: Keep Moodle and plugins updated
- **Backup Security**: Encrypt and secure backups
- **Monitoring**: Implement comprehensive logging and monitoring

### User Security
- **Training**: Educate users about security best practices
- **Strong Passwords**: Enforce password policies
- **Regular Reviews**: Periodic access reviews
- **Incident Reporting**: Clear procedures for reporting security issues

---

This security documentation provides comprehensive coverage of the security features and considerations for the Computer Service plugin, ensuring robust protection against common threats while maintaining usability and integration with Moodle's security framework. 