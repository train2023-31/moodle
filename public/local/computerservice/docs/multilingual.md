# Multilingual Support Documentation

This document explains how the Computer Service plugin implements bilingual support for Arabic and English languages.

## 🌐 Overview

The Computer Service plugin provides comprehensive bilingual support, allowing the interface and data to be displayed in both Arabic and English. This feature is essential for educational institutions serving diverse linguistic communities.

### Supported Languages
- **English (en)**: Left-to-right (LTR) text direction
- **Arabic (ar)**: Right-to-left (RTL) text direction

### Key Features
- **Dynamic language switching**: Changes language based on user preference
- **Bidirectional device names**: Separate storage for English and Arabic device names
- **RTL interface support**: Proper Arabic text rendering
- **Fallback mechanism**: Defaults to English if Arabic translation missing
- **Contextual language resolution**: Uses current user's language setting

## 🏗️ Architecture

### Language Resolution Strategy

#### Current Language Detection
```php
// Get current user's language
$current_lang = current_language();

// Determine device name based on language
$device_name = ($current_lang === 'ar') ? $device->devicename_ar : $device->devicename_en;
```

#### Language-Specific Data Retrieval
```php
// Dynamic field selection
$langfield = (current_language() === 'ar') ? 'devicename_ar' : 'devicename_en';

$sql = "SELECT id, {$langfield} as device_name FROM {local_computerservice_devices} WHERE status = 'active'";
$devices = $DB->get_records_sql($sql);
```

### Database Schema for Multilingual Data

#### Device Names Storage
```sql
-- Device table with separate language columns
CREATE TABLE mdl_local_computerservice_devices (
    id             BIGINT(10) NOT NULL AUTO_INCREMENT,
    devicename_en  VARCHAR(100) NOT NULL,  -- English name
    devicename_ar  VARCHAR(100) NOT NULL,  -- Arabic name  
    status         VARCHAR(10) NOT NULL DEFAULT 'active',
    PRIMARY KEY (id)
);
```

#### Data Example
| ID | devicename_en | devicename_ar | status |
|----|---------------|---------------|---------|
| 1  | Projector     | جهاز عرض      | active  |
| 2  | Laptop        | حاسوب محمول   | active  |
| 3  | Microphone    | ميكروفون      | active  |

## 📁 Language File Structure

### Directory Organization
```
/lang/
├── en/                                 # English language pack
│   └── local_computerservice.php      # English strings
└── ar/                                 # Arabic language pack
    └── local_computerservice.php      # Arabic strings
```

### Language String Format

#### English Language File (`/lang/en/local_computerservice.php`)
```php
<?php
defined('MOODLE_INTERNAL') || die();

$string['computerservice'] = 'Computer Service';
$string['requestdevices'] = 'Request Devices';
$string['managerequests'] = 'Manage Requests';
$string['managedevices'] = 'Manage Devices';
$string['devicetype'] = 'Device Type';
$string['numdevices'] = 'Number of Devices';
$string['requestneededby'] = 'Required Date';
$string['comments'] = 'Comments';
$string['submit'] = 'Submit Request';

// Status strings
$string['status_initial'] = 'Initial';
$string['status_leader1_review'] = 'Leader 1 Review';
$string['status_leader2_review'] = 'Leader 2 Review';
$string['status_leader3_review'] = 'Leader 3 Review';
$string['status_boss_review'] = 'Boss Review';
$string['status_approved'] = 'Approved';
$string['status_rejected'] = 'Rejected';

// Action strings
$string['approve'] = 'Approve';
$string['reject'] = 'Reject';
$string['approvalNote'] = 'Approval Note';
$string['rejectionNote'] = 'Rejection Note';

// Error messages
$string['invalidrequest'] = 'Invalid request';
$string['insufficientpermissions'] = 'Insufficient permissions';
$string['rejectionnoterequired'] = 'Rejection note is required';
```

#### Arabic Language File (`/lang/ar/local_computerservice.php`)
```php
<?php
defined('MOODLE_INTERNAL') || die();

$string['computerservice'] = 'خدمة الحاسوب';
$string['requestdevices'] = 'طلب الأجهزة';
$string['managerequests'] = 'إدارة الطلبات';
$string['managedevices'] = 'إدارة الأجهزة';
$string['devicetype'] = 'نوع الجهاز';
$string['numdevices'] = 'عدد الأجهزة';
$string['requestneededby'] = 'التاريخ المطلوب';
$string['comments'] = 'تعليقات';
$string['submit'] = 'إرسال الطلب';

// Status strings
$string['status_initial'] = 'أولي';
$string['status_leader1_review'] = 'مراجعة القائد الأول';
$string['status_leader2_review'] = 'مراجعة القائد الثاني';
$string['status_leader3_review'] = 'مراجعة القائد الثالث';
$string['status_boss_review'] = 'مراجعة المدير';
$string['status_approved'] = 'موافق عليه';
$string['status_rejected'] = 'مرفوض';

// Action strings
$string['approve'] = 'موافقة';
$string['reject'] = 'رفض';
$string['approvalNote'] = 'ملاحظة الموافقة';
$string['rejectionNote'] = 'ملاحظة الرفض';

// Error messages
$string['invalidrequest'] = 'طلب غير صحيح';
$string['insufficientpermissions'] = 'أذونات غير كافية';
$string['rejectionnoterequired'] = 'ملاحظة الرفض مطلوبة';
```

## 🎨 User Interface Adaptation

### RTL (Right-to-Left) Support

#### CSS Considerations
```css
/* Arabic RTL styling */
[dir="rtl"] .computerservice-form {
    text-align: right;
}

[dir="rtl"] .computerservice-table {
    direction: rtl;
}

[dir="rtl"] .status-badge {
    margin-left: 0;
    margin-right: 5px;
}
```

#### Automatic Direction Detection
```php
// In output renderers
$direction = (current_language() === 'ar') ? 'rtl' : 'ltr';
$html .= '<div dir="' . $direction . '" class="computerservice-content">';
```

### Form Adaptation

#### Dynamic Label Generation
```php
// In form classes
public function definition() {
    $mform = $this->_form;
    
    // Language-aware labels
    $mform->addElement('select', 'deviceid', 
        get_string('devicetype', 'local_computerservice'), 
        $this->get_device_options()
    );
}

private function get_device_options() {
    global $DB;
    
    $lang = current_language();
    $name_field = ($lang === 'ar') ? 'devicename_ar' : 'devicename_en';
    
    $devices = $DB->get_records('local_computerservice_devices', 
        ['status' => 'active'], 
        $name_field, 
        "id, {$name_field} as name"
    );
    
    $options = [];
    foreach ($devices as $device) {
        $options[$device->id] = $device->name;
    }
    
    return $options;
}
```

### Template Language Support

#### Mustache Template with Language Data
```mustache
{{! manage_requests.mustache }}
<div class="computerservice-container" dir="{{text_direction}}">
    <h2>{{page_title}}</h2>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>{{#str}}username, local_computerservice{{/str}}</th>
                <th>{{#str}}coursename, local_computerservice{{/str}}</th>
                <th>{{#str}}devicetype, local_computerservice{{/str}}</th>
                <th>{{#str}}status, local_computerservice{{/str}}</th>
                <th>{{#str}}actions, local_computerservice{{/str}}</th>
            </tr>
        </thead>
        <tbody>
            {{#requests}}
            <tr>
                <td>{{username}}</td>
                <td>{{coursename}}</td>
                <td>{{device_name}}</td>
                <td><span class="badge badge-{{status_class}}">{{status_name}}</span></td>
                <td>
                    {{#can_approve}}
                    <button class="btn btn-success" onclick="approveRequest({{id}})">
                        {{#str}}approve, local_computerservice{{/str}}
                    </button>
                    {{/can_approve}}
                    {{#can_reject}}
                    <button class="btn btn-danger" onclick="rejectRequest({{id}})">
                        {{#str}}reject, local_computerservice{{/str}}
                    </button>
                    {{/can_reject}}
                </td>
            </tr>
            {{/requests}}
        </tbody>
    </table>
</div>
```

#### Template Data Preparation
```php
// In output/manage_requests.php
public function export_for_template(renderer_base $output) {
    $data = new stdClass();
    
    // Set text direction for RTL support
    $data->text_direction = (current_language() === 'ar') ? 'rtl' : 'ltr';
    
    // Process requests with language-specific device names
    $data->requests = [];
    foreach ($this->requests as $request) {
        $request_data = $this->format_request($request);
        $request_data->device_name = $this->get_device_name($request, current_language());
        $data->requests[] = $request_data;
    }
    
    return $data;
}

private function get_device_name($request, $lang) {
    return ($lang === 'ar') ? $request->devicename_ar : $request->devicename_en;
}
```

## 🔧 Implementation Details

### Language String Usage

#### Basic String Retrieval
```php
// Simple string
$title = get_string('computerservice', 'local_computerservice');

// String with parameters
$message = get_string('requestsubmitted', 'local_computerservice', $device_name);

// Conditional string based on language
if (current_language() === 'ar') {
    $welcome = get_string('welcome_arabic', 'local_computerservice');
} else {
    $welcome = get_string('welcome_english', 'local_computerservice');
}
```

#### Advanced String Handling
```php
// Check if string exists before using
if (get_string_manager()->string_exists('custom_string', 'local_computerservice')) {
    $text = get_string('custom_string', 'local_computerservice');
} else {
    $text = get_string('default_string', 'local_computerservice');
}

// Format string with multiple parameters
$params = [
    'username' => $user->firstname . ' ' . $user->lastname,
    'devicename' => $device_name,
    'date' => userdate(time())
];
$message = get_string('request_notification', 'local_computerservice', $params);
```

### Device Name Management

#### Adding Bilingual Devices
```php
// In device form processing
if ($data = $mform->get_data()) {
    $record = (object)[
        'devicename_en' => clean_param($data->devicename_en, PARAM_TEXT),
        'devicename_ar' => clean_param($data->devicename_ar, PARAM_TEXT),
        'status' => $data->status,
    ];
    
    $DB->insert_record('local_computerservice_devices', $record);
}
```

#### Retrieving Language-Specific Device Names
```php
// Dynamic device name retrieval
function get_localized_device_name($device_id) {
    global $DB;
    
    $device = $DB->get_record('local_computerservice_devices', ['id' => $device_id]);
    
    if (!$device) {
        return '';
    }
    
    $lang = current_language();
    return ($lang === 'ar') ? $device->devicename_ar : $device->devicename_en;
}
```

### AJAX Language Support

#### Language-Aware AJAX Responses
```php
// In actions/update_request_status.php
$response = [
    'success' => true,
    'message' => get_string('request_approved', 'local_computerservice'),
    'new_status' => $new_status,
    'status_text' => get_string('status_' . $status_name, 'local_computerservice')
];

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
```

#### JavaScript Language Support
```javascript
// Include language strings in JavaScript
var LANG = {
    approve: "{{#str}}approve, local_computerservice{{/str}}",
    reject: "{{#str}}reject, local_computerservice{{/str}}",
    confirm: "{{#str}}confirmaction, local_computerservice{{/str}}",
    success: "{{#str}}actionsuccess, local_computerservice{{/str}}",
    error: "{{#str}}actionerror, local_computerservice{{/str}}"
};

function showMessage(key, isSuccess) {
    var message = LANG[key] || 'Unknown message';
    var className = isSuccess ? 'alert-success' : 'alert-danger';
    
    $('.message-container').html(
        '<div class="alert ' + className + '">' + message + '</div>'
    );
}
```

## 🌍 Localization Best Practices

### String Naming Conventions

#### Consistent Naming
```php
// Good: Descriptive and hierarchical
$string['form_devicetype'] = 'Device Type';
$string['form_numdevices'] = 'Number of Devices';
$string['status_approved'] = 'Approved';
$string['status_rejected'] = 'Rejected';
$string['error_invalidrequest'] = 'Invalid request';
$string['action_approve'] = 'Approve';

// Avoid: Generic or ambiguous names
$string['field1'] = 'Field 1';
$string['text'] = 'Text';
$string['button'] = 'Button';
```

#### Parameter Handling
```php
// String with placeholder
$string['request_notification'] = 'Request for {$a->devicename} by {$a->username} on {$a->date}';

// Usage with parameters
$params = (object)[
    'devicename' => $device_name,
    'username' => $user_name,
    'date' => $formatted_date
];
$message = get_string('request_notification', 'local_computerservice', $params);
```

### Translation Guidelines

#### Arabic Translation Best Practices
1. **Cultural Adaptation**: Use terms familiar to Arabic speakers
2. **Gender Considerations**: Arabic has gender-specific forms
3. **Formal Language**: Use formal Arabic for official communications
4. **Technical Terms**: Balance between Arabic terms and accepted English technical terms

#### Example Translations
| English | Arabic | Notes |
|---------|--------|-------|
| Computer Service | خدمة الحاسوب | Direct translation |
| Request | طلب | Formal request |
| Approve | موافقة | Official approval |
| Reject | رفض | Formal rejection |
| Device | جهاز | Generic device |
| Projector | جهاز عرض | Projection device |
| Laptop | حاسوب محمول | Portable computer |

### Language Testing

#### Testing Checklist
- [ ] All interface elements display in correct language
- [ ] Device names show appropriate language variant
- [ ] Text direction is correct (LTR for English, RTL for Arabic)
- [ ] No untranslated strings appear
- [ ] Special characters display correctly
- [ ] Form validation messages are translated
- [ ] Date and time formats are appropriate
- [ ] Export functionality works with both languages

#### Common Issues and Solutions

**Mixed Language Display**
```php
// Problem: Mixing English and Arabic
$device_name = $device->devicename_en; // Always English

// Solution: Dynamic language resolution
$lang = current_language();
$device_name = ($lang === 'ar') ? $device->devicename_ar : $device->devicename_en;
```

**Missing Translations**
```php
// Problem: String not found
$string = get_string('missing_string', 'local_computerservice'); // Error

// Solution: Fallback mechanism
if (get_string_manager()->string_exists('custom_string', 'local_computerservice')) {
    $string = get_string('custom_string', 'local_computerservice');
} else {
    $string = get_string('default_string', 'local_computerservice');
}
```

**RTL Layout Issues**
```css
/* Problem: Fixed left margins */
.element {
    margin-left: 10px; /* Only works for LTR */
}

/* Solution: Direction-aware margins */
[dir="ltr"] .element {
    margin-left: 10px;
}

[dir="rtl"] .element {
    margin-right: 10px;
}
```

## 🔄 Language Administration

### Adding New Languages

#### Steps to Add a New Language
1. **Create language directory**: `/lang/[lang_code]/`
2. **Copy English file**: Use as template
3. **Translate all strings**: Maintain same keys
4. **Test thoroughly**: Verify all functionality
5. **Update device names**: Add new language column if needed

#### Language Code Standards
- Use ISO 639-1 language codes
- Examples: `en` (English), `ar` (Arabic), `fr` (French), `es` (Spanish)

### Maintenance

#### Regular Tasks
- **String Reviews**: Quarterly review of translations
- **New String Addition**: Add translations when adding features
- **User Feedback**: Collect feedback on translation quality
- **Consistency Checks**: Ensure consistent terminology usage

#### Version Control
- Track language file changes
- Document translation updates
- Maintain translation history
- Coordinate with translators

---

This multilingual documentation provides comprehensive guidance for understanding and extending the bilingual features of the Computer Service plugin, ensuring effective support for diverse linguistic communities. 