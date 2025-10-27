# API Documentation

This document describes the web services and AJAX endpoints available in the Residence Booking plugin.

## Overview

The Residence Booking plugin provides both internal AJAX endpoints and web service functions for integration with external systems and enhanced user experience.

## AJAX Endpoints

### Guest Search Autocomplete

**Endpoint**: `/local/residencebooking/ajax/guest_search.php`

**Purpose**: Provides real-time guest name autocomplete functionality for the booking form (data sourced from Oracle via `oracle_manager`).

#### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `term` | string | Yes | Search term for guest names |
| `limit` | int | No | Maximum results to return (default: 10) |

#### Request Example

```javascript
// Using jQuery AJAX
$.get('/local/residencebooking/ajax/guest_search.php', {
    term: 'john',
    limit: 5
}, function(data) {
    // Handle response
});

// Using Moodle's AJAX module
require(['core/ajax'], function(Ajax) {
    Ajax.call([{
        methodname: 'local_residencebooking_guest_search',
        args: {term: 'john', limit: 5}
    }])[0].done(function(response) {
        // Handle response
    });
});
```

#### Response Format

```json
{
    "results": [
        {
            "id": "سارة خالد سليمان العتيبي",
            "text": "PF002 - سارة خالد سليمان العتيبي",
            "pf_number": "PF002"
        },
        {
            "id": "أحمد محمد علي",
            "text": "PF003 - أحمد محمد علي",
            "pf_number": "PF003"
        }
    ]
}
```

#### Error Response

```json
{
    "success": false,
    "error": "Invalid search query",
    "code": "INVALID_QUERY"
}
```

### PF Number Lookup

**Endpoint**: `/local/residencebooking/ajax/get_pf_number.php`

**Purpose**: Retrieves PF number for a selected guest using intelligent name processing and database search.

#### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `guest_name` | string | Yes | Full name or partial name of the guest |

#### Request Example

```javascript
$.get('/local/residencebooking/ajax/get_pf_number.php', {
    guest_name: 'سارة خالد سليمان العتيبي'
}, function(response) {
    if (response.success) {
        console.log('PF Number:', response.pf_number);
    }
});
```

#### Response Format

**Success Response:**
```json
{
    "success": true,
    "pf_number": "PF002"
}
```

**Error Response:**
```json
{
    "success": false,
    "error": "PF number not found for this guest"
}
```

#### Features

- **Direct PF Extraction**: Extracts PF numbers directly from guest names containing "PF" patterns
- **Smart Name Processing**: Uses first name extraction for database searches
- **Multiple Matching Strategies**: Exact, partial, and reverse matching
- **Fallback Mechanisms**: Graceful handling when direct extraction fails

#### Security Considerations

- Requires valid Moodle session
- Checks `local/residencebooking:submitrequest` capability
- Sanitizes all input parameters
- Returns limited information to prevent data leakage

## Web Services

### Current Implementation

The plugin currently has minimal web service implementation in `/db/services.php`. Below is the planned API expansion.

### Planned Web Service Functions

#### `local_residencebooking_submit_request`

**Purpose**: Submit a new accommodation booking request.

**Parameters**:
```php
[
    'guest_name' => [
        'type' => PARAM_TEXT,
        'description' => 'Name of the guest'
    ],
    'service_number' => [
        'type' => PARAM_ALPHANUMEXT,
        'description' => 'Service/ID number'
    ],
    'residence_type' => [
        'type' => PARAM_INT,
        'description' => 'ID of accommodation type'
    ],
    'start_date' => [
        'type' => PARAM_INT,
        'description' => 'Start date (Unix timestamp)'
    ],
    'end_date' => [
        'type' => PARAM_INT,
        'description' => 'End date (Unix timestamp)'
    ],
    'purpose' => [
        'type' => PARAM_INT,
        'description' => 'ID of booking purpose'
    ],
    'notes' => [
        'type' => PARAM_TEXT,
        'description' => 'Additional notes'
    ],
    'courseid' => [
        'type' => PARAM_INT,
        'description' => 'Course ID for context'
    ]
]
```

**Returns**:
```php
[
    'success' => PARAM_BOOL,
    'request_id' => PARAM_INT,
    'message' => PARAM_TEXT,
    'status_id' => PARAM_INT
]
```

#### `local_residencebooking_get_requests`

**Purpose**: Retrieve booking requests with filtering options.

**Parameters**:
```php
[
    'filters' => [
        'type' => PARAM_RAW,
        'description' => 'JSON encoded filter criteria'
    ],
    'page' => [
        'type' => PARAM_INT,
        'description' => 'Page number for pagination'
    ],
    'limit' => [
        'type' => PARAM_INT,
        'description' => 'Number of records per page'
    ]
]
```

**Returns**:
```php
[
    'requests' => [
        'type' => external_multiple_structure,
        'description' => 'List of booking requests'
    ],
    'total' => PARAM_INT,
    'page' => PARAM_INT,
    'has_more' => PARAM_BOOL
]
```

#### `local_residencebooking_update_status`

**Purpose**: Update the status of a booking request.

**Parameters**:
```php
[
    'request_id' => [
        'type' => PARAM_INT,
        'description' => 'ID of the booking request'
    ],
    'action' => [
        'type' => PARAM_ALPHA,
        'description' => 'Action: approve, reject, or delete'
    ],
    'note' => [
        'type' => PARAM_TEXT,
        'description' => 'Approval/rejection note'
    ]
]
```

**Rejection Behavior**:
- **Leader 1 Rejection**: Goes directly to Rejected (21) status
- **Leader 2, 3, Boss Rejections**: Return to Leader 1 Review (16) for re-evaluation
- **Capability Required**: Each level requires its respective workflow step capability

**Returns**:
```php
[
    'success' => PARAM_BOOL,
    'new_status_id' => PARAM_INT,
    'message' => PARAM_TEXT
]
```

#### `local_residencebooking_get_types`

**Purpose**: Get available accommodation types.

**Returns**:
```php
[
    'types' => [
        'type' => external_multiple_structure,
        'description' => 'Available accommodation types'
    ]
]
```

#### `local_residencebooking_get_purposes`

**Purpose**: Get available booking purposes.

**Returns**:
```php
[
    'purposes' => [
        'type' => external_multiple_structure,
        'description' => 'Available booking purposes'
    ]
]
```

## Data Structures

### Request Object

```php
[
    'id' => PARAM_INT,
    'guest_name' => PARAM_TEXT,
    'service_number' => PARAM_ALPHANUMEXT,
    'residence_type' => PARAM_INT,
    'residence_type_name' => PARAM_TEXT,
    'start_date' => PARAM_INT,
    'end_date' => PARAM_INT,
    'purpose' => PARAM_INT,
    'purpose_name' => PARAM_TEXT,
    'notes' => PARAM_TEXT,
    'status_id' => PARAM_INT,
    'status_name' => PARAM_TEXT,
    'userid' => PARAM_INT,
    'username' => PARAM_TEXT,
    'courseid' => PARAM_INT,
    'course_name' => PARAM_TEXT,
    'created_time' => PARAM_INT,
    'modified_time' => PARAM_INT,
    'approval_note' => PARAM_TEXT,
    'rejection_note' => PARAM_TEXT
]
```

### Type Object

```php
[
    'id' => PARAM_INT,
    'type_name_en' => PARAM_TEXT,
    'type_name_ar' => PARAM_TEXT,
    'type_name' => PARAM_TEXT, // Current language
    'deleted' => PARAM_BOOL
]
```

### Purpose Object

```php
[
    'id' => PARAM_INT,
    'purpose_name_en' => PARAM_TEXT,
    'purpose_name_ar' => PARAM_TEXT,
    'purpose_name' => PARAM_TEXT, // Current language
    'description_en' => PARAM_TEXT,
    'description_ar' => PARAM_TEXT,
    'description' => PARAM_TEXT, // Current language
    'deleted' => PARAM_BOOL
]
```

## Authentication

### Web Service Authentication

Web services require valid Moodle authentication:

```php
// Token-based authentication
$token = 'your_webservice_token';
$request_url = $CFG->wwwroot . '/webservice/rest/server.php';

$params = [
    'wstoken' => $token,
    'wsfunction' => 'local_residencebooking_submit_request',
    'moodlewsrestformat' => 'json',
    // ... other parameters
];
```

### Session-based Authentication

For AJAX calls from within Moodle:

```javascript
// Automatically uses current session
require(['core/ajax'], function(Ajax) {
    Ajax.call([{
        methodname: 'local_residencebooking_get_requests',
        args: {filters: {}, page: 1, limit: 20}
    }]);
});
```

## Capabilities and Permissions

### Required Capabilities by Function

| Function | Required Capability |
|----------|-------------------|
| `submit_request` | `local/residencebooking:submitrequest` |
| `get_requests` | `local/residencebooking:viewbookings` |
| `update_status` | `local/residencebooking:manage` OR workflow capabilities |
| `get_types` | `local/residencebooking:viewbookings` |
| `get_purposes` | `local/residencebooking:viewbookings` |

### Context Requirements

- Most functions require system context
- Some administrative functions may require site administration context
- Request submission may be limited to course context in future versions

## Error Handling

### Standard Error Codes

| Code | Description |
|------|-------------|
| `INVALID_PARAMETER` | Required parameter missing or invalid |
| `NO_PERMISSION` | User lacks required capability |
| `INVALID_REQUEST` | Request ID does not exist or user cannot access |
| `WORKFLOW_ERROR` | Invalid workflow transition |
| `DATABASE_ERROR` | Database operation failed |
| `VALIDATION_ERROR` | Data validation failed |

### Error Response Format

```json
{
    "exception": "moodle_exception",
    "errorcode": "NO_PERMISSION",
    "message": "You do not have permission to perform this action",
    "debuginfo": "Additional debug information (if debug enabled)"
}
```

## Rate Limiting

### Current Implementation
- No rate limiting currently implemented
- AJAX requests limited by session timeouts
- Web service calls limited by Moodle's built-in restrictions

### Recommended Implementation
```php
// In web service functions
$rate_limiter = new \local_residencebooking\rate_limiter();
if (!$rate_limiter->check_rate_limit($USER->id, 'submit_request', 10, 300)) {
    throw new moodle_exception('rate_limit_exceeded', 'local_residencebooking');
}
```

## Usage Examples

### JavaScript Integration

```javascript
// Guest autocomplete setup with PF number population
require(['local_residencebooking/guest_autocomplete'], function(GuestAutocomplete) {
    GuestAutocomplete.initAutocomplete('#id_guest_name', '#id_service_number');
});

// Submit request via web service
require(['core/ajax'], function(Ajax) {
    Ajax.call([{
        methodname: 'local_residencebooking_submit_request',
        args: {
            guest_name: 'John Smith',
            service_number: 'SVC001',
            residence_type: 1,
            start_date: 1640995200, // Unix timestamp
            end_date: 1641081600,
            purpose: 2,
            notes: 'Business trip accommodation',
            courseid: 1
        }
    }])[0].done(function(response) {
        if (response.success) {
            alert('Request submitted successfully! ID: ' + response.request_id);
        }
    }).fail(function(error) {
        alert('Error: ' + error.message);
    });
});
```

### PHP Integration

```php
// External API usage
$webservice = new \local_residencebooking\external\submit_request();
$result = $webservice->execute(
    'John Smith',
    'SVC001', 
    1, // residence_type
    time() + 86400, // start_date
    time() + 172800, // end_date
    2, // purpose
    'Test booking',
    1 // courseid
);
```

### Mobile App Integration

```javascript
// React Native / Cordova example
const submitBooking = async (bookingData) => {
    const response = await fetch(moodleUrl + '/webservice/rest/server.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            wstoken: userToken,
            wsfunction: 'local_residencebooking_submit_request',
            moodlewsrestformat: 'json',
            ...bookingData
        })
    });
    
    return await response.json();
};
```

## Future API Enhancements

### Planned Features
- **Bulk operations**: Submit/update multiple requests
- **File attachments**: Support for document uploads
- **Notification subscriptions**: Real-time status updates
- **Reporting endpoints**: Analytics and statistics
- **Calendar integration**: iCal export functionality
- **Mobile push notifications**: Status change alerts

### GraphQL Consideration
Future versions may implement GraphQL endpoint for more flexible data querying:

```graphql
query GetBookingRequests($filters: BookingFilters, $page: Int) {
  bookingRequests(filters: $filters, page: $page) {
    id
    guestName
    serviceNumber
    status {
      id
      name
    }
    accommodationType {
      id
      name
    }
  }
}
``` 