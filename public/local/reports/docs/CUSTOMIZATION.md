# Customization Guide

This document explains how to customize and extend the Student Reports plugin to meet specific organizational needs.

## Overview

The Student Reports plugin is designed to be flexible and extensible. This guide covers various customization options, from simple configuration changes to advanced modifications.

## Customization Levels

### Level 1: Configuration Changes
- No code modification required
- Changes through Moodle admin interface
- Role and capability adjustments
- Language customizations

### Level 2: Template Modifications
- Modify appearance and layout
- Change form fields and displays
- Customize PDF output
- Minimal PHP knowledge required

### Level 3: Functionality Extensions
- Add new report fields
- Modify workflow logic
- Create new endpoints
- Requires PHP and Moodle development knowledge

### Level 4: Core Modifications
- Change database schema
- Implement new workflow systems
- Integration with external systems
- Requires advanced Moodle development skills

## Configuration Customizations

### Capability Customization

#### Creating Custom Capabilities
Add new capabilities to `db/access.php`:
```php
$capabilities = [
    // Existing capabilities...
    
    'local/reports:department_review' => [
        'riskbitmask' => RISK_SPAM,
        'captype'     => 'write',
        'contextlevel'=> CONTEXT_COURSE,
        'archetypes'  => [
            'editingteacher' => CAP_ALLOW,
        ],
    ],
    
    'local/reports:final_approval' => [
        'riskbitmask' => RISK_SPAM,
        'captype'     => 'write', 
        'contextlevel'=> CONTEXT_SYSTEM,
        'archetypes'  => [
            'manager' => CAP_ALLOW,
        ],
    ],
];
```

#### Role-Based Workflow
Customize workflow based on organizational structure:
```php
// In index.php or allreports.php
$custom_workflow = [
    30 => ['next' => 31, 'capability' => 'local/reports:department_review'],
    31 => ['next' => 32, 'capability' => 'local/reports:academic_review'], 
    32 => ['next' => 33, 'capability' => 'local/reports:dean_approval'],
    33 => ['next' => 50, 'capability' => 'local/reports:final_approval'],
];
```

### Language Customization

#### Custom Language Strings
Modify existing strings or add new ones:

1. **Site-wide Customization**:
   - Go to **Site Administration > Language > Language customization**
   - Select language and component (`local_reports`)
   - Modify strings as needed

2. **Plugin-level Customization**:
   ```php
   // In lang/en/local_reports.php
   $string['pluginname'] = 'Academic Progress Reports';
   $string['custom_field'] = 'Custom Field Label';
   $string['department_specific'] = 'Department-Specific Information';
   ```

3. **Context-Specific Strings**:
   ```php
   // Add conditional language strings
   $string['report_type_undergraduate'] = 'Undergraduate Report';
   $string['report_type_graduate'] = 'Graduate Report';
   $string['report_type_research'] = 'Research Report';
   ```

## Template Customizations

### Modifying Report Display

#### Customizing Report List Template
Edit `templates/reports_list.mustache`:
```mustache
{{!-- Add custom columns --}}
<th>{{#str}}custom_field, local_reports{{/str}}</th>

{{!-- In the data rows --}}
<td>{{custom_field_value}}</td>

{{!-- Add conditional display --}}
{{#show_department_info}}
<div class="department-specific">
    {{department_info}}
</div>
{{/show_department_info}}
```

#### Customizing PDF Templates
Modify PDF templates for different layouts:

1. **Custom Vertical PDF** (`templates/custom_pdf_vertical.mustache`):
   ```mustache
   <!DOCTYPE html>
   <html>
   <head>
       <style>
           .custom-header { 
               background-color: #organization-color; 
               color: white;
           }
           .department-section {
               border: 1px solid #ddd;
               margin: 10px 0;
               padding: 10px;
           }
       </style>
   </head>
   <body>
       <div class="custom-header">
           <h1>{{organization_name}} - Student Report</h1>
       </div>
       
       <div class="student-info">
           <h2>Student Information</h2>
           <p>Name: {{student_name}}</p>
           <p>ID: {{student_id}}</p>
           <p>Department: {{department_name}}</p>
       </div>
       
       {{!-- Custom sections --}}
       <div class="department-section">
           <h3>Department-Specific Information</h3>
           {{department_specific_content}}
       </div>
   </body>
   </html>
   ```

### Form Customization

#### Adding New Form Fields
Modify `classes/form/report_form.php`:
```php
public function definition() {
    $mform = $this->_form;
    
    // Existing fields...
    
    // Add custom fields
    $mform->addElement('textarea', 'academic_goals', 
        get_string('academic_goals', 'local_reports'), 
        ['rows' => 4, 'cols' => 60]);
    
    $mform->addElement('select', 'semester_performance', 
        get_string('semester_performance', 'local_reports'),
        [
            'excellent' => get_string('excellent', 'local_reports'),
            'good' => get_string('good', 'local_reports'),
            'satisfactory' => get_string('satisfactory', 'local_reports'),
            'needs_improvement' => get_string('needs_improvement', 'local_reports')
        ]);
    
    // Add department-specific fields based on course category
    $category_id = $this->get_course_category();
    if ($category_id == ENGINEERING_CATEGORY) {
        $this->add_engineering_fields();
    } elseif ($category_id == BUSINESS_CATEGORY) {
        $this->add_business_fields();
    }
}

private function add_engineering_fields() {
    $mform = $this->_form;
    
    $mform->addElement('textarea', 'technical_skills', 
        get_string('technical_skills', 'local_reports'));
    
    $mform->addElement('textarea', 'project_experience', 
        get_string('project_experience', 'local_reports'));
}
```

#### Dynamic Form Behavior
```php
// Add JavaScript for dynamic form behavior
public function definition() {
    // ... existing code ...
    
    // Add JavaScript for dynamic fields
    global $PAGE;
    $PAGE->requires->js_init_code("
        document.addEventListener('DOMContentLoaded', function() {
            var reportType = document.getElementById('id_type_id');
            var customSection = document.getElementById('custom_fields_section');
            
            reportType.addEventListener('change', function() {
                if (this.value == '1') {
                    customSection.style.display = 'block';
                } else {
                    customSection.style.display = 'none';
                }
            });
        });
    ");
}
```

## Functionality Extensions

### Adding New Report Fields

#### Database Schema Changes
1. **Update Database Schema** (`db/install.xml`):
   ```xml
   <FIELD NAME="academic_goals" TYPE="text" NOTNULL="false"/>
   <FIELD NAME="semester_performance" TYPE="char" LENGTH="50" NOTNULL="false"/>
   <FIELD NAME="technical_skills" TYPE="text" NOTNULL="false"/>
   <FIELD NAME="department_id" TYPE="int" LENGTH="10" NOTNULL="false"/>
   ```

2. **Create Upgrade Script** (`db/upgrade.php`):
   ```php
   <?php
   function xmldb_local_reports_upgrade($oldversion) {
       global $DB;
       $dbman = $DB->get_manager();
       
       if ($oldversion < 2025041703) {
           $table = new xmldb_table('local_reports');
           
           // Add new fields
           $field = new xmldb_field('academic_goals', XMLDB_TYPE_TEXT);
           if (!$dbman->field_exists($table, $field)) {
               $dbman->add_field($table, $field);
           }
           
           upgrade_plugin_savepoint(true, 2025041703, 'local', 'reports');
       }
       
       return true;
   }
   ```

3. **Update Version** (`version.php`):
   ```php
   $plugin->version = 2025041703; // Increment version
   ```

#### Form Integration
Update form to handle new fields:
```php
// In classes/form/report_form.php
public function get_data() {
    $data = parent::get_data();
    if ($data) {
        // Process custom fields
        $data->academic_goals = clean_text($data->academic_goals);
        $data->semester_performance = clean_param($data->semester_performance, PARAM_ALPHA);
    }
    return $data;
}
```

### Custom Workflow Implementation

#### Advanced Workflow System
Create a custom workflow engine:
```php
<?php
// classes/workflow/custom_workflow.php
namespace local_reports\workflow;

class custom_workflow {
    private $workflow_config;
    
    public function __construct() {
        $this->workflow_config = $this->load_workflow_config();
    }
    
    public function can_advance_status($current_status, $user_id, $context) {
        $next_status = $this->get_next_status($current_status);
        if (!$next_status) return false;
        
        $required_capability = $this->workflow_config[$current_status]['capability'];
        return has_capability($required_capability, $context, $user_id);
    }
    
    public function advance_status($report_id, $user_id, $comments = '') {
        // Custom logic for status advancement
        $report = $this->get_report($report_id);
        $new_status = $this->get_next_status($report->status_id);
        
        // Update database
        $this->update_report_status($report_id, $new_status, $user_id);
        
        // Log action
        $this->log_workflow_action($report_id, $report->status_id, $new_status, $user_id, $comments);
        
        // Send notifications
        $this->send_notifications($report_id, $new_status);
        
        return $new_status;
    }
    
    private function load_workflow_config() {
        // Load from database, config file, or define programmatically
        return [
            0  => ['next' => 30, 'capability' => 'local/reports:submit'],
            30 => ['next' => 31, 'capability' => 'local/reports:department_review'],
            31 => ['next' => 32, 'capability' => 'local/reports:academic_review'],
            32 => ['next' => 50, 'capability' => 'local/reports:final_approval'],
        ];
    }
}
```

### Integration with External Systems

#### Student Information System Integration
```php
<?php
// classes/external/sis_integration.php
namespace local_reports\external;

class sis_integration {
    private $api_endpoint;
    private $api_key;
    
    public function __construct() {
        $this->api_endpoint = get_config('local_reports', 'sis_api_endpoint');
        $this->api_key = get_config('local_reports', 'sis_api_key');
    }
    
    public function get_student_data($student_id) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->api_endpoint . "/students/" . $student_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->api_key,
                "Content-Type: application/json"
            ]
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        return json_decode($response, true);
    }
    
    public function sync_report_data($report_id) {
        // Sync report data with external system
        $report = $this->get_report($report_id);
        $external_data = $this->format_for_external_system($report);
        
        return $this->send_to_external_system($external_data);
    }
}
```

#### Email Notification System
```php
<?php
// classes/notifications/email_notifier.php
namespace local_reports\notifications;

class email_notifier {
    public function send_approval_notification($report_id, $approver_id) {
        global $DB, $CFG;
        
        $report = $DB->get_record('local_reports', ['id' => $report_id]);
        $student = $DB->get_record('user', ['id' => $report->userid]);
        $approver = $DB->get_record('user', ['id' => $approver_id]);
        
        $subject = get_string('report_approved_subject', 'local_reports');
        $message = get_string('report_approved_message', 'local_reports', [
            'student_name' => fullname($student),
            'approver_name' => fullname($approver),
            'course_name' => $this->get_course_name($report->courseid)
        ]);
        
        // Send to student
        email_to_user($student, $approver, $subject, $message);
        
        // Send to relevant staff
        $this->notify_relevant_staff($report, $subject, $message);
    }
}
```

## Advanced Customizations

### Custom Dashboard Integration

#### Add to Moodle Dashboard
```php
<?php
// In lib.php
function local_reports_extend_navigation_user_settings($navigation, $user, $usercontext, $course, $coursecontext) {
    if (has_capability('local/reports:viewall', $coursecontext)) {
        $url = new moodle_url('/local/reports/dashboard.php');
        $navigation->add(
            get_string('reports_dashboard', 'local_reports'), 
            $url, 
            navigation_node::TYPE_CUSTOM
        );
    }
}
```

#### Custom Dashboard Page
```php
<?php
// dashboard.php
require_once(__DIR__ . '/../../config.php');

$context = context_system::instance();
require_login();
require_capability('local/reports:viewall', $context);

$PAGE->set_url(new moodle_url('/local/reports/dashboard.php'));
$PAGE->set_context($context);
$PAGE->set_title('Reports Dashboard');

// Get dashboard data
$total_reports = $DB->count_records('local_reports');
$pending_reports = $DB->count_records('local_reports', ['status_id' => 30]);
$approved_reports = $DB->count_records('local_reports', ['status_id' => 50]);

// Render dashboard
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_reports/dashboard', [
    'total_reports' => $total_reports,
    'pending_reports' => $pending_reports,
    'approved_reports' => $approved_reports,
    'charts_data' => $this->get_charts_data()
]);
echo $OUTPUT->footer();
```

### Performance Optimizations

#### Caching Implementation
```php
<?php
// classes/cache/report_cache.php
namespace local_reports\cache;

class report_cache {
    private $cache;
    
    public function __construct() {
        $this->cache = \cache::make('local_reports', 'reportdata');
    }
    
    public function get_course_reports($courseid, $status = null) {
        $cache_key = "course_{$courseid}_status_{$status}";
        $cached_data = $this->cache->get($cache_key);
        
        if ($cached_data === false) {
            $cached_data = $this->fetch_course_reports($courseid, $status);
            $this->cache->set($cache_key, $cached_data, 300); // 5 minute cache
        }
        
        return $cached_data;
    }
    
    public function invalidate_course_cache($courseid) {
        // Invalidate all cache entries for a course
        $this->cache->delete("course_{$courseid}_status_30");
        $this->cache->delete("course_{$courseid}_status_50");
    }
}
```

#### Database Query Optimization
```php
<?php
// Optimized queries for large datasets
function get_reports_with_pagination($courseid, $page = 0, $perpage = 20) {
    global $DB;
    
    $sql = "SELECT r.*, u.firstname, u.lastname, u.email
            FROM {local_reports} r
            JOIN {user} u ON r.userid = u.id
            WHERE r.courseid = :courseid
            ORDER BY r.timemodified DESC";
    
    return $DB->get_records_sql($sql, 
        ['courseid' => $courseid], 
        $page * $perpage, 
        $perpage
    );
}
```

## Testing Customizations

### Unit Testing Custom Features
```php
<?php
// tests/custom_workflow_test.php
class custom_workflow_test extends advanced_testcase {
    
    public function test_custom_approval_workflow() {
        $this->resetAfterTest();
        
        // Create test data
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $teacher = $this->getDataGenerator()->create_user();
        
        // Test custom workflow
        $workflow = new \local_reports\workflow\custom_workflow();
        $this->assertTrue($workflow->can_advance_status(30, $teacher->id, $context));
    }
}
```

### Integration Testing
```php
<?php
// tests/integration_test.php
class integration_test extends advanced_testcase {
    
    public function test_external_system_integration() {
        // Test external API integration
        $sis = new \local_reports\external\sis_integration();
        $student_data = $sis->get_student_data(123);
        $this->assertArrayHasKey('student_id', $student_data);
    }
}
```

## Deployment Considerations

### Version Control
- Keep customizations in separate branches
- Document all changes thoroughly
- Test in staging environment before production
- Plan for plugin updates and compatibility

### Configuration Management
- Use Moodle's config storage for settings
- Implement admin settings page for customizations
- Document configuration requirements
- Provide migration scripts for major changes

### Backup Strategy
- Backup original plugin files before customization
- Include custom database fields in backup procedures
- Test restore procedures with customizations
- Document rollback procedures

This customization guide provides a foundation for adapting the Student Reports plugin to meet specific organizational requirements. Always test thoroughly and follow Moodle development best practices when implementing customizations. 