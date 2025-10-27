# Development Guidelines

This document outlines the development practices, standards, and workflows for the Residence Booking plugin.

## Development Environment Setup

### Prerequisites
- Moodle 4.0+ development environment
- PHP 7.4+ (recommended: PHP 8.1+)
- MySQL 8.0+ or PostgreSQL 12+
- **Dependencies**:
  - `local_status` plugin (workflow management)
  - Theme Stream (for custom dialogs and export)

### Initial Setup
1. Clone/place plugin in `moodle/local/residencebooking/`
2. Install `local_status` plugin first
3. Run Moodle upgrade: `Admin → Site administration → Notifications`
4. Configure capabilities for testing roles
5. Enable plugin via `Site administration → Plugins → Local plugins → Residence Booking`

## Code Standards

### PHP Standards
- Follow **Moodle Coding Style Guidelines**
- Use **strict typing** where possible: `declare(strict_types=1);`
- **Namespace**: All classes under `local_residencebooking`
- **Documentation**: PHPDoc blocks for all classes and public methods
- **Error Handling**: Use Moodle exceptions (`moodle_exception`, `coding_exception`)
- **Comments**: Add meaningful comments for complex logic

### JavaScript Standards
- **AMD modules** only (no ES6 modules in Moodle core)
- Use **JSDoc** for function documentation
- Follow **Moodle JavaScript guidelines**
- Minify files using Moodle's grunt/shifter tools
- **Production Code**: Remove all console.log statements and debugging code
- **Error Handling**: Implement silent fails for user-facing features

### Database Standards
- All table names: `local_residencebooking_*`
- Use **foreign keys** for data integrity
- Include **soft delete** fields where appropriate (`deleted` flag)
- **Multilingual fields**: `*_en`, `*_ar` naming convention
- Audit fields: use Moodle convention `timecreated`, `timemodified`, and add `created_by`, `modified_by` where provenance is needed

## File Organization Principles

### Naming Conventions
```
/classes/
  /form/           - Moodle form classes
  /output/         - Output/renderable classes
  /privacy/        - GDPR compliance classes
  /external/       - Web service classes
  workflow_*.php   - Workflow-related classes

/db/
  install.xml      - Initial schema
  upgrade.php      - Version upgrades
  access.php       - Capabilities
  services.php     - Web services

/templates/
  *.mustache      - Template files

/amd/
  /src/           - Source JS files
  /build/         - Compiled JS files

/lang/
  /en/            - English strings
  /ar/            - Arabic strings
```

### Class Architecture
```php
namespace local_residencebooking;

// Forms
class form\booking_form extends \moodleform
class form\admin_form extends \moodleform

// Output
class output\requests_table implements \renderable
class output\dashboard implements \renderable

// Core logic
class workflow_manager
class request_handler
class data_manager
```

## Database Development

### Schema Changes
1. **Always increment version** in `version.php`
2. **Add upgrade step** in `db/upgrade.php`
3. **Test upgrades** from previous versions
4. **Document changes** in version.php comments

### Migration Best Practices
```php
// In upgrade.php
if ($oldversion < 2025061800) {
    // Create table
    $table = new xmldb_table('local_residencebooking_new');
    // ... define fields
    
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
    
    upgrade_plugin_savepoint(true, 2025061800, 'local', 'residencebooking');
}
```

### Multilingual Data
```sql
-- Always include both language fields
ALTER TABLE local_residencebooking_types ADD (
    type_name_en VARCHAR(255) NOT NULL,
    type_name_ar VARCHAR(255) NOT NULL,
    deleted TINYINT(1) DEFAULT 0
);
```

## Workflow Development

### Status Integration
The plugin integrates with `local_status` using hardcoded workflow logic:

```php
// Status constants
const STATUS_INITIAL        = 15;
const STATUS_LEADER1_REVIEW = 16;
const STATUS_LEADER2_REVIEW = 17;
const STATUS_LEADER3_REVIEW = 18;
const STATUS_BOSS_REVIEW    = 19;
const STATUS_APPROVED       = 20;
const STATUS_REJECTED       = 21;

// Rejection behavior: Leader 1 → Final rejection, others → Leader 1 Review
```

### Adding New Workflow Steps
1. **Define new status** in `local_status` plugin
2. **Update constants** in `simple_workflow_manager.php`
3. **Add capability** in `db/access.php`
4. **Update workflow map** in get_workflow_map()
5. **Test transitions** thoroughly

## Frontend Development

### Mustache Templates
- Use **semantic HTML5** elements
- Include **accessibility attributes**
- Follow **Moodle's template guidelines**
- Support **RTL languages** (Arabic)

```mustache
{{#requests}}
<tr data-request-id="{{id}}">
    <td>{{guest_name}}</td>
    <td>{{status_display}}</td>
    <td class="actions">
        {{#can_approve}}
        <button class="btn btn-success" data-action="approve">
            {{#str}}approve, local_residencebooking{{/str}}
        </button>
        {{/can_approve}}
    </td>
</tr>
{{/requests}}
```

### JavaScript Development
```javascript
// AMD module structure
define(['jquery', 'core/ajax'], function($, Ajax) {
    return {
        init: function() {
            // Initialization code
        },
        
        setupAutocomplete: function(selector) {
            // Feature implementation
        }
    };
});
```

### AJAX Guidelines
- **Always validate** user permissions
- **Return JSON** responses consistently
- **Handle errors** gracefully
- **Use Moodle's AJAX framework**
- **Production Code**: Remove debugging statements

```php
// In AJAX endpoint
require_sesskey(); // CSRF protection
require_capability('local/residencebooking:manage', $context);

$response = ['success' => false, 'message' => ''];
try {
    // Process request
    $response['success'] = true;
} catch (Exception $e) {
    $response['message'] = get_string('error', 'local_residencebooking');
}

echo json_encode($response);
```

## Testing Guidelines

### Manual Testing Checklist
- [ ] **Installation** on fresh Moodle site
- [ ] **Upgrade** from previous versions
- [ ] **Permissions** for different roles
- [ ] **Multilingual** interface (English/Arabic)
- [ ] **Workflow** state transitions
- [ ] **AJAX** functionality
- [ ] **Responsive** design on mobile
- [ ] **Accessibility** with screen readers

### Database Testing
- [ ] **Schema validation** after upgrades
- [ ] **Foreign key** constraints working
- [ ] **Soft delete** functionality
- [ ] **Data migration** accuracy

### Integration Testing
- [ ] **local_status** plugin integration
- [ ] **Moodle capabilities** system
- [ ] **Theme compatibility**
- [ ] **Language pack** switching

## Version Management

### Version Number Format
`YYYYMMDD##` - Year, Month, Day, Sequence

Example: `2025061700` = June 17, 2025, first release of the day

### Release Process
1. **Update version.php** with new version and release notes
2. **Test upgrade path** from previous version
3. **Update language strings** if needed
4. **Document changes** in version.php comments
5. **Test on clean Moodle installation**
6. **Remove debugging code** before production release

### Version History Documentation
```php
/**
 * 2025061700 – Add approval_note field & update workflow:
 *   • Added approval_note field for simple workflow manager.
 *   • Updated workflow to use simple hardcoded approach.
 *   • Updated to use local_status workflow capabilities.
 */
```

## Debugging and Troubleshooting

### Common Issues
1. **Permission Denied**: Check capability definitions in `db/access.php`
2. **AJAX Failures**: Verify sesskey and capability checks
3. **Database Errors**: Check foreign key constraints
4. **Template Errors**: Validate Mustache syntax
5. **Workflow Stuck**: Verify status_id values in local_status

### Debug Settings (Development Only)
```php
// In config.php for development
$CFG->debug = E_ALL | E_STRICT;
$CFG->debugdisplay = 1;
$CFG->debugpageinfo = 1;
```

### Logging
```php
// Add debug logging (development only)
debugging('Residence booking: Processing request ID ' . $requestid, DEBUG_DEVELOPER);

// Error logging (production)
error_log('Residence booking error: ' . $e->getMessage());
```

## Security Considerations

### Input Validation
- **Always sanitize** user input using Moodle's cleaning functions
- **Validate dates** and numeric inputs
- **Escape output** in templates

### Permission Checks
```php
// Always check capabilities
require_capability('local/residencebooking:submitrequest', $context);

// Check ownership for editing
if ($request->userid != $USER->id && !has_capability('local/residencebooking:manage', $context)) {
    throw new moodle_exception('nopermission');
}
```

### CSRF Protection
```php
require_sesskey(); // For state-changing operations
```

## Performance Optimization

### Database Queries
- Use **prepared statements** (Moodle handles this automatically)
- **Minimize queries** in loops
- **Use indexes** appropriately
- **Implement pagination** for large datasets

### Caching
```php
// Cache expensive operations
$cache = cache::make('local_residencebooking', 'types');
$types = $cache->get('all_types');
if ($types === false) {
    $types = $DB->get_records('local_residencebooking_types');
    $cache->set('all_types', $types);
}
```

## Production Readiness Checklist

### Code Quality
- [ ] **No debugging statements** (console.log, var_dump, etc.)
- [ ] **Proper error handling** implemented
- [ ] **Input validation** on all endpoints
- [ ] **Security checks** in place
- [ ] **Performance optimized** queries
- [ ] **Documentation updated**

### Testing
- [ ] **All functionality tested** manually
- [ ] **Edge cases covered**
- [ ] **Error scenarios tested**
- [ ] **Upgrade path verified**
- [ ] **Performance tested** under load

### Deployment
- [ ] **Version numbers updated**
- [ ] **Release notes documented**
- [ ] **Backup procedures** in place
- [ ] **Rollback plan** prepared

## Contributing Guidelines

### Pull Request Process
1. **Create feature branch** from develop
2. **Follow coding standards**
3. **Add/update tests** if applicable
4. **Update documentation**
5. **Test thoroughly**
6. **Remove debugging code**
7. **Submit PR with clear description**

### Code Review Checklist
- [ ] Follows Moodle coding standards
- [ ] Security best practices followed
- [ ] Database changes properly handled
- [ ] Language strings updated
- [ ] Documentation updated
- [ ] Backward compatibility maintained
- [ ] No debugging code in production files 