# Oracle Integration for Resident Lecturer Forms

## Overview

This document describes the integration between the External Lecturer Management plugin and the Oracle Fetch plugin for resident lecturer data population.

## Integration Summary

The resident lecturer form now includes **Oracle database integration** that allows users to:

1. **Search by Civil Number**: Enter a civil number to automatically populate form fields
2. **Auto-fill Data**: Name, civil number, and nationality are automatically filled from Oracle database
3. **Manual Completion**: Users complete remaining fields (age, specialization, organization, degree, passport) manually

## Components Added

### 1. Oracle Fetch Plugin - New AJAX Endpoint

**File**: `local/oracleFetch/ajax/search_by_civil.php`

- **Purpose**: Search for person data by civil number
- **Method**: POST
- **Parameters**: `civil_number` (required)
- **Returns**: JSON response with person data including nationality

### 2. Updated Resident Lecturer Form

**File**: `local/externallecturer/templates/form_residentlecturer.mustache`

**Changes Made**:
- Added civil number search field at the top of form
- Made name field read-only (auto-filled from Oracle)
- Made civil number field read-only (auto-filled from Oracle)  
- Made nationality field read-only (auto-filled from Oracle)
- Added loading indicator for search process
- Added helpful text explaining auto-fill functionality

### 3. Enhanced JavaScript Functionality

**File**: `local/externallecturer/js/main.js`

**New Functions Added**:
- `searchByCivilNumber()`: Performs AJAX request to Oracle database
- `populateResidentFormFields()`: Fills form fields with Oracle data
- `clearResidentFormFields()`: Clears form when search is empty
- Debounced search (500ms delay) for better performance

### 4. Language Support

**Files**: 
- `local/externallecturer/lang/en/local_externallecturer.php`
- `local/externallecturer/lang/ar/local_externallecturer.php`

**New Strings Added**:
- `civilnumbersearch`: "Civil Number Search" / "البحث بالرقم المدني"
- `civilnumberplaceholder`: Search placeholder text
- `civilnumberhelp`: Help text explaining functionality
- `searchingdata`: Loading message
- `namefrompracle`: Explanation that name comes from Oracle
- `nationalityfrompracle`: Explanation that nationality comes from Oracle

## How It Works

### User Workflow

1. **Open Resident Lecturer Form**
   - Click "اضف محاضر مقيم" (Add Resident Lecturer) button
   - Form opens with civil number search field at top

2. **Search by Civil Number**
   - User enters civil number in search field
   - System waits 500ms after typing stops (debounced)
   - AJAX request sent to Oracle database

3. **Auto-Population**
   - If person found: Name, civil number, and nationality are filled automatically
   - If not found: Fields remain empty, user can proceed with manual entry

4. **Complete Form**
   - User fills remaining required fields manually:
     - Age
     - Specialization
     - Organization
     - Degree
     - Passport
   - Submit form as normal

### Technical Flow

1. **User Input**: Civil number entered in search field
2. **JavaScript**: Captures input, debounces for 500ms
3. **AJAX Request**: POST to `../oracleFetch/ajax/search_by_civil.php`
4. **Oracle Query**: Searches `DUNIA_PERSONAL_DETAILS` table
5. **Data Response**: Returns person data including new nationality field
6. **Form Population**: JavaScript fills read-only fields automatically

## Database Schema Integration

The integration leverages the **nationality support** added to the Oracle Fetch plugin:

- **Table**: `DUNIA_PERSONAL_DETAILS`
- **New Field**: `nationality_arabic_SYS` (VARCHAR2(100))
- **Auto-filled Fields**: 
  - `NAME_ARABIC_1`, `NAME_ARABIC_2`, `NAME_ARABIC_3` → Full name
  - `CIVIL_NUMBER` → Civil number
  - `NATIONALITY_ARABIC_SYS` → Nationality

## Benefits

### For Users
- **Faster Data Entry**: No need to manually type name and nationality
- **Data Accuracy**: Information comes directly from authoritative Oracle database
- **Error Prevention**: Reduces typos in name and nationality fields
- **User-Friendly**: Clear indicators and help text guide the process

### For System
- **Data Consistency**: Ensures resident lecturer data matches Oracle records
- **Integration**: Leverages existing Oracle database infrastructure
- **Scalability**: Can be extended to other form fields as needed

## Testing

### Test Script Available
**File**: `local/oracleFetch/test_civil_search.php`

This script allows administrators to:
- Test civil number search functionality
- Verify Oracle database connectivity
- Check sample data responses
- Test AJAX endpoint directly

### Sample Test Data
Civil numbers to test with (from dummy data):
- 10001 → أحمد محمد علي (سعودي)
- 10002 → سارة خالد سليمان (كويتي)
- 10003 → خالد جابر سعد (إماراتي)
- 10004 → ليلى ناصر علي (بحريني)
- 10005 → عمر سعود حسن (قطري)

## Configuration Requirements

### Prerequisites
1. **Oracle Fetch Plugin**: Must be installed and configured
2. **Oracle Database**: DUNIA_PERSONAL_DETAILS table with nationality field
3. **Moodle Authentication**: User must be logged in
4. **Proper Capabilities**: User needs `local/externallecturer:manage` capability

### No Additional Configuration Needed
- Integration works automatically once both plugins are installed
- Uses existing Oracle connection from Oracle Fetch plugin
- Leverages Moodle's built-in authentication and session management

## Error Handling

### Graceful Degradation
- If Oracle database is unavailable: Form works in manual mode
- If civil number not found: User can proceed with manual entry
- If JavaScript disabled: Form falls back to manual mode
- Network errors: Clear error messaging and fallback to manual entry

### User Feedback
- **Loading indicator**: Shows when search is in progress
- **Clear messaging**: Explains when data is auto-filled vs manual
- **Visual cues**: Read-only fields are clearly marked
- **Help text**: Explains functionality in both languages

## Future Enhancements

### Potential Improvements
1. **Additional Fields**: Auto-fill more fields from Oracle (e.g., organization)
2. **Validation**: Cross-check passport numbers with Oracle data
3. **Real-time Sync**: Update Oracle database when resident lecturer data changes
4. **Audit Trail**: Track which data came from Oracle vs manual entry

### Integration Opportunities
1. **Other Forms**: Apply similar integration to external visitor forms
2. **User Management**: Integrate with Moodle user creation
3. **Reporting**: Generate reports showing Oracle vs manual data sources
