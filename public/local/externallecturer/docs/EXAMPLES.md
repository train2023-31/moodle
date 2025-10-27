# Examples and Use Cases

This document provides practical examples and common use cases for the External Lecturer Management plugin.

## Basic Usage Examples

### Adding a New External Lecturer

**Scenario:** You need to add a new external lecturer to your system.

**Steps:**
1. Navigate to `/local/externallecturer/index.php`
2. Choose the appropriate lecturer type:
   - Click "Add External Lecturer" for visiting professionals from other institutions (external_visitor)
   - Click "Add Resident Lecturer" for permanent local teaching staff (resident)

#### For External Visitor Lecturers:
3. Use name-based autocomplete:
   - Start typing the lecturer's name
   - Select from Oracle database autocomplete suggestions
   - Passport and other data auto-populate (readonly)
4. Complete remaining fields manually:

```
Age: 45
Specialization: Computer Science
Organization: Cairo University
Degree: PhD in Computer Science
```

#### For Resident Lecturers:
3. Use civil number search:
   - Enter the civil identification number
   - Oracle DUNIA database automatically populates name and nationality (readonly)
4. Complete remaining professional information manually:

```
Civil Number: 12345678 (auto-searched)
Name: Dr. Fatima Al-Zahra (auto-populated)
Nationality: Omani (auto-populated)
Age: 38
Specialization: Information Technology
Organization: Local Technical Institute
Degree: PhD in Computer Engineering
Passport: B87654321
```

5. Click "Submit" to save the lecturer

**Result:** The lecturer will appear in the lecturers table with an initial course count of 0. The system will automatically track who created the record (Created By column) and when it was last modified (Last Modified column). The lecturer type field will be set to either "external_visitor" or "resident" based on which form was used. Audit fields (`timecreated`, `timemodified`, `created_by`, `modified_by`) are automatically populated.

### Viewing Audit Information

**Scenario:** You want to see who created a lecturer record and when it was last modified.

**Information Displayed:**
- **Created By**: Shows the full name of the user who created the lecturer record
- **Last Modified**: Shows the date and time when the record was last updated (format: YYYY-MM-DD HH:MM)

**Example Table Row:**
```
Name: Dr. Ahmed Hassan
Age: 45
Specialization: Computer Science
Organization: Cairo University
Degree: PhD
Passport: A12345678
Courses Count: 2
Created By: John Smith
Last Modified: 2025-08-04 14:30
Actions: [Add to Course] [Edit] [Delete]
```

### Enrolling a Lecturer in a Course

**Scenario:** Assign the newly added lecturer to teach a specific course.

**Steps:**
1. Switch to the "الدورات المسجلة" (Enrolled Courses) tab
2. Click "Enroll to Course" button
3. Select lecturer and course:

```
Lecturer: Dr. Ahmed Hassan
Course: Advanced Programming (CS101)
Cost: $2500
```

4. Submit the enrollment

**Result:** The lecturer's course count will increment, and the enrollment will appear in the courses table.

### Working with Different Lecturer Types

**Scenario:** Understanding the difference between external visitor and resident lecturers.

**External Visitor Lecturers (`lecturer_type = "external_visitor"`):**
- Visiting professionals from other institutions
- Form includes name-based autocomplete from Oracle database
- Passport and other data auto-populated and read-only during creation
- Created via "Add External Lecturer" button and `form_externallecturer.mustache`
- Typically short-term teaching assignments

**Resident Lecturers (`lecturer_type = "resident"`):**
- Permanent teaching staff hired locally
- Form uses civil number search with Oracle DUNIA integration
- Name and nationality auto-populated from Oracle, professional fields manual entry
- Created via "Add Resident Lecturer" button and `form_residentlecturer.mustache`
- Long-term or permanent teaching positions

**Example Usage:**
```
External Visitor:
- Dr. Sarah Wilson (MIT) - visiting for one semester
- Prof. Michael Chen (Stanford) - guest lecture series
- lecturer_type: "external_visitor"

Resident Lecturer:
- Dr. Ahmed Al-Rashid - hired locally as permanent faculty
- Prof. Fatima Al-Zahra - local expert joining full-time
- lecturer_type: "resident"
```

## Advanced Use Cases

### Managing Multiple Lecturers for a Department

**Scenario:** A university department needs to manage external lecturers for the computer science program.

**Implementation:**

1. **Bulk Lecturer Addition:**
   Add multiple lecturers with similar specializations:

```
Lecturer 1:
- Name: Dr. Sarah Wilson
- Specialization: Artificial Intelligence
- Organization: MIT
- Courses: AI Fundamentals, Machine Learning

Lecturer 2:
- Name: Prof. Michael Chen
- Specialization: Database Systems
- Organization: Stanford University
- Courses: Database Design, Advanced SQL

Lecturer 3:
- Name: Dr. Elena Rodriguez
- Specialization: Web Development
- Organization: Tech Corp
- Courses: Frontend Development, Backend Systems
```

2. **Cost Management:**
   Track different cost structures:
   - Senior lecturers: $3000 per course
   - Junior lecturers: $2000 per course
   - Specialized topics: $3500 per course

### Training Institute Management

**Scenario:** A training institute offers various certification courses with external expert instructors.

**Use Case Implementation:**

1. **Lecturer Categories:**
```
Industry Experts:
- Name: John Smith
- Organization: Google
- Specialization: Cloud Computing
- Rate: $4000 per course

Academic Researchers:
- Name: Dr. Lisa Park
- Organization: University Research Lab
- Specialization: Data Science
- Rate: $2800 per course

Consultants:
- Name: Robert Johnson
- Organization: Tech Consulting LLC
- Specialization: Project Management
- Rate: $3200 per course
```

2. **Course Assignment Strategy:**
- Match lecturer expertise with course requirements
- Consider cost constraints
- Track lecturer availability through course counts

### Corporate Training Program

**Scenario:** A corporation runs internal training programs with external specialists.

**Setup:**

1. **Lecturer Profiles:**
```
Leadership Training:
- Name: Mary Thompson
- Organization: Leadership Institute
- Focus: Executive Development
- Corporate Rate: $5000

Technical Training:
- Name: David Kim
- Organization: Tech Academy
- Focus: Software Engineering
- Corporate Rate: $3500

Compliance Training:
- Name: Jennifer Adams
- Organization: Legal Corp
- Focus: Regulatory Compliance
- Corporate Rate: $2800
```

2. **Budget Tracking:**
- Use cost field to track training expenses
- Generate reports for budget planning
- Monitor ROI on external training investments

## Workflow Examples

### Semester Planning Workflow

**Phase 1: Planning**
1. Identify courses requiring external expertise
2. Define budget allocations per course
3. Search for qualified external lecturers

**Phase 2: Lecturer Management**
1. Add new lecturers to the system:
```php
// Example data entry
Name: Dr. Ahmed Al-Rashid
Age: 52
Specialization: Cybersecurity
Organization: Security Institute
Degree: PhD in Information Security
Passport: B98765432
```

**Phase 3: Course Assignment**
1. Enroll lecturers in appropriate courses
2. Set cost parameters:
```
Course: Network Security (CS450)
Lecturer: Dr. Ahmed Al-Rashid
Cost: $3200
Duration: 12 weeks
```

**Phase 4: Monitoring**
1. Track lecturer workload through course counts
2. Monitor budget utilization
3. Export data for administrative reporting

### Quality Assurance Workflow

**Step 1: Lecturer Verification**
- Verify passport information
- Confirm degree credentials
- Validate organization affiliation

**Step 2: Performance Tracking**
```sql
-- Example query to track high-performing lecturers
SELECT l.name, l.specialization, COUNT(c.id) as course_count,
       AVG(CAST(c.cost AS DECIMAL)) as average_cost
FROM mdl_externallecturer l
JOIN mdl_externallecturer_courses c ON l.id = c.lecturerid
GROUP BY l.id
HAVING course_count > 3
ORDER BY average_cost DESC;
```

**Step 3: Continuous Improvement**
- Review lecturer utilization patterns
- Optimize cost-effectiveness
- Plan for future semester needs

## Data Export Examples

### Financial Reporting

**Generate Cost Summary:**
1. Switch to "Enrolled Courses" tab
2. Click "Export CSV" to download course enrollment data
3. Process in spreadsheet for financial analysis

**Sample CSV Output:**
```csv
Lecturer Name,Course Name,Cost,Specialization
Dr. Ahmed Hassan,Advanced Programming,$2500,Computer Science
Dr. Sarah Wilson,AI Fundamentals,$3000,Artificial Intelligence
Prof. Michael Chen,Database Design,$2800,Database Systems
```

### Administrative Reports

**Lecturer Utilization Report:**
1. Export lecturer data from main tab
2. Combine with course enrollment data
3. Generate comprehensive utilization analysis

**Sample Analysis:**
```
Lecturer Utilization Summary:
- Total External Lecturers: 15
- Average Courses per Lecturer: 2.3
- Most Utilized Specialization: Computer Science (8 lecturers)
- Highest Cost per Course: $4000 (Cloud Computing)
- Total Program Cost: $45,600
```

## Integration Examples

### With Moodle Calendar

**Scenario:** Integrate lecturer schedules with Moodle calendar events.

**Implementation Concept:**
```php
// Pseudo-code for calendar integration
foreach ($lecturer_courses as $enrollment) {
    $event = new calendar_event();
    $event->name = "Course: " . $enrollment->coursename;
    $event->description = "Lecturer: " . $enrollment->lecturername;
    $event->timestart = $course_start_time;
    $event->courseid = $enrollment->courseid;
    calendar_event::create($event);
}
```

### With Financial System

**Data Export for ERP Integration:**
```csv
Export Format for Financial System:
Department,Lecturer_ID,Lecturer_Name,Course_Code,Cost,Payment_Status
CS,001,Dr. Ahmed Hassan,CS101,$2500,Pending
CS,002,Dr. Sarah Wilson,CS201,$3000,Approved
IT,003,Prof. Michael Chen,IT301,$2800,Paid
```

### With HR Management

**Lecturer Database Export:**
```json
{
  "external_lecturers": [
    {
      "id": 1,
      "name": "Dr. Ahmed Hassan",
      "specialization": "Computer Science",
      "organization": "Cairo University",
      "qualification": "PhD",
      "active_courses": 2,
      "total_compensation": "$5000"
    }
  ]
}
```

## Custom Modifications Examples

### Adding Custom Fields

**Scenario:** Add email field to lecturer profile.

**Database Modification:**
```sql
ALTER TABLE mdl_externallecturer 
ADD COLUMN email VARCHAR(255) AFTER passport;
```

**Template Update:**
```mustache
<!-- Add to form_externallecturer.mustache -->
<div class="form-group">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" class="form-control" required>
</div>
```

**Action File Update:**
```php
// Add to addlecturer.php
$email = required_param('email', PARAM_EMAIL);
$newlecturer->email = $email;
```

### Custom Validation Rules

**Scenario:** Ensure passport numbers are unique.

**Implementation:**
```php
// In addlecturer.php
$passport = required_param('passport', PARAM_TEXT);

// Check for duplicate passport
$existing = $DB->get_record('externallecturer', ['passport' => $passport]);
if ($existing) {
    echo json_encode([
        'status' => 'fail', 
        'message' => 'Passport number already exists'
    ]);
    exit();
}
```

### Cost Calculation Logic

**Scenario:** Implement dynamic cost calculation based on lecturer experience.

**Implementation:**
```php
// Custom cost calculation
function calculate_lecturer_cost($lecturer_id, $course_id) {
    global $DB;
    
    $lecturer = $DB->get_record('externallecturer', ['id' => $lecturer_id]);
    $base_cost = 2000;
    
    // Experience bonus
    if ($lecturer->courses_count > 5) {
        $base_cost += 500;
    }
    
    // Specialization premium
    $premium_specializations = ['Artificial Intelligence', 'Cybersecurity'];
    if (in_array($lecturer->specialization, $premium_specializations)) {
        $base_cost += 800;
    }
    
    return $base_cost;
}
```

## Performance Optimization Examples

### Large Dataset Handling

**Scenario:** Managing 1000+ external lecturers efficiently.

**Database Optimization:**
```sql
-- Add indexes for better performance
CREATE INDEX idx_externallecturer_specialization 
ON mdl_externallecturer(specialization);

CREATE INDEX idx_externallecturer_organization 
ON mdl_externallecturer(organization);

CREATE INDEX idx_courses_cost 
ON mdl_externallecturer_courses(cost);
```

**Pagination Optimization:**
```php
// Implement efficient pagination
$total_count = $DB->count_records('externallecturer');
$page_size = 25; // Increased for better performance
$offset = $page * $page_size;

$lecturers = $DB->get_records('externallecturer', null, 'name ASC', 
                             'id, name, specialization, courses_count', 
                             $offset, $page_size);
```

### Caching Implementation

**Scenario:** Cache frequently accessed data.

**Implementation:**
```php
// Cache lecturer counts
$cache = cache::make('local_externallecturer', 'lecturer_stats');
$lecturer_count = $cache->get('total_lecturers');

if ($lecturer_count === false) {
    $lecturer_count = $DB->count_records('externallecturer');
    $cache->set('total_lecturers', $lecturer_count);
}
```

## Error Handling Examples

### Graceful Error Management

**AJAX Error Handling:**
```javascript
// In main.js
function submitLecturerForm() {
    fetch('/local/externallecturer/actions/addlecturer.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showSuccessMessage(data.message);
            refreshLecturerTable();
        } else {
            showErrorMessage(data.message);
        }
    })
    .catch(error => {
        showErrorMessage('Network error: Please try again later.');
        console.error('Error:', error);
    });
}
```

**PHP Exception Handling:**
```php
// In action files
try {
    $DB->insert_record('externallecturer', $newlecturer);
    echo json_encode(['status' => 'success', 'message' => 'Lecturer added successfully']);
} catch (dml_exception $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode(['status' => 'fail', 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log('General error: ' . $e->getMessage());
    echo json_encode(['status' => 'fail', 'message' => 'An unexpected error occurred']);
}
```

These examples demonstrate the flexibility and extensibility of the External Lecturer Management plugin, providing a foundation for various institutional needs and custom implementations. 