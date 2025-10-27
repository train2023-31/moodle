# Development Guide

This document provides guidelines and best practices for developing and maintaining the participant plugin.

## Development Environment Setup

### Prerequisites
- Moodle 4.1 or later
- PHP 7.4 or later
- MySQL/PostgreSQL database
- Web server (Apache/Nginx)

### Plugin Dependencies
- **Required**: `local/status` plugin (for workflow capabilities)
- **Optional**: Compatible theme with CSV export functionality

## Code Standards

### Moodle Coding Standards
Follow Moodle's coding standards:
- Use proper indentation (4 spaces)
- Follow Moodle naming conventions
- Include proper PHPDoc comments
- Use Moodle's database API
- Follow security best practices

### File Organization
```
local/participant/
├── classes/           # All class files
│   ├── form/         # Form classes extend moodleform
│   └── local/        # Local helper classes
├── db/               # Database definitions
├── lang/             # Language strings
├── templates/        # Mustache templates
├── js/               # JavaScript files
└── actions/          # Action handlers
```

## Development Workflow

### 1. Making Changes

#### Adding New Features
1. **Plan**: Document the feature requirements
2. **Database**: Update `db/install.xml` and `db/upgrade.php` if needed
3. **Language**: Add new strings to `lang/en/local_participant.php`
4. **Classes**: Create or modify classes in appropriate directories
5. **Templates**: Update mustache templates if UI changes are needed
6. **Test**: Thoroughly test all functionality

#### Modifying Existing Features
1. **Backup**: Always backup before making changes
2. **Version**: Update version number in `version.php`
3. **Database**: Add upgrade steps if database changes are needed
4. **Language**: Update or add language strings
5. **Test**: Test both new and existing functionality

### 2. Database Management

#### Schema Changes
When modifying the database schema:

1. **Update install.xml**:
   ```xml
   <!-- Add new tables or modify existing ones -->
   <TABLE NAME="local_participant_new_table">
       <FIELDS>
           <FIELD NAME="id" TYPE="int" LENGTH="10" NOTNULL="true" SEQUENCE="true"/>
           <!-- Add other fields -->
       </FIELDS>
   </TABLE>
   ```

2. **Add upgrade steps in upgrade.php**:
   ```php
   if ($oldversion < 2025071303) {
       // Upgrade code here
       $table = new xmldb_table('local_participant_new_table');
       // Define table structure
       if (!$dbman->table_exists($table)) {
           $dbman->create_table($table);
       }
       upgrade_plugin_savepoint(true, 2025071303, 'local', 'participant');
   }
   ```

3. **Update version.php**:
   ```php
   $plugin->version = 2025071303; // Increment version
   ```

### 3. Language String Management

#### Adding New Strings
1. Add to English file first: `lang/en/local_participant.php`
2. Add corresponding Arabic translation: `lang/ar/local_participant.php`
3. Use descriptive keys and clear, concise text

#### String Naming Convention
```php
// Good examples
$string['addrequest'] = 'Add Request';
$string['viewrequests'] = 'View Requests';
$string['invalidnumber'] = 'Please enter a valid number';

// Use prefixes for related strings
$string['status_pending'] = 'Pending';
$string['status_approved'] = 'Approved';
$string['status_rejected'] = 'Rejected';
```

### 4. Form Development

#### Creating New Forms
Extend `moodleform` and follow these guidelines:

```php
class new_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        
        // Add form elements
        $mform->addElement('text', 'fieldname', get_string('fieldlabel', 'local_participant'));
        $mform->setType('fieldname', PARAM_TEXT);
        $mform->addRule('fieldname', get_string('required'), 'required', null, 'client');
        
        // Add buttons
        $this->add_action_buttons();
    }
    
    public function validation($data, $files) {
        // Custom validation logic
        return parent::validation($data, $files);
    }
}
```

### 5. Workflow System Development

#### Understanding the Workflow
The plugin uses a status-based workflow system:

```php
// Status constants in simple_workflow_manager.php
STATUS_INITIAL (56)        // New request
STATUS_LEADER1_REVIEW (57) // First review level
STATUS_LEADER2_REVIEW (58) // Second review level
STATUS_LEADER3_REVIEW (59) // Third review level
STATUS_BOSS_REVIEW (60)    // Final review level
STATUS_APPROVED (61)       // Approved
STATUS_REJECTED (62)       // Rejected
```

#### Modifying Workflow
To modify the workflow:

1. **Update constants** in `simple_workflow_manager.php`
2. **Modify workflow map** to define new transitions
3. **Update capabilities** in the status plugin
4. **Test all workflow paths**

#### Adding New Actions
Create new action files in `/actions/` directory:

```php
// actions/new_action.php
<?php
require_once('../../../config.php');
require_once('../classes/simple_workflow_manager.php');

// Capability check
require_capability('appropriate_capability', $context);

// Process the action
$workflow_manager = new \local_participant\simple_workflow_manager();
// Handle the action logic
```

## Testing Guidelines

### Manual Testing Checklist
- [ ] Test all form submissions with valid data
- [ ] Test form validation with invalid data
- [ ] Test permission restrictions for different user roles
- [ ] Test workflow transitions at each stage
- [ ] Test pagination and filtering functionality
- [ ] Test both English and Arabic interfaces
- [ ] Test CSV export functionality

### Database Testing
- [ ] Verify all database operations work correctly
- [ ] Test upgrade scripts from previous versions
- [ ] Check for data integrity after operations
- [ ] Verify foreign key relationships

### Security Testing
- [ ] Verify capability checks on all pages
- [ ] Test for SQL injection vulnerabilities
- [ ] Check XSS protection in form inputs
- [ ] Validate CSRF protection

## Debugging

### Common Issues

#### Database Errors
- Check `db/install.xml` syntax
- Verify upgrade script logic in `db/upgrade.php`
- Ensure version numbers are incremented properly

#### Permission Issues
- Verify capability definitions in `db/access.php`
- Check capability assignments in user roles
- Ensure context levels are appropriate

#### Language Issues
- Check for missing language strings
- Verify string keys match usage in code
- Test RTL display for Arabic interface

### Debug Mode
Enable Moodle debugging:
```php
// In config.php
$CFG->debug = E_ALL | E_STRICT;
$CFG->debugdisplay = 1;
```

## Performance Considerations

### Database Optimization
- Use appropriate indexes on frequently queried columns
- Implement pagination for large datasets
- Use Moodle's database API efficiently
- Cache frequently accessed data when appropriate

### JavaScript Optimization
- Minimize external JavaScript dependencies
- Use Moodle's AMD module system
- Implement progressive enhancement

## Security Best Practices

### Input Validation
- Always validate and sanitize user input
- Use Moodle's PARAM_* constants for validation
- Implement server-side validation for all forms

### Capability Checks
- Check capabilities on every page
- Use appropriate context levels
- Implement fine-grained permissions

### SQL Security
- Always use Moodle's database API
- Use placeholders for dynamic queries
- Never concatenate user input into SQL

## Deployment

### Version Management
1. Update `version.php` with new version number
2. Test upgrade script thoroughly
3. Document changes in release notes
4. Tag releases in version control

### Migration Strategy
1. Backup production database
2. Test upgrade on staging environment
3. Deploy during maintenance window
4. Verify functionality after deployment
5. Monitor for issues post-deployment 