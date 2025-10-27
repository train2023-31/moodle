# Development Guide

This guide covers everything you need to know to develop and contribute to the Room Booking plugin.

## Development Environment Setup

### Prerequisites

1. **Moodle 4.1+** - The plugin requires Moodle 4.1 minimum
2. **PHP 7.4+** - Follow Moodle's PHP requirements
3. **Database** - MySQL, PostgreSQL, or MariaDB
4. **Web Server** - Apache or Nginx
5. **Git** - For version control

### Local Development Setup

1. **Clone the Moodle codebase**:
   ```bash
   git clone https://github.com/moodle/moodle.git
   cd moodle
   git checkout MOODLE_41_STABLE  # or latest stable
   ```

2. **Install the Room Booking plugin**:
   ```bash
   cd local/
   git clone [plugin-repository] roombooking
   ```

3. **Configure Moodle**:
   - Set up your `config.php`
   - Enable debugging: `$CFG->debug = E_ALL; $CFG->debugdisplay = 1;`
   - Run the Moodle installation/upgrade process

## Code Standards and Guidelines

### PHP Coding Standards

Follow Moodle's coding standards:

1. **PSR-4 Autoloading**: Classes in `classes/` directory
2. **Naming Conventions**:
   - Classes: `PascalCase`
   - Methods/Functions: `snake_case`
   - Variables: `snake_case`
   - Constants: `UPPER_SNAKE_CASE`

3. **File Structure**:
   ```php
   <?php
   defined('MOODLE_INTERNAL') || die();

   /**
    * Class documentation
    */
   class example_class {
       // Class implementation
   }
   ```

### Database Standards

1. **Table Names**: Use `local_roombooking_` prefix
2. **Field Names**: Use descriptive names with underscores
3. **XMLDB**: All schema changes must be in `db/install.xml`
4. **Upgrades**: Version-specific changes in `db/upgrade.php`

### Template Standards

1. **Mustache Templates**: Use semantic HTML
2. **CSS Classes**: Follow Moodle's Bootstrap classes
3. **Accessibility**: Ensure WCAG compliance
4. **Responsive Design**: Mobile-first approach

## Development Workflow

### 1. Repository Pattern Implementation

The plugin uses the Repository pattern for data access:

```php
// Example: booking_repository.php
class booking_repository {
    public function get_booking($id) {
        global $DB;
        return $DB->get_record('local_roombooking_bookings', ['id' => $id]);
    }
    
    public function save_booking($booking) {
        global $DB;
        if (isset($booking->id)) {
            return $DB->update_record('local_roombooking_bookings', $booking);
        } else {
            return $DB->insert_record('local_roombooking_bookings', $booking);
        }
    }
}
```

### 2. Service Layer Development

Business logic belongs in service classes:

```php
// Example: booking_service.php
class booking_service {
    private $booking_repository;
    
    public function __construct($booking_repository) {
        $this->booking_repository = $booking_repository;
    }
    
    public function create_booking($data) {
        // Validate data
        // Check conflicts
        // Apply business rules
        // Save via repository
    }
}
```

### 3. Form Development

Use Moodle's form API:

```php
// Example: booking_form.php
class booking_form extends moodleform {
    protected function definition() {
        $mform = $this->_form;
        
        $mform->addElement('text', 'title', get_string('title', 'local_roombooking'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');
        
        $this->add_action_buttons();
    }
}
```

### 4. Page Development

Follow Moodle's page structure:

```php
// Example page structure
require_once('../../config.php');
require_login();

$context = context_system::instance();
require_capability('local/roombooking:manage', $context);

$PAGE->set_context($context);
$PAGE->set_url('/local/roombooking/pages/example.php');
$PAGE->set_title(get_string('pagetitle', 'local_roombooking'));
$PAGE->set_heading(get_string('pageheading', 'local_roombooking'));

echo $OUTPUT->header();
// Page content
echo $OUTPUT->footer();
```

## Plugin Architecture

### Layer Structure

```
┌─────────────────────────────────────────┐
│               Pages Layer               │
│    (User Interface & Controllers)       │
├─────────────────────────────────────────┤
│               Service Layer             │
│        (Business Logic)                 │
├─────────────────────────────────────────┤
│             Repository Layer            │
│         (Data Access)                   │
├─────────────────────────────────────────┤
│             Database Layer              │
│    (Moodle Database Abstraction)       │
└─────────────────────────────────────────┘
```

### Component Interaction

1. **Pages** handle HTTP requests and coordinate responses
2. **Forms** process user input and validation
3. **Services** implement business logic and rules
4. **Repositories** provide data access abstraction
5. **Templates** render HTML output using Mustache

## Testing Guidelines

### Unit Testing

Create PHPUnit tests in `tests/` directory:

```php
// Example: tests/booking_service_test.php
class booking_service_test extends advanced_testcase {
    
    public function setUp(): void {
        $this->resetAfterTest(true);
    }
    
    public function test_create_booking() {
        // Test implementation
    }
}
```

### Manual Testing

1. **Functionality Testing**: Test all CRUD operations
2. **Permission Testing**: Verify capability checks
3. **UI Testing**: Test responsive design and accessibility
4. **Browser Testing**: Test across different browsers

## Database Development

### Schema Changes

1. **Modify `db/install.xml`** for new installations
2. **Add upgrade steps** in `db/upgrade.php`
3. **Update version** in `version.php`

Example upgrade step:
```php
function xmldb_local_roombooking_upgrade($oldversion) {
    global $DB;
    
    if ($oldversion < 2025061601) {
        // Add new field
        $table = new xmldb_table('local_roombooking_bookings');
        $field = new xmldb_field('new_field', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_plugin_savepoint(true, 2025061601, 'local', 'roombooking');
    }
    
    return true;
}
```

## Workflow Integration

### Understanding the Workflow System

The plugin integrates with a generic workflow engine:

1. **Workflow Type ID**: `8` (configured in the workflow system)
2. **States**: pending, approved, rejected
3. **Transitions**: Managed by `simple_workflow_manager.php`
4. **Rejection Behavior**: Leader 1 rejections go to final rejection, while higher-level rejections return to Leader 1 Review for re-evaluation

### Adding Workflow to New Features

```php
// Example: Integrating workflow with a new entity
$workflow_manager = new simple_workflow_manager();
$workflow_manager->submit_for_approval($entity_id, $entity_type);
```

## Internationalization (i18n)

### Adding Language Strings

1. **Add to English**: `lang/en/local_roombooking.php`
   ```php
   $string['newstring'] = 'English text';
   ```

2. **Add to Arabic**: `lang/ar/local_roombooking.php` (if applicable)
   ```php
   $string['newstring'] = 'النص العربي';
   ```

3. **Use in code**:
   ```php
   echo get_string('newstring', 'local_roombooking');
   ```

## Debugging and Troubleshooting

### Enable Debugging

In `config.php`:
```php
$CFG->debug = E_ALL;
$CFG->debugdisplay = 1;
$CFG->debugpageinfo = 1;
```

### Common Issues

1. **Capability Errors**: Check `db/access.php` and user roles
2. **Database Errors**: Verify table structure and field types
3. **Template Errors**: Check Mustache syntax and data structure
4. **Autoloading Issues**: Verify class namespaces and file locations

### Logging

Use Moodle's debugging functions:
```php
debugging('Debug message', DEBUG_DEVELOPER);
error_log('Error message');
```

## Performance Considerations

### Database Optimization

1. **Use appropriate indexes** in `db/install.xml`
2. **Limit query results** with pagination
3. **Use caching** for expensive operations
4. **Avoid N+1 queries** by using joins

### Caching

Implement caching for expensive operations:
```php
$cache = cache::make('local_roombooking', 'bookings');
$data = $cache->get('key');
if ($data === false) {
    $data = expensive_operation();
    $cache->set('key', $data);
}
```

## Security Guidelines

### Input Validation

1. **Always validate input**: Use `PARAM_*` constants
2. **Escape output**: Use `s()`, `format_text()`, etc.
3. **Check capabilities**: Use `require_capability()`
4. **Verify ownership**: Check user permissions for data access

### SQL Injection Prevention

Use Moodle's database API:
```php
// Good
$DB->get_record('table', ['field' => $value]);

// Bad - Never do this
$DB->get_record_sql("SELECT * FROM table WHERE field = '$value'");
```

## Contribution Guidelines

### Before Contributing

1. **Check existing issues** and discussions
2. **Create an issue** for new features or bugs
3. **Follow coding standards**
4. **Write tests** for new functionality

### Pull Request Process

1. **Create feature branch**: `git checkout -b feature/description`
2. **Make changes** following guidelines
3. **Test thoroughly**
4. **Submit pull request** with clear description
5. **Respond to code review** feedback

### Documentation

Update relevant documentation:
- Code comments for complex logic
- Update this development guide for architectural changes
- Update API reference for new public methods
- Update user guide for new features

## Release Process

### Version Management

1. **Update version** in `version.php`
2. **Document changes** in appropriate documentation
3. **Test upgrade process** from previous versions
4. **Verify backward compatibility**

### Release Checklist

- [ ] All tests pass
- [ ] Documentation updated
- [ ] Version number incremented
- [ ] Upgrade script tested
- [ ] Language strings complete
- [ ] Performance tested
- [ ] Security reviewed

## Getting Help

### Resources

1. **Moodle Developer Documentation**: https://docs.moodle.org/dev/
2. **Moodle Forums**: https://moodle.org/mod/forum/
3. **Plugin Documentation**: This docs folder
4. **Code Comments**: Inline documentation in source files

### Common Development Tasks

1. **Adding a new capability**: Modify `db/access.php`
2. **Creating a new page**: Add to `pages/` directory
3. **Adding a form field**: Modify appropriate form class
4. **Changing database schema**: Update `db/install.xml` and `db/upgrade.php`
5. **Adding language strings**: Update language files
6. **Creating new template**: Add `.mustache` file to `templates/`

This development guide should be your primary reference for contributing to the Room Booking plugin. Always refer to Moodle's official documentation for platform-specific guidelines. 