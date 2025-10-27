# User Creation Feature Documentation

## Overview

The Participant Management plugin now includes automatic Moodle user creation functionality. When a user selects an employee from the Oracle database dropdown and submits the form, the system will automatically create a Moodle user account for that employee if one doesn't already exist.

## Features

### 1. Automatic User Creation

- When an internal participant is selected from the Oracle database
- Creates Moodle user account with the employee's PF number as username
- Uses employee data (first name, last name, civil number) to populate user fields
- Generates a random password for the new account
- Assigns default "student" role to the user

### 2. Automatic Course Enrollment

- Automatically enrolls the created user in the selected course
- Uses manual enrollment method
- Assigns student role in the course context
- Prevents duplicate enrollments

### 3. User Detection

- Checks if user already exists by username (PF number)
- Checks if user exists by email (civil number)
- Returns existing user ID if found, creates new user if not

## Technical Implementation

### Files Modified

1. **`add_request.php`**

   - Added `create_or_get_moodle_user()` function
   - Added `enroll_user_in_course()` function
   - Modified form processing to create users
   - Added success/error notifications

2. **Language Files**
   - `lang/en/local_participant.php` - English strings
   - `lang/ar/local_participant.php` - Arabic strings

### Key Functions

#### `create_or_get_moodle_user($employee_data_json, $pf_number)`

- Parses employee data from JSON
- Checks for existing users by username or email
- Creates new user if not found
- Returns Moodle user ID

#### `enroll_user_in_course($user_id, $course_id, $role_id = 5)`

- Checks for existing enrollment
- Creates manual enrollment record
- Assigns role in course context
- Returns success/failure status

## User Experience

### Form Submission Process

1. User selects employee from Oracle database dropdown
2. User fills in other form fields (course, type, duration, etc.)
3. User clicks "Save Changes"
4. System processes the request and:
   - Creates Moodle user account (if needed)
   - Enrolls user in the course
   - Saves participant request
   - Shows success message with user creation info

### Success Messages

- **English**: "New request has been added. Moodle user account created for PF: [PF_NUMBER]. User enrolled in course successfully."
- **Arabic**: "تم إرسال الطلب بنجاح. تم إنشاء حساب مستخدم في مودل للرقم الوظيفي: [PF_NUMBER]. تم تسجيل المستخدم في الدورة بنجاح."

### Error Handling

- Shows warning if user creation fails
- Continues with request processing even if user creation fails
- Logs all errors for debugging

## Testing

### Test File

Use `test_user_creation.php` to test the functionality:

- Tests user creation with sample data
- Tests course enrollment
- Shows detailed results and user information
- Provides cleanup instructions

### Access Requirements

- Admin capabilities required to run test file
- URL: `/local/participant/test_user_creation.php`

## Configuration

### User Account Settings

- **Authentication**: Manual
- **Username**: Employee PF number
- **Email**: Civil number + @moodle.local (or PF number + @moodle.local)
- **Password**: Randomly generated
- **Role**: Student (default)
- **Status**: Confirmed and active

### Course Enrollment Settings

- **Method**: Manual enrollment
- **Role**: Student (role ID 5)
- **Duration**: No end date (permanent)
- **Status**: Active

## Security Considerations

1. **Password Generation**: Uses Moodle's `generate_password()` function
2. **Email Validation**: Uses civil number or PF number for email generation
3. **Role Assignment**: Limited to student role by default
4. **Duplicate Prevention**: Checks for existing users before creation
5. **Error Logging**: All operations are logged for audit purposes

## Troubleshooting

### Common Issues

1. **User Creation Fails**

   - Check database permissions
   - Verify Oracle connection
   - Check error logs

2. **Course Enrollment Fails**

   - Ensure manual enrollment is enabled for the course
   - Check course visibility settings
   - Verify role assignments

3. **Duplicate Users**
   - System automatically detects existing users
   - Uses PF number as unique identifier
   - Checks both username and email fields

### Debug Information

- All operations are logged to Moodle's error log
- Test file provides detailed debugging information
- Form includes debug information during development

## Future Enhancements

1. **Email Notifications**: Send welcome emails to new users
2. **Password Reset**: Allow users to reset their passwords
3. **Role Customization**: Allow different roles based on participant type
4. **Bulk Operations**: Support for bulk user creation
5. **Integration**: Better integration with existing Moodle user management

## Name Display in View Requests

### Overview

The view requests page now displays the full name of internal participants instead of just their PF number. This provides a better user experience by showing readable names.

### How It Works

1. **Primary Lookup**: First checks the Moodle user table for the employee's full name
2. **Fallback Lookup**: If not found in Moodle, queries the Oracle database
3. **Format Handling**: Supports both PF number formats (with "PF" prefix and numeric only)
4. **Final Fallback**: Shows "PF: [number]" if no name is found

### Technical Details

- **Function**: `get_employee_name_by_pf()` in `index.php`
- **Moodle Lookup**: Searches by username (PF number) in the user table
- **Oracle Lookup**: Uses the same connection parameters as the AJAX functionality
- **Error Handling**: Graceful fallback with logging for debugging

### Testing

Use `test_name_lookup.php` to test the name lookup functionality:

- Tests various PF number formats
- Shows existing Moodle users with PF numbers
- Tests Oracle connection
- Provides detailed results and debugging information
