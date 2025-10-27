# API Documentation

This document describes the AJAX endpoints and API interfaces provided by the Student Reports plugin.

## Overview

The plugin provides several AJAX endpoints for dynamic interaction without page reloads. All endpoints follow Moodle's security and parameter validation standards.

## Common Request/Response Patterns

### Request Headers
All AJAX requests should include:
```
Content-Type: application/x-www-form-urlencoded
X-Requested-With: XMLHttpRequest
```

### Response Format
All endpoints return JSON responses with consistent structure:
```json
{
    "success": true|false,
    "message": "Human-readable message",
    "data": { /* Response-specific data */ },
    "errors": [ /* Array of error messages */ ]
}
```

### Authentication
- All endpoints require user authentication
- Session-based authentication (Moodle standard)
- CSRF protection via Moodle's sesskey mechanism

## AJAX Endpoints

### 1. Form Handler - `form_ajax.php`

**Purpose**: Handles report form submissions (create/update)

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | int | No | Report ID (for updates, 0 for new) |
| `courseid` | int | Yes | Course ID where report belongs |
| `userid` | int | Yes | Student user ID |
| `futureexpec` | text | No | Future expectations content |
| `dep_op` | text | No | Department opinion content |
| `seg_path` | text | No | Suggested path content |
| `researchtitle` | text | No | Research title content |
| `type_id` | int | No | Report type identifier |
| `sesskey` | string | Yes | Moodle session key for CSRF protection |

#### Response Data
```json
{
    "success": true,
    "message": "Report saved successfully.",
    "data": {
        "reportid": 123,
        "status_id": 0,
        "timemodified": 1640995200
    }
}
```

#### Error Responses
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": [
        "User ID is required",
        "Course ID is invalid"
    ]
}
```

#### Usage Example
```javascript
fetch('/local/reports/form_ajax.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
        'courseid': 123,
        'userid': 456,
        'futureexpec': 'Student expectations...',
        'sesskey': M.cfg.sesskey
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Report saved:', data.data.reportid);
    } else {
        console.error('Errors:', data.errors);
    }
});
```

### 2. Approval Handler - `approve_ajax.php`

**Purpose**: Handles report approval workflow actions

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `reportid` | int | Yes | Report ID to approve |
| `action` | string | Yes | Action type ('approve' or 'approve_all') |
| `courseid` | int | No | Course ID (for bulk operations) |
| `tab` | string | No | Current tab context |
| `sesskey` | string | Yes | Moodle session key |

#### Single Report Approval
```javascript
// Approve single report
{
    "reportid": 123,
    "action": "approve",
    "sesskey": "abc123"
}
```

#### Bulk Approval
```javascript
// Approve all eligible reports
{
    "action": "approve_all",
    "courseid": 456,
    "tab": "pending",
    "sesskey": "abc123"
}
```

#### Response Data
```json
{
    "success": true,
    "message": "Report approved successfully",
    "data": {
        "reportid": 123,
        "old_status": 30,
        "new_status": 31,
        "approved_count": 1
    }
}
```

#### Capability Requirements
- User must have appropriate workflow capability for the report's current status
- For bulk operations, user must have capability for all affected reports

### 3. Disapproval Handler - `disapprove_ajax.php`

**Purpose**: Handles report disapproval and rejection

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `reportid` | int | Yes* | Report ID to disapprove |
| `action` | string | Yes | Action type ('disapprove' or 'disapprove_all') |
| `reason` | text | Yes | Reason for disapproval |
| `courseid` | int | No | Course ID (for bulk operations) |
| `tab` | string | No | Current tab context |
| `sesskey` | string | Yes | Moodle session key |

*Required for single report disapproval

#### Single Report Disapproval
```javascript
{
    "reportid": 123,
    "action": "disapprove",
    "reason": "Incomplete information provided",
    "sesskey": "abc123"
}
```

#### Bulk Disapproval
```javascript
{
    "action": "disapprove_all",
    "courseid": 456,
    "reason": "Department review required",
    "tab": "pending",
    "sesskey": "abc123"
}
```

#### Response Data
```json
{
    "success": true,
    "message": "Report disapproved successfully",
    "data": {
        "reportid": 123,
        "old_status": 31,
        "new_status": 30,
        "reason": "Incomplete information provided",
        "disapproved_count": 1
    }
}
```

## JavaScript Integration

### Global Variables
The plugin sets up global JavaScript variables:
```javascript
window.LOCALREPORTS = {
    courseid: 123,           // Current course ID
    isAllReports: false      // True when on allreports.php page
};
```

### Required JavaScript Files
The following JavaScript files must be loaded for full functionality:
- `/theme/stream/js/modal.js` - Modal dialog handling
- `/theme/stream/js/openreportform.js` - Form modal management
- `/theme/stream/js/approve.js` - Approval workflow handling
- `/theme/stream/js/previewreport.js` - PDF preview functionality

### Event Handling Pattern
```javascript
// Example approval handling
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('approve-report')) {
        const reportId = e.target.dataset.reportid;
        
        fetch('/local/reports/approve_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'reportid': reportId,
                'action': 'approve',
                'sesskey': M.cfg.sesskey
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                location.reload(); // Or dynamic update
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
});
```

## Error Handling

### Common Error Codes
| Error Type | HTTP Status | Description |
|------------|-------------|-------------|
| Authentication | 403 | User not logged in |
| Authorization | 403 | Insufficient capabilities |
| Validation | 400 | Invalid parameters |
| Not Found | 404 | Report/Course not found |
| Server Error | 500 | Database or system error |

### Error Response Format
```json
{
    "success": false,
    "message": "Primary error message",
    "errors": [
        "Detailed error 1",
        "Detailed error 2"
    ],
    "debug": "Additional debug info (if debug mode enabled)"
}
```

### Client-Side Error Handling
```javascript
fetch(endpoint, options)
.then(response => {
    if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    return response.json();
})
.then(data => {
    if (!data.success) {
        // Handle application-level errors
        console.error('API Error:', data.message);
        if (data.errors && data.errors.length > 0) {
            data.errors.forEach(error => console.error('  -', error));
        }
        return;
    }
    // Handle success
    console.log('Success:', data.data);
})
.catch(error => {
    // Handle network or parsing errors
    console.error('Request failed:', error.message);
});
```

## Security Considerations

### Input Validation
All endpoints implement:
- Parameter type validation using Moodle's `required_param()` and `optional_param()`
- Capability checks before any database operations
- CSRF protection via sesskey validation
- SQL injection prevention through Moodle DML

### Capability Verification
Each endpoint checks appropriate capabilities:
```php
// Example capability check pattern
require_capability('local/reports:manage', $context);

// Workflow-specific checks
if ($action === 'approve') {
    $required_capability = $workflow[$current_status]['capability'];
    require_capability($required_capability, $context);
}
```

### Data Sanitization
- All text inputs are cleaned for HTML/script content
- User IDs validated against existing users
- Course IDs validated against user enrollment/access

## Rate Limiting

### Current Implementation
- No explicit rate limiting implemented
- Moodle's session management provides basic protection
- Consider implementing for high-traffic installations

### Recommended Limits
For production environments, consider:
- Maximum 60 requests per minute per user
- Maximum 5 bulk operations per minute
- Exponential backoff for repeated failures

## Testing

### Unit Testing
Test each endpoint with:
- Valid parameters
- Invalid parameters
- Missing required parameters
- Capability edge cases
- Concurrent access scenarios

### Integration Testing
- Test workflow transitions end-to-end
- Verify UI updates after AJAX calls
- Test error handling and user feedback
- Verify audit trail creation

### Load Testing
- Test bulk operations with large datasets
- Verify performance under concurrent usage
- Monitor database query performance
- Test timeout and error recovery

## Future Enhancements

### Potential API Improvements
1. **RESTful Endpoints**: Convert to proper REST API
2. **Batch Operations**: More efficient bulk processing
3. **Webhooks**: External system integration
4. **Rate Limiting**: Built-in protection
5. **API Versioning**: Support for backwards compatibility
6. **Real-time Updates**: WebSocket integration for live updates

### Integration Possibilities
- External report generation systems
- Student information systems
- Notification services
- Analytics platforms
- Mobile applications 