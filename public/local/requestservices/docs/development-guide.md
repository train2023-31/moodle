# Development Guide

## Getting Started

### Prerequisites
- Moodle 4.0 or higher
- PHP 7.4 or higher
- Understanding of Moodle plugin development
- Familiarity with Mustache templating system

### Development Environment Setup
1. Clone/download the plugin to `/local/requestservices/`
2. Install dependencies (if any)
3. Run Moodle upgrade to install plugin
4. Enable plugin in Site Administration

## Coding Standards

### PHP Standards
- Follow [Moodle Coding Style](https://docs.moodle.org/dev/Coding_style)
- Use PSR-4 autoloading for classes
- Include proper PHPDoc comments
- Always include `defined('MOODLE_INTERNAL') || die();` security check

### File Structure Standards
```php
<?php
defined('MOODLE_INTERNAL') || die();

// Include required files
require_once($CFG->dirroot . '/path/to/file.php');

// Class definitions or functional code
```

### Template Standards
- Use semantic HTML5 elements
- Follow Moodle Bootstrap conventions
- Include proper accessibility attributes
- Use responsive design principles

### Language String Standards
- Keep strings descriptive but concise
- Use lowercase with underscores for keys
- Group related strings together
- Provide meaningful context in comments

## Architecture Patterns

### Tab System Architecture
```
index.php (Main Controller)
    ↓
Tab Navigation System
    ↓
Individual Tab Files (/tabs/)
    ↓
Subtab System (/tabs/subtabs/)
    ↓
Template Rendering (/templates/)
```

### Data Flow Pattern
1. **Controller** (index.php) handles routing
2. **Tab files** manage specific functionality
3. **Renderer classes** prepare data for templates
4. **Mustache templates** handle presentation
5. **Language strings** provide localization

### Permission Flow
1. Check course context
2. Verify user login
3. Check `local/requestservices:view` capability
4. Grant or deny access accordingly

## Adding New Features

### Adding a New Service Tab

1. **Create Tab File**
```php
// /tabs/newservice.php
<?php
defined('MOODLE_INTERNAL') || die();

require_capability('local/requestservices:view', $context);

// Tab-specific logic here
```

2. **Add to Tab Array** (in index.php)
```php
$tabnames = ['allrequests', 'computerservicesTab', 'financialservices',
            'registeringroom','requestparticipant', 'residencebooking', 'newservice'];
```

3. **Create Language Strings**
```php
// /lang/en/local_requestservices.php
$string['newservice'] = 'New Service';
```

4. **Create Template** (if needed)
```mustache
{{!-- /templates/newserviceview.mustache --}}
<div class="new-service-container">
    {{#requests}}
        <!-- Template content -->
    {{/requests}}
</div>
```

5. **Create Renderer Class** (if needed)
```php
// /classes/output/newserviceview.php
<?php
namespace local_requestservices\output;

class newserviceview implements \renderable, \templatable {
    public function export_for_template(\renderer_base $output) {
        // Data preparation logic
        return $data;
    }
}
```

### Adding Subtab Views

1. **Create Subtab File**
```php
// /tabs/subtabs/newserviceview.php
<?php
defined('MOODLE_INTERNAL') || die();

// Subtab logic here
```

2. **Add to Subtab Array** (in allrequests.php)
```php
$subtabnames = [
    'computerservicesview' => get_string('computerservicesTab', 'local_requestservices'),
    'newserviceview' => get_string('newservice', 'local_requestservices'),
    // ... other subtabs
];
```

### Database Integration

If your feature requires database operations:

1. **Create Database Schema** (if needed)
```php
// /db/install.xml
// Define table structure following Moodle standards
```

2. **Use Moodle Database API**
```php
global $DB;

// Select
$records = $DB->get_records('table_name', $conditions);

// Insert
$DB->insert_record('table_name', $dataobject);

// Update
$DB->update_record('table_name', $dataobject);
```

## Testing Guidelines

### Manual Testing Checklist
- [ ] Plugin installs without errors
- [ ] All tabs load correctly
- [ ] Forms submit successfully
- [ ] Templates render properly
- [ ] Permissions work as expected
- [ ] Multi-language support functions
- [ ] Responsive design works on mobile

### Capability Testing
- [ ] Teachers can access the plugin
- [ ] Students cannot access (unless granted permission)
- [ ] Admin can access all features
- [ ] Guest users are properly restricted

### Cross-browser Testing
- [ ] Chrome/Chromium
- [ ] Firefox
- [ ] Safari
- [ ] Edge
- [ ] Mobile browsers

## Troubleshooting Development Issues

### Common Issues

**Plugin Not Appearing in Navigation**
- Check capability definition in `/db/access.php`
- Verify `lib.php` navigation function
- Clear Moodle caches
- Check user permissions

**Templates Not Rendering**
- Verify template file names match class names
- Check renderer class implements correct interfaces
- Ensure data is properly exported
- Clear template caches

**Language Strings Not Working**
- Check file naming convention
- Verify string key names
- Clear language caches
- Check for syntax errors in language files

**Permission Denied Errors**
- Check capability definitions
- Verify context levels
- Check role assignments
- Review capability checking code

### Debugging Tips

1. **Enable Debugging**
```php
// In config.php
$CFG->debug = 32767;
$CFG->debugdisplay = 1;
```

2. **Use Moodle Debugging Functions**
```php
debugging('Debug message', DEBUG_DEVELOPER);
error_log('Log message');
```

3. **Check Moodle Logs**
- Site Administration → Reports → Logs
- Check web server error logs

## Best Practices

### Security
- Always validate input parameters using `PARAM_*` constants
- Use capability checking before sensitive operations
- Escape output to prevent XSS attacks
- Follow Moodle security guidelines

### Performance
- Use database queries efficiently
- Cache data when appropriate
- Optimize template complexity
- Minimize external dependencies

### Maintainability
- Keep functions small and focused
- Use descriptive variable and function names
- Comment complex logic thoroughly
- Follow consistent code formatting

### Accessibility
- Use semantic HTML elements
- Include proper ARIA labels
- Ensure keyboard navigation works
- Test with screen readers

## Contribution Guidelines

1. **Code Style**: Follow Moodle coding standards
2. **Testing**: Test thoroughly before submitting
3. **Documentation**: Update documentation for new features
4. **Language**: Provide translations when possible
5. **Backward Compatibility**: Maintain compatibility when possible

## Version Control

### Branch Strategy
- `main`: Stable production code
- `develop`: Development branch
- `feature/*`: Feature development branches
- `hotfix/*`: Critical bug fixes

### Commit Messages
```
feat: Add new service tab for equipment requests
fix: Resolve permission check issue in navigation
docs: Update API documentation
style: Format code according to standards
``` 

## Recent Progress

### September 3, 2025
- ✅ **Bug #2 RESOLVED**: Fixed requestparticipant dropdown data loading issue
- ✅ AJAX endpoints now working correctly with absolute URLs
- ✅ Employee and lecturer data loading successfully
- ✅ Conditional field behavior fully functional
- ✅ All debugging code removed for production use
- ✅ **Bug #3 RESOLVED**: Fully modernized residencebooking tab
- ✅ Added comprehensive error handling, security improvements, and automatic service number population
- ✅ Integrated with existing residencebooking plugin JavaScript for autocomplete functionality
- ✅ Used `$PAGE->requires->js_call_amd()` for proper AMD module loading

## Current Development Status

**Bug Fixes**: 3 of 3 resolved (100%) ✅  
**Tab Modernization**: 3 of 6 completed (50%)  
**Template Internationalization**: 1 of 6 completed (17%)

### Next Development Target
- **Financial Services Tab**: Modernization and security improvements
- **Room Registration Tab**: Modernization and security improvements
- **Remaining Templates**: Continue internationalization progress 