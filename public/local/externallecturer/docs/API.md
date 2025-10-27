# API Documentation

This document describes the API endpoints, functions, and data structures used in the External Lecturer Management plugin version 1.5.

## AJAX Endpoints

All AJAX endpoints are located in the `/actions/` directory and return JSON responses. All endpoints require the `local/externallecturer:manage` capability.

### Add Lecturer
**Endpoint**: `/local/externallecturer/actions/addlecturer.php`
**Method**: POST
**Content-Type**: application/x-www-form-urlencoded or application/json

#### Request Parameters
```php
- name (PARAM_TEXT, required): Lecturer's full name
- age (PARAM_INT, required): Lecturer's age (2-digit number)
- specialization (PARAM_TEXT, required): Area of expertise (2-100 characters, letters only)
- organization (PARAM_TEXT, required): Associated organization (2-100 characters)
- degree (PARAM_TEXT, required): Academic qualification (2-100 characters, letters only)
- passport (PARAM_TEXT, required): Passport number
- civil_number (PARAM_TEXT, optional): Civil identification number
- lecturer_type (PARAM_TEXT, optional): Type of lecturer ("external_visitor" or "resident", defaults to "external_visitor")
  - "external_visitor": Visiting professionals from other institutions, created via form_externallecturer.mustache with name-based Oracle autocomplete
  - "resident": Permanent local teaching staff, created via form_residentlecturer.mustache with civil number-based Oracle search
- nationality (PARAM_TEXT, optional): Lecturer nationality
```

#### Response Format
```json
// Success Response
{
    "status": "success",
    "message": "New lecturer added successfully",
    "id": 123
}

// Error Response
{
    "status": "error",
    "message": "Failed to add lecturer",
    "error": "Detailed error description"
}
```

#### Example Usage
```javascript
fetch('/local/externallecturer/actions/addlecturer.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
    },
    body: new URLSearchParams({
        name: 'Dr. Ahmed Al-Mansouri',
        age: '35',
        specialization: 'Computer Science',
        organization: 'University of Technology',
        degree: 'PhD',
        passport: 'A12345678',
        civil_number: '1234567890'
    })
})
.then(response => response.json())
.then(data => {
    if (data.status === 'success') {
        console.log('Lecturer added:', data.id);
    } else {
        console.error('Error:', data.message);
    }
});
```

### Edit Lecturer
**Endpoint**: `/local/externallecturer/actions/editlecturer.php`
**Method**: POST

#### Request Parameters
```php
- id (PARAM_INT, required): Lecturer ID to edit
- name (PARAM_TEXT, required): Updated lecturer name
- age (PARAM_INT, required): Updated age
- specialization (PARAM_TEXT, required): Updated specialization
- organization (PARAM_TEXT, required): Updated organization
- degree (PARAM_TEXT, required): Updated degree
- passport (PARAM_TEXT, required): Updated passport number
- civil_number (PARAM_TEXT, optional): Updated civil number
- lecturer_type (PARAM_TEXT, optional): Updated lecturer type ("external_visitor" or "resident")
  - "external_visitor": Visiting professionals from other institutions
  - "resident": Permanent local teaching staff
- nationality (PARAM_TEXT, optional): Updated nationality
```

#### Response Format
```json
// Success
{
    "status": "success",
    "message": "Lecturer data updated successfully"
}

// Error
{
    "status": "error",
    "message": "Failed to update lecturer data"
}
```

### Delete Lecturer
**Endpoint**: `/local/externallecturer/actions/deletelecturer.php`
**Method**: POST

#### Request Parameters
```php
- id (PARAM_INT, required): Lecturer ID to delete
```

#### Behavior
- Deletes lecturer record from `externallecturer` table
- Cascades to remove related course enrollments
- Returns success/error status

#### Response Format
```json
// Success
{
    "status": "success",
    "message": "Lecturer deleted successfully"
}

// Error
{
    "status": "error",
    "message": "Failed to delete lecturer"
}
```

<!-- Course enrollment functionality has been removed from this plugin -->

## Database Schema

### Table: `externallecturer`

```sql
CREATE TABLE mdl_externallecturer (
    id BIGINT(10) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    age INT(3) NOT NULL,
    specialization VARCHAR(255) NOT NULL,
    organization VARCHAR(255) NOT NULL,
    degree VARCHAR(255) NOT NULL,
    passport VARCHAR(20) NOT NULL,
    civil_number VARCHAR(20) NULL,
    lecturer_type VARCHAR(20) NOT NULL DEFAULT 'external_visitor',
    nationality VARCHAR(100) NULL,
    courses_count INT(10) NOT NULL DEFAULT 0,
    timecreated INT(10) NOT NULL DEFAULT 0,
    timemodified INT(10) NOT NULL DEFAULT 0,
    created_by INT(10) NOT NULL DEFAULT 0,
    modified_by INT(10) DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Field Descriptions
- `id`: Primary key, auto-increment
- `name`: Lecturer's full name (255 characters max)
- `age`: Lecturer's age (3-digit integer)
- `specialization`: Area of expertise (255 characters max)
- `organization`: Associated organization (255 characters max)
- `degree`: Academic qualification (255 characters max)
- `passport`: Passport number (20 characters max)
- `civil_number`: Civil identification number (20 characters max, optional)
- `lecturer_type`: Type of lecturer - "external_visitor" (default) for visiting professionals or "resident" for permanent local staff
- `nationality`: Lecturer nationality (100 characters max, optional)
- `courses_count`: Number of courses lecturer is enrolled in (auto-updated)
- `timecreated`: Unix timestamp when record was created (auto-populated)
- `timemodified`: Unix timestamp when record was last modified (auto-updated)
- `created_by`: User ID who created this record (auto-populated, displayed as username in interface)
- `modified_by`: User ID who last modified the record (auto-updated)

<!-- The externallecturer_courses table has been removed from this plugin -->

## Data Validation Rules

### Lecturer Data Validation
- **Name**: Required, max 255 characters
- **Age**: Required, 2-digit number (1-99)
- **Specialization**: Required, 2-100 characters, letters only
- **Organization**: Required, 2-100 characters, letters and numbers allowed
- **Degree**: Required, 2-100 characters, letters only
- **Passport**: Required, 20 characters max
- **Civil Number**: Optional, 20 characters max
- **Audit Fields**: Automatically populated by system (created_by, created_datetime, modified_datetime)

<!-- Course enrollment validation has been removed from this plugin -->

## Error Handling

### Common Error Codes
- `400`: Bad Request - Invalid parameters
- `403`: Forbidden - Insufficient permissions
- `404`: Not Found - Lecturer or course not found
- `409`: Conflict - Duplicate enrollment
- `500`: Internal Server Error - Database or system error

### Error Response Format
```json
{
    "status": "error",
    "message": "Human-readable error message",
    "error": "Technical error details (if available)",
    "code": 400
}
```

## Security Considerations

### Input Validation
- All inputs are validated using Moodle's PARAM_* functions
- SQL injection prevention through prepared statements
- XSS protection through proper output escaping

### Permission Checks
- All endpoints require `local/externallecturer:manage` capability
- System context permission validation
- User authentication required

### Data Sanitization
- HTML entities encoding for output
- Parameter type checking
- Length validation for all string inputs

## Performance Considerations

### Database Optimization
- Foreign key constraints for data integrity
- Indexes on frequently queried fields
- Cascade deletes for maintaining consistency

### Caching
- Moodle's built-in caching system
- Session-based data caching
- Query result caching where appropriate

## Lecturer Type Workflows

### External Visitor Lecturer Creation
**Form**: `form_externallecturer.mustache`
**Process**:
1. User enters lecturer name in autocomplete field
2. Oracle database search provides matching results based on name
3. User selects from autocomplete suggestions
4. Form auto-populates passport and other available data (readonly)
5. User completes remaining fields (age, specialization, etc.)
6. Form submits with `lecturer_type = "external_visitor"`

### Resident Lecturer Creation
**Form**: `form_residentlecturer.mustache`
**Process**:
1. User enters civil number in search field
2. Oracle DUNIA database search retrieves person data
3. Form auto-populates name and nationality (readonly)
4. User manually enters professional information (age, specialization, etc.)
5. Form submits with `lecturer_type = "resident"`

## Integration Examples

### JavaScript Integration
```javascript
// Example: Add lecturer with error handling
async function addLecturer(lecturerData) {
    try {
        const response = await fetch('/local/externallecturer/actions/addlecturer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(lecturerData)
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showSuccessMessage(result.message);
            refreshLecturerList();
        } else {
            showErrorMessage(result.message);
        }
    } catch (error) {
        console.error('Network error:', error);
        showErrorMessage('Network error occurred');
    }
}
```

### PHP Integration
```php
// Example: Get lecturer data
function getLecturerData($lecturerid) {
    global $DB;
    
    $lecturer = $DB->get_record('externallecturer', ['id' => $lecturerid]);
    
    if (!$lecturer) {
        throw new Exception('Lecturer not found');
    }
    
    return $lecturer;
}

// Example: Get lecturer enrollments
function getLecturerEnrollments($lecturerid) {
    global $DB;
    
    $sql = "SELECT ec.*, c.fullname as course_name 
            FROM {externallecturer_courses} ec
            JOIN {course} c ON ec.courseid = c.id
            WHERE ec.lecturerid = :lecturerid";
    
    return $DB->get_records_sql($sql, ['lecturerid' => $lecturerid]);
}
```

## Language Support

### String Retrieval
```php
// Get language string
$message = get_string('lectureraddedsuccess', 'local_externallecturer');

// Get string with parameters
$message = get_string('lectureraddedwithname', 'local_externallecturer', $lecturer->name);
```

### JavaScript Language Support
```javascript
// Access language strings in JavaScript
const strings = M.str.local_externallecturer;
console.log(strings.lectureraddedsuccess);
```

## Testing

### Unit Testing
- Test all validation rules
- Test error conditions
- Test permission checks
- Test database operations

### Integration Testing
- Test AJAX endpoints
- Test UI interactions
- Test multilingual support
- Test performance with large datasets

## Troubleshooting

### Common Issues
1. **Permission Denied**: Check user capabilities
2. **Database Errors**: Verify table structure
3. **AJAX Failures**: Check network connectivity
4. **Validation Errors**: Review input format requirements

### Debug Information
- Enable Moodle debugging for detailed error messages
- Check browser console for JavaScript errors
- Review server error logs for PHP errors
- Monitor database query logs for performance issues 