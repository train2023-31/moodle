# Development Guidelines

This document outlines how to work with and develop the Student Reports plugin.

## Development Environment Setup

### Prerequisites
1. **Moodle Development Environment**: Set up a local Moodle installation (4.0+)
2. **Database**: MySQL/MariaDB or PostgreSQL
3. **Web Server**: Apache/Nginx with PHP support
4. **Version Control**: Git for source code management

### Plugin Installation for Development
1. Clone/copy the plugin to `{moodle}/local/reports/`
2. Visit **Site Administration > Notifications** to install database tables
3. Configure development-specific settings in your Moodle config

## Code Structure and Patterns

### File Organization
```
local/reports/
├── Core Files (version.php, lib.php)
├── Interface Files (index.php, allreports.php)
├── AJAX Endpoints (*_ajax.php)
├── classes/ (OOP code)
├── templates/ (Mustache templates)
├── lang/ (Language strings)
├── db/ (Database definitions)
└── assets/ (Static files)
```

### Coding Standards
- Follow **Moodle Coding Style Guidelines**
- Use proper PHPDoc comments
- Implement capability checks on every page
- Sanitize all user inputs using Moodle parameters
- Use Moodle's database abstraction layer (DML)

### Key Development Patterns

#### 1. AJAX Endpoint Pattern
```php
<?php
// Standard AJAX endpoint structure
require_once(__DIR__ . '/../../config.php');
require_login();

// Get and validate parameters
$param = required_param('param', PARAM_TYPE);

// Check capabilities
require_capability('local/reports:capability', $context);

// Process request
$result = process_request($param);

// Return JSON response
header('Content-Type: application/json');
echo json_encode($result);
```

#### 2. Form Class Pattern
```php
<?php
namespace local_reports\form;
require_once($CFG->libdir . '/formslib.php');

class report_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;
        // Form definition
    }
    
    public function validation($data, $files) {
        // Custom validation
    }
}
```

#### 3. Template Rendering Pattern
```php
<?php
// Prepare template data
$templatedata = [
    'reports' => $reports,
    'contextid' => $context->id,
    'courseid' => $courseid
];

// Render template
$output = $PAGE->get_renderer('local_reports');
echo $output->render_from_template('local_reports/template_name', $templatedata);
```

## Database Development

### Schema Management
- Database changes go in `db/install.xml`
- Use Moodle's XMLDB editor for schema modifications
- Increment version number in `version.php` after DB changes

### Database Operations
```php
<?php
// Use Moodle DML for all database operations
global $DB;

// Insert
$record = new stdClass();
$record->userid = $userid;
$record->courseid = $courseid;
$id = $DB->insert_record('local_reports', $record);

// Update
$record = $DB->get_record('local_reports', ['id' => $id]);
$record->status_id = $newstatus;
$DB->update_record('local_reports', $record);

// Select
$reports = $DB->get_records('local_reports', ['courseid' => $courseid]);
```

## Frontend Development

### JavaScript Integration
- JavaScript files are referenced from theme directory
- Use Moodle's AMD (Asynchronous Module Definition) when possible
- AJAX calls should use Moodle's web service framework

### Modal Dialog Pattern
```javascript
// Example modal usage (referenced in JS files)
window.LOCALREPORTS = {
    courseid: courseid,
    isAllReports: false
};

// Modal handling in external JS files
// - modal.js: Core modal functionality
// - openreportform.js: Form modal handling
// - approve.js: Approval workflow
// - previewreport.js: PDF preview
```

### Template Development
- Use Mustache syntax for templates
- Support both RTL and LTR layouts
- Include proper context data for all dynamic content

## Workflow Development

### Status Workflow System
The plugin uses a capability-based workflow system:

```php
<?php
// Workflow definition in index.php
$workflow = [
    30 => ['next' => 31, 'capability' => 'local/status:reports_workflow_step1'],
    31 => ['next' => 32, 'capability' => 'local/status:reports_workflow_step2'],
    32 => ['next' => 50, 'capability' => 'local/status:reports_workflow_step3'],
];
```

### Adding New Workflow Steps
1. Define new capabilities in `db/access.php`
2. Update workflow arrays in relevant files
3. Add language strings for new statuses
4. Update approval/disapproval logic

## Testing and Debugging

### Development Testing
1. **Unit Testing**: Write PHPUnit tests for core functionality
2. **Integration Testing**: Test AJAX endpoints manually
3. **User Interface Testing**: Test all user interactions
4. **Permission Testing**: Verify capability checks work correctly

### Debugging Tools
- Use Moodle's debugging features: `$CFG->debug` and `$CFG->debugdisplay`
- Enable developer mode for detailed error messages
- Use browser developer tools for AJAX debugging
- Check Moodle logs for database and capability errors

### Common Issues
1. **Capability Errors**: Ensure proper capability checks
2. **AJAX Failures**: Check parameter validation and JSON responses
3. **Template Errors**: Verify template data structure
4. **Database Issues**: Check field types and constraints

## Adding New Features

### New Report Fields
1. Add field to `db/install.xml`
2. Update form class in `classes/form/report_form.php`
3. Add language strings in `lang/*/local_reports.php`
4. Update templates to display new fields
5. Modify AJAX endpoints to handle new data

### New Capabilities
1. Define in `db/access.php`
2. Add to appropriate archetypes
3. Implement checks in relevant files
4. Add language strings for capability names

### New Templates
1. Create `.mustache` file in `templates/`
2. Add corresponding output renderer if needed
3. Include proper context data
4. Test with sample data

## Code Review Checklist

Before committing code, ensure:
- [ ] All user inputs are properly sanitized
- [ ] Capability checks are in place
- [ ] Language strings are used (no hardcoded text)
- [ ] Database operations use Moodle DML
- [ ] PHPDoc comments are complete
- [ ] Code follows Moodle style guidelines
- [ ] AJAX endpoints return proper JSON
- [ ] Templates handle empty data gracefully
- [ ] Version number is updated if needed

## Performance Considerations

- Use efficient database queries (avoid N+1 queries)
- Implement proper caching where appropriate
- Optimize large dataset handling
- Consider pagination for large report lists
- Minimize AJAX requests where possible

## Security Guidelines

- Always use `required_param()` and `optional_param()`
- Implement proper capability checks
- Validate all form inputs
- Use CSRF protection for forms
- Sanitize output to prevent XSS
- Follow Moodle security guidelines 